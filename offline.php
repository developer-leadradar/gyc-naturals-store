<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>You're Offline — GYC Naturals</title>
<link rel="icon" href="/gyc-store/assets/images/favicon.ico" type="image/x-icon">
<style>
  :root {
    --gyc-green-900: #0D2B1A;
    --gyc-green-700: #166534;
    --gyc-green-600: #16A34A;
    --gyc-green-100: #DCFCE7;
    --gyc-gold:      #C8A152;
    --gyc-dark:      #1A1A2E;
  }
  * { margin:0; padding:0; box-sizing:border-box; }
  body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: #F8FAF9;
    color: var(--gyc-dark);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    text-align: center;
  }
  .offline-card {
    background: #fff;
    border: 1.5px solid var(--gyc-green-100);
    border-radius: 20px;
    padding: 3rem 2.5rem;
    max-width: 480px;
    width: 100%;
    box-shadow: 0 4px 24px rgba(0,0,0,.08);
  }
  .wifi-icon {
    width: 80px;
    height: 80px;
    background: var(--gyc-green-100);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
  }
  h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.85rem;
    margin-bottom: .75rem;
    color: var(--gyc-dark);
  }
  p {
    font-size: .92rem;
    color: #6B7280;
    line-height: 1.7;
    margin-bottom: 1.25rem;
  }
  .btn-green {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: var(--gyc-green-700);
    color: #fff;
    padding: .75rem 1.75rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: .9rem;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s;
    margin: .35rem;
  }
  .btn-green:hover { background: var(--gyc-green-900); }
  .btn-outline {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: transparent;
    color: var(--gyc-green-700);
    padding: .75rem 1.75rem;
    border-radius: 8px;
    border: 1.5px solid var(--gyc-green-700);
    cursor: pointer;
    font-size: .9rem;
    font-weight: 600;
    text-decoration: none;
    transition: background .2s, color .2s;
    margin: .35rem;
  }
  .btn-outline:hover { background: var(--gyc-green-100); }
  .tips {
    margin-top: 2rem;
    background: var(--gyc-green-100);
    border-radius: 12px;
    padding: 1.25rem;
    text-align: left;
  }
  .tips-title {
    font-weight: 700;
    font-size: .85rem;
    color: var(--gyc-green-700);
    margin-bottom: .75rem;
  }
  .tip-item {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    font-size: .82rem;
    color: #374151;
    margin-bottom: .4rem;
  }
  .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--gyc-green-600);
    flex-shrink: 0;
    margin-top: .4rem;
  }
  .logo {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gyc-green-700);
    margin-bottom: 2rem;
    letter-spacing: -.02em;
  }
  .status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #EF4444;
    margin-right: .4rem;
    animation: pulse 1.5s ease-in-out infinite;
  }
  .status-dot.online { background: #10B981; animation: none; }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: .4; }
  }
</style>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600;700&display=swap">
</head>
<body>

<div class="logo">GYC Naturals</div>

<div class="offline-card">
  <div class="wifi-icon">
    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--gyc-green-700)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <line x1="1" y1="1" x2="23" y2="23"/>
      <path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/>
      <path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/>
      <path d="M10.71 5.05A16 16 0 0 1 22.56 9"/>
      <path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/>
      <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
      <line x1="12" y1="20" x2="12.01" y2="20"/>
    </svg>
  </div>

  <h1>You're Offline</h1>

  <p>
    <span class="status-dot" id="status-dot"></span>
    <span id="status-text">No internet connection</span>
  </p>

  <p>Don't worry — our team is ready to help when you're back online. GYC Naturals, Big Qua Mall, Calabar.</p>

  <div>
    <button class="btn-green" onclick="retryConnection()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
      Try Again
    </button>
    <a href="javascript:history.back()" class="btn-outline">← Go Back</a>
  </div>

  <div class="tips">
    <div class="tips-title">While you wait…</div>
    <div class="tip-item"><div class="dot"></div><span>Check your Wi-Fi or mobile data connection</span></div>
    <div class="tip-item"><div class="dot"></div><span>Try turning airplane mode on and off</span></div>
    <div class="tip-item"><div class="dot"></div><span>Move closer to your Wi-Fi router</span></div>
    <div class="tip-item"><div class="dot"></div><span>Contact us on WhatsApp — we can still chat!</span></div>
  </div>
</div>

<!-- WhatsApp fallback -->
<div style="margin-top:1.75rem;">
  <a href="https://wa.me/<?php
    if (file_exists(__DIR__ . '/config.php')) {
      define('GYC_ACCESS', true);
      require_once __DIR__ . '/config.php';
      echo preg_replace('/[^0-9]/', '', SITE_WHATSAPP);
    } else {
      echo '2348000000000';
    }
  ?>"
     target="_blank" rel="noopener"
     style="display:inline-flex;align-items:center;gap:.5rem;background:#25D366;color:#fff;padding:.65rem 1.4rem;border-radius:8px;text-decoration:none;font-weight:600;font-size:.85rem;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    WhatsApp GYC Naturals
  </a>
</div>

<script>
function retryConnection() {
  updateStatus();
  if (navigator.onLine) {
    window.location.reload();
  }
}

function updateStatus() {
  const dot  = document.getElementById('status-dot');
  const text = document.getElementById('status-text');
  if (navigator.onLine) {
    dot.classList.add('online');
    text.textContent = 'Connection restored! Reloading…';
    setTimeout(function() { window.location.href = '/gyc-store/'; }, 1000);
  } else {
    dot.classList.remove('online');
    text.textContent = 'No internet connection';
  }
}

window.addEventListener('online',  updateStatus);
window.addEventListener('offline', updateStatus);
updateStatus();
</script>

</body>
</html>
