<?php
$phone     = getSetting('site_phone') ?: SITE_PHONE;
$whatsapp  = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
$instagram = getSetting('instagram_handle') ?: 'gycnaturals';
$igUrl     = getSetting('instagram_url')    ?: 'https://instagram.com/gycnaturals';
$hours     = getSetting('business_hours')   ?: 'Mon–Sat: 9am – 7pm';
$address   = getSetting('business_address') ?: 'Big Qua Mall, Ediba Road, Calabar, Cross River State';
$email     = getSetting('site_email')       ?: SITE_EMAIL;
$waLink    = getWhatsAppFloat();
?>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Brand -->
      <div class="footer-brand">
        <img src="<?= SITE_URL ?>/assets/images/gyc-logo-horizontal.svg"
             alt="GYC Naturals" width="180" height="45"
             style="filter:brightness(0) invert(1);opacity:0.9;">
        <p>Professional African hair braiding, natural hair products &amp; fashion in Calabar, Cross River State. Wear your crown with confidence.</p>
        <div class="footer-social">
          <a href="<?= htmlspecialchars($igUrl) ?>" target="_blank" rel="noopener" aria-label="Instagram">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
              <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
              <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
            </svg>
          </a>
          <a href="<?= htmlspecialchars($waLink) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          </a>
          <a href="mailto:<?= htmlspecialchars($email) ?>" aria-label="Email">
            <i data-lucide="mail" style="width:18px;height:18px;"></i>
          </a>
          <a href="tel:<?= preg_replace('/[^+0-9]/','',$phone) ?>" aria-label="Phone">
            <i data-lucide="phone" style="width:18px;height:18px;"></i>
          </a>
        </div>
      </div>

      <!-- Quick Links -->
      <div>
        <h4 class="footer-heading">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/gallery.php">Hair Gallery</a></li>
          <li><a href="<?= SITE_URL ?>/shop.php">Shop Products</a></li>
          <li><a href="<?= SITE_URL ?>/book-appointment.php">Book Appointment</a></li>
          <li><a href="<?= SITE_URL ?>/quiz.php">Hair Quiz</a></li>
          <li><a href="<?= SITE_URL ?>/moodboard.php">My Moodboard</a></li>
          <li><a href="<?= SITE_URL ?>/clothing.php">Clothing Line</a></li>
        </ul>
      </div>

      <!-- Company -->
      <div>
        <h4 class="footer-heading">Company</h4>
        <ul class="footer-links">
          <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
          <li><a href="<?= SITE_URL ?>/services.php">Our Services</a></li>
          <li><a href="<?= SITE_URL ?>/faq.php">FAQ</a></li>
          <li><a href="<?= SITE_URL ?>/contact.php">Contact Us</a></li>
          <li><a href="<?= SITE_URL ?>/privacy.php">Privacy Policy</a></li>
          <li><a href="<?= SITE_URL ?>/terms.php">Terms & Conditions</a></li>
          <li><a href="<?= SITE_URL ?>/refund.php">Refund Policy</a></li>
        </ul>
      </div>

      <!-- Contact -->
      <div>
        <h4 class="footer-heading">Visit Us</h4>
        <ul class="footer-links">
          <li style="margin-bottom:0.75rem;">
            <span style="color:rgba(255,255,255,0.4);font-size:0.75rem;display:block;margin-bottom:0.2rem;">Address</span>
            <?= htmlspecialchars($address) ?>
          </li>
          <li style="margin-bottom:0.75rem;">
            <span style="color:rgba(255,255,255,0.4);font-size:0.75rem;display:block;margin-bottom:0.2rem;">Hours</span>
            <?= htmlspecialchars($hours) ?>
          </li>
          <li style="margin-bottom:0.75rem;">
            <span style="color:rgba(255,255,255,0.4);font-size:0.75rem;display:block;margin-bottom:0.2rem;">Phone / WhatsApp</span>
            <a href="<?= htmlspecialchars($waLink) ?>" style="color:rgba(255,255,255,0.65);"><?= htmlspecialchars($phone) ?></a>
          </li>
          <li>
            <a href="<?= SITE_URL ?>/book-appointment.php"
               class="btn btn-gold btn-sm" style="margin-top:0.5rem;">
              Book Appointment
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Adinkra Gye Nyame -->
    <div class="footer-adinkra">
      <img src="<?= SITE_URL ?>/assets/images/adinkra-gye-nyame.svg" alt="Gye Nyame" width="36" height="36" style="filter:brightness(0) invert(1);">
    </div>

    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> GYC Naturals. All rights reserved.</span>
      <span>Made with love in Calabar 🇳🇬</span>
      <span>
        <a href="<?= SITE_URL ?>/privacy.php" style="color:rgba(255,255,255,0.4);">Privacy</a> ·
        <a href="<?= SITE_URL ?>/terms.php"   style="color:rgba(255,255,255,0.4);">Terms</a>
      </span>
    </div>
  </div>
</footer>

<!-- WhatsApp Float -->
<a href="<?= htmlspecialchars($waLink) ?>"
   class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
  <svg viewBox="0 0 24 24" fill="currentColor">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
</a>

<!-- Scroll to top -->
<button class="scroll-top" id="scroll-top" aria-label="Back to top">
  <i data-lucide="chevron-up" style="width:22px;height:22px;"></i>
</button>

<!-- Cookie Banner -->
<div class="cookie-banner" id="cookie-banner">
  <span>
    We use cookies to improve your experience. By continuing you accept our
    <a href="<?= SITE_URL ?>/privacy.php">Privacy Policy</a>.
  </span>
  <button onclick="document.getElementById('cookie-banner').classList.add('hidden');localStorage.setItem('gyc_cookies','1')"
          class="btn btn-gold btn-sm" style="flex-shrink:0;">
    Accept
  </button>
</div>

<!-- Main JS -->
<script>window.GYC_LOGGED_IN = <?= isLoggedIn() ? 'true' : 'false' ?>;</script>
<?php $_mainJs = __DIR__ . '/../assets/js/main.js'; $_mainV = file_exists($_mainJs) ? '?v=' . filemtime($_mainJs) : ''; ?>
<script src="<?= SITE_URL ?>/assets/js/main.js<?= $_mainV ?>" defer></script>

<script>
// ── Init Lucide icons ──
document.addEventListener('DOMContentLoaded', function() {
  if (typeof lucide !== 'undefined') lucide.createIcons();
});

// ── Service Worker ──
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('<?= SITE_URL ?>/service-worker.js')
    .catch(function(err) { console.log('SW reg failed:', err); });
}

// ── Lazy image fade-in ──
// Reveal helper: idempotent — always also wires load/error so late-loading images
// can never get stranded at opacity:0 (the previous code had a race where complete
// images skipped both the observer and the load listener, leaving them invisible).
(function() {
  function reveal(img) {
    if (img.classList.contains('loaded') || img.classList.contains('instantly-visible')) return;
    if (img.complete && img.naturalWidth > 0) {
      img.classList.add('instantly-visible');
    } else {
      img.addEventListener('load',  function() { img.classList.add('loaded'); }, { once: true });
      img.addEventListener('error', function() { img.classList.add('loaded'); }, { once: true });
      // Safety net: if neither load nor error fires (cached 304, decode race, etc.),
      // poll once after 1.5s to mark as visible anyway.
      setTimeout(function() {
        if (!img.classList.contains('loaded') && !img.classList.contains('instantly-visible')) {
          img.classList.add('loaded');
        }
      }, 1500);
    }
  }

  function observeAll() {
    document.querySelectorAll('img[loading="lazy"]').forEach(function(img) {
      if (img.dataset._gycLazyBound) return;
      img.dataset._gycLazyBound = '1';
      if (!('IntersectionObserver' in window)) { reveal(img); return; }
      io.observe(img);
      // Reveal immediately if already in viewport on first pass
      var r = img.getBoundingClientRect();
      if (r.top < window.innerHeight + 200 && r.bottom > -200) reveal(img);
    });
  }

  var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) { reveal(entry.target); io.unobserve(entry.target); }
    });
  }, { rootMargin: '300px 0px' }) : null;

  observeAll();
  // Re-scan when JS adds new lazy images (moodboard grid, filtered gallery)
  var mo = new MutationObserver(observeAll);
  mo.observe(document.body, { childList: true, subtree: true });
})();

// ── Cookie banner ──
(function() {
  if (localStorage.getItem('gyc_cookies')) {
    var b = document.getElementById('cookie-banner');
    if (b) b.style.display = 'none';
  }
})();
</script>
</body>
</html>
