/**
 * GYC Naturals Service Worker
 * Stale-while-revalidate for pages, cache-first for assets
 */

const CACHE_VERSION   = 'gyc-v1';
const STATIC_CACHE    = CACHE_VERSION + '-static';
const DYNAMIC_CACHE   = CACHE_VERSION + '-dynamic';
const IMAGE_CACHE     = CACHE_VERSION + '-images';

// Assets to pre-cache on install
const PRECACHE_URLS = [
  '/gyc-store/',
  '/gyc-store/index.php',
  '/gyc-store/shop.php',
  '/gyc-store/gallery.php',
  '/gyc-store/book-appointment.php',
  '/gyc-store/cart.php',
  '/gyc-store/offline.php',
  '/gyc-store/assets/css/style.css',
  '/gyc-store/assets/css/pages.css',
  '/gyc-store/assets/css/home-additions.css',
  '/gyc-store/manifest.json',
];

// Pages that work great offline from cache
const CACHE_FIRST_PAGES = [
  '/gyc-store/about.php',
  '/gyc-store/services.php',
  '/gyc-store/faq.php',
  '/gyc-store/contact.php',
  '/gyc-store/privacy.php',
  '/gyc-store/terms.php',
  '/gyc-store/refund.php',
];

// ── Install ───────────────────────────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then(cache => {
      return cache.addAll(PRECACHE_URLS.map(url => new Request(url, { credentials: 'same-origin' })));
    }).then(() => self.skipWaiting())
  );
});

// ── Activate ──────────────────────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys.filter(k => k.startsWith('gyc-') && ![STATIC_CACHE, DYNAMIC_CACHE, IMAGE_CACHE].includes(k))
            .map(k => caches.delete(k))
      )
    ).then(() => self.clients.claim())
  );
});

// ── Fetch strategy ────────────────────────────────────────
self.addEventListener('fetch', event => {
  const req = event.request;
  const url = new URL(req.url);

  // Skip non-GET, cross-origin, admin pages, API calls
  if (req.method !== 'GET') return;
  if (url.origin !== location.origin) return;
  if (url.pathname.startsWith('/gyc-store/admin/')) return;
  if (url.pathname.startsWith('/gyc-store/api/')) return;

  // Images: cache-first with long TTL
  if (/\.(png|jpg|jpeg|webp|gif|svg|ico)$/i.test(url.pathname)) {
    event.respondWith(cacheFirst(req, IMAGE_CACHE));
    return;
  }

  // Static assets: cache-first
  if (/\.(css|js|woff2?|ttf|eot)$/i.test(url.pathname)) {
    event.respondWith(cacheFirst(req, STATIC_CACHE));
    return;
  }

  // Static info pages: cache-first (offline-friendly)
  if (CACHE_FIRST_PAGES.some(p => url.pathname.includes(p.replace('/gyc-store', '')))) {
    event.respondWith(cacheFirst(req, DYNAMIC_CACHE));
    return;
  }

  // PHP pages: stale-while-revalidate
  if (url.pathname.endsWith('.php') || url.pathname.endsWith('/')) {
    event.respondWith(staleWhileRevalidate(req, DYNAMIC_CACHE));
    return;
  }
});

// ── Strategy: cache-first ────────────────────────────────
async function cacheFirst(req, cacheName) {
  const cached = await caches.match(req);
  if (cached) return cached;
  try {
    const res = await fetch(req);
    if (res.ok) {
      const cache = await caches.open(cacheName);
      cache.put(req, res.clone());
    }
    return res;
  } catch {
    // Offline fallback
    if (req.headers.get('Accept')?.includes('text/html')) {
      return caches.match('/gyc-store/offline.php');
    }
    return new Response('', { status: 503 });
  }
}

// ── Strategy: stale-while-revalidate ────────────────────
async function staleWhileRevalidate(req, cacheName) {
  const cache  = await caches.open(cacheName);
  const cached = await cache.match(req);

  const fetchPromise = fetch(req).then(res => {
    if (res.ok) cache.put(req, res.clone());
    return res;
  }).catch(() => null);

  if (cached) {
    // Serve cached immediately, update in background
    fetchPromise; // fire-and-forget
    return cached;
  }

  // Nothing cached — wait for network
  const res = await fetchPromise;
  if (res) return res;

  // Full offline fallback
  return caches.match('/gyc-store/offline.php') ||
         new Response('<h1>You are offline</h1><p><a href="/gyc-store/offline.php">Go to offline page</a></p>', {
           status: 503,
           headers: { 'Content-Type': 'text/html; charset=utf-8' }
         });
}

// ── Background sync for cart (future enhancement) ────────
self.addEventListener('sync', event => {
  if (event.tag === 'sync-cart') {
    // Placeholder — cart is session-based so sync is handled server-side
  }
});

// ── Push notifications (future) ──────────────────────────
self.addEventListener('push', event => {
  if (!event.data) return;
  const data = event.data.json();
  event.waitUntil(
    self.registration.showNotification(data.title || 'GYC Naturals', {
      body:    data.body    || 'You have a new notification.',
      icon:    data.icon    || '/gyc-store/assets/images/icon-192.png',
      badge:   data.badge   || '/gyc-store/assets/images/icon-72.png',
      tag:     data.tag     || 'gyc-notification',
      data:    { url: data.url || '/gyc-store/' },
      actions: data.actions || [],
    })
  );
});

self.addEventListener('notificationclick', event => {
  event.notification.close();
  const url = event.notification.data?.url || '/gyc-store/';
  event.waitUntil(
    clients.matchAll({ type: 'window' }).then(windowClients => {
      for (const client of windowClients) {
        if (client.url === url && 'focus' in client) return client.focus();
      }
      return clients.openWindow(url);
    })
  );
});
