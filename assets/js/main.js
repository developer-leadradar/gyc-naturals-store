/* GYC Naturals — Main JavaScript */

document.addEventListener('DOMContentLoaded', function () {

  // ── Lucide icons ──────────────────────────────────
  if (typeof lucide !== 'undefined') lucide.createIcons();

  // ── Mobile nav toggle ─────────────────────────────
  const navToggle = document.getElementById('nav-toggle');
  const navMobile = document.getElementById('nav-mobile');
  if (navToggle && navMobile) {
    navToggle.addEventListener('click', function () {
      navMobile.classList.toggle('open');
      const spans = navToggle.querySelectorAll('span');
      navMobile.classList.contains('open')
        ? (spans[0].style.transform = 'rotate(45deg) translate(5px,5px)',
           spans[1].style.opacity   = '0',
           spans[2].style.transform = 'rotate(-45deg) translate(5px,-5px)')
        : (spans.forEach(s => (s.style.transform = '', s.style.opacity = '')));
    });
  }

  // ── Scroll to top ──────────────────────────────────
  const scrollTopBtn = document.getElementById('scroll-top');
  if (scrollTopBtn) {
    window.addEventListener('scroll', function () {
      scrollTopBtn.classList.toggle('visible', window.scrollY > 300);
    });
    scrollTopBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // ── Cookie banner ──────────────────────────────────
  const cookieBanner = document.getElementById('cookie-banner');
  if (cookieBanner && localStorage.getItem('gyc_cookies') === '1') {
    cookieBanner.classList.add('hidden');
  }

  // ── Moodboard nav link ─────────────────────────────
  updateMoodboardNav();

  // ── Cart count (from session or local) ─────────────
  // (updated by AJAX responses — no extra fetch needed)

  // ── FAQ accordion ──────────────────────────────────
  document.querySelectorAll('.faq-question').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const item = btn.closest('.faq-item');
      const isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!isOpen) item.classList.add('open');
    });
  });

  // ── Tab bars ───────────────────────────────────────
  document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const bar   = btn.closest('.tab-bar') || btn.parentElement;
      const target = btn.dataset.tab;
      bar.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const container = bar.parentElement || document;
      container.querySelectorAll('.tab-panel').forEach(function (p) {
        p.classList.toggle('active', p.id === target);
      });
    });
  });

  // ── Gallery filter chips ───────────────────────────
  document.querySelectorAll('.filter-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
      document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const category = chip.dataset.category || '';
      filterGallery(category);
    });
  });

  // ── Bookmark / moodboard toggle ────────────────────
  document.querySelectorAll('.gallery-card-bookmark').forEach(function (btn) {
    const slug = btn.dataset.slug;
    if (slug) {
      updateBookmarkIcon(btn, isSaved(slug));
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleMoodboard(slug, btn);
      });
    }
  });

  // ── Before/After slider ────────────────────────────
  document.querySelectorAll('.before-after-slider').forEach(initBeforeAfter);

  // ── Add to cart buttons ────────────────────────────
  document.querySelectorAll('[data-add-to-cart]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const productId = btn.dataset.addToCart;
      addToCart(productId, 1, btn);
    });
  });

  // ── Wishlist toggle ────────────────────────────────
  document.querySelectorAll('[data-wishlist-toggle]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const productId = btn.dataset.wishlistToggle;
      toggleWishlist(productId, btn);
    });
  });

  // ── Search autocomplete ────────────────────────────
  const searchInput = document.querySelector('.search-bar input');
  if (searchInput) initSearchAutocomplete(searchInput);

});

// ═══════════════════════════════════════════════════
// TOAST
// ═══════════════════════════════════════════════════
function showToast(message, type = 'success', duration = 3500) {
  const container = document.getElementById('toast-container');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = 'toast toast-' + type;
  const icons = { success: 'check-circle', error: 'x-circle', info: 'info' };
  toast.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><use href="#icon-' + (icons[type] || 'info') + '"/></svg>' + message;
  container.appendChild(toast);
  setTimeout(function () {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(20px)';
    toast.style.transition = '300ms ease';
    setTimeout(function () { toast.remove(); }, 300);
  }, duration);
}

// ═══════════════════════════════════════════════════
// CART
// ═══════════════════════════════════════════════════
function addToCart(productId, quantity, btn) {
  const original = btn ? btn.innerHTML : null;
  if (btn) { btn.innerHTML = '<span style="display:inline-block;width:16px;height:16px;border:2px solid currentColor;border-top-color:transparent;border-radius:50%;animation:spin 0.6s linear infinite;"></span>'; btn.disabled = true; }

  fetch(window.GYC_URL + '/api/add-to-cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'product_id=' + productId + '&quantity=' + quantity + '&csrf=' + encodeURIComponent(document.querySelector('meta[name="csrf"]')?.content || '')
  })
  .then(r => r.json())
  .then(function (data) {
    if (data.success) {
      updateCartCount(data.cart_count);
      showToast('Added to cart!', 'success');
    } else {
      showToast(data.message || 'Could not add to cart.', 'error');
    }
  })
  .catch(function () { showToast('Network error. Please try again.', 'error'); })
  .finally(function () {
    if (btn && original) { btn.innerHTML = original; btn.disabled = false; }
  });
}

function updateCartCount(count) {
  const badge = document.getElementById('cart-count');
  if (!badge) return;
  badge.textContent = count;
  badge.style.display = count > 0 ? 'flex' : 'none';
}

// ═══════════════════════════════════════════════════
// WISHLIST
// ═══════════════════════════════════════════════════
function toggleWishlist(productId, btn) {
  fetch(window.GYC_URL + '/api/add-to-wishlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'product_id=' + productId
  })
  .then(r => r.json())
  .then(function (data) {
    if (data.success) {
      var added = data.action === 'added';
      btn.classList.toggle('active', added);
      showToast(added ? 'Added to wishlist!' : 'Removed from wishlist.', 'info');
      // Update nav wishlist badge
      var badge = document.getElementById('wishlist-count');
      if (badge) {
        var cur = parseInt(badge.textContent) || 0;
        var next = added ? cur + 1 : Math.max(0, cur - 1);
        badge.textContent = next;
        badge.style.display = next > 0 ? 'flex' : 'none';
      }
    } else {
      if (data.redirect) window.location.href = data.redirect;
      else showToast(data.message || 'Login required.', 'error');
    }
  });
}

// ═══════════════════════════════════════════════════
// GALLERY FILTER (AJAX)
// ═══════════════════════════════════════════════════
function filterGallery(category) {
  const grid    = document.getElementById('gallery-grid');
  const loading = document.getElementById('gallery-loading');
  if (!grid) return;
  if (loading) loading.style.display = 'flex';

  fetch(window.GYC_URL + '/api/filter-gallery.php?category=' + encodeURIComponent(category))
    .then(r => r.text())
    .then(function (html) {
      grid.innerHTML = html;
      if (loading) loading.style.display = 'none';
      // Re-init bookmarks on new cards
      grid.querySelectorAll('.gallery-card-bookmark').forEach(function (btn) {
        const slug = btn.dataset.slug;
        if (slug) {
          updateBookmarkIcon(btn, isSaved(slug));
          btn.addEventListener('click', function (e) {
            e.stopPropagation(); toggleMoodboard(slug, btn);
          });
        }
      });
      if (typeof lucide !== 'undefined') lucide.createIcons();
    });
}

// ═══════════════════════════════════════════════════
// MOODBOARD (localStorage)
// ═══════════════════════════════════════════════════
function getMoodboard() {
  try { return JSON.parse(localStorage.getItem('gyc_moodboard') || '[]'); }
  catch(e) { return []; }
}
function saveMoodboard(items) {
  localStorage.setItem('gyc_moodboard', JSON.stringify(items));
}
function isSaved(slug) {
  return getMoodboard().includes(slug);
}
function toggleMoodboard(slug, btn) {
  const items = getMoodboard();
  const idx   = items.indexOf(slug);
  if (idx === -1) {
    items.push(slug);
    updateBookmarkIcon(btn, true);
    showToast('Saved to your moodboard!', 'success');
  } else {
    items.splice(idx, 1);
    updateBookmarkIcon(btn, false);
    showToast('Removed from moodboard.', 'info');
  }
  saveMoodboard(items);
  updateMoodboardNav();
}
function updateBookmarkIcon(btn, saved) {
  if (!btn) return;
  btn.classList.toggle('saved', saved);
  const icon = btn.querySelector('[data-lucide]');
  if (icon) {
    icon.setAttribute('data-lucide', saved ? 'bookmark-check' : 'bookmark');
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }
}
function updateMoodboardNav() {
  const items = getMoodboard();
  const link  = document.getElementById('nav-moodboard-link');
  const count = document.getElementById('nav-moodboard-count');
  if (link) link.style.display = items.length > 0 ? 'inline-flex' : 'none';
  if (count) count.textContent = items.length;
}

// ═══════════════════════════════════════════════════
// BEFORE/AFTER SLIDER
// ═══════════════════════════════════════════════════
function initBeforeAfter(slider) {
  const divider = slider.querySelector('.ba-divider');
  const after   = slider.querySelector('.ba-after');
  if (!divider || !after) return;
  let isDragging = false;

  function setPosition(x) {
    const rect = slider.getBoundingClientRect();
    let pct    = Math.max(2, Math.min(98, ((x - rect.left) / rect.width) * 100));
    divider.style.left = pct + '%';
    after.style.clipPath = 'inset(0 ' + (100 - pct) + '% 0 0)';
    after.style.webkitClipPath = 'inset(0 ' + (100 - pct) + '% 0 0)';
  }

  // Init at 50%
  setPosition(slider.getBoundingClientRect().left + slider.offsetWidth / 2);

  divider.addEventListener('mousedown', function (e) { isDragging = true; e.preventDefault(); });
  document.addEventListener('mousemove', function (e) { if (isDragging) setPosition(e.clientX); });
  document.addEventListener('mouseup',   function ()  { isDragging = false; });
  divider.addEventListener('touchstart', function (e) { isDragging = true; });
  document.addEventListener('touchmove', function (e) { if (isDragging) setPosition(e.touches[0].clientX); });
  document.addEventListener('touchend',  function ()  { isDragging = false; });
}

// ═══════════════════════════════════════════════════
// SEARCH AUTOCOMPLETE
// ═══════════════════════════════════════════════════
function initSearchAutocomplete(input) {
  const dropdown = input.parentElement.querySelector('.autocomplete-dropdown');
  if (!dropdown) return;
  let debounce;
  input.addEventListener('input', function () {
    clearTimeout(debounce);
    const q = input.value.trim();
    if (q.length < 2) { dropdown.classList.remove('open'); return; }
    debounce = setTimeout(function () {
      fetch(window.GYC_URL + '/api/search-autocomplete.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(function (data) {
          dropdown.innerHTML = '';
          if (!data.length) { dropdown.classList.remove('open'); return; }
          data.forEach(function (item) {
            const el = document.createElement('div');
            el.className = 'autocomplete-item';
            el.textContent = item.label;
            el.addEventListener('click', function () {
              input.value = item.label;
              dropdown.classList.remove('open');
              window.location.href = item.url;
            });
            dropdown.appendChild(el);
          });
          dropdown.classList.add('open');
        });
    }, 250);
  });
  document.addEventListener('click', function (e) {
    if (!input.parentElement.contains(e.target)) dropdown.classList.remove('open');
  });
}

// Set global URL
window.GYC_URL = (function () {
  const scripts = document.querySelectorAll('script[src*="main.js"]');
  if (scripts.length) {
    const src = scripts[scripts.length - 1].src;
    return src.replace('/assets/js/main.js', '');
  }
  return '';
})();
