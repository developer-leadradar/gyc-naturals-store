<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'My Style Moodboard — GYC Naturals Calabar';
$pageDescription = 'Your saved hair styles and inspiration board. Share your favourites with our stylists when you book your appointment.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Moodboard Hero -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:5rem 0 3rem;">
  <div class="container" style="color:#fff;text-align:center;">
    <span class="section-eyebrow" style="color:var(--gyc-gold-300);">Your Inspiration</span>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,4vw,3rem);color:#fff;margin:.5rem 0 1rem;">
      My Style Moodboard
    </h1>
    <p style="color:rgba(255,255,255,.75);max-width:500px;margin:0 auto 1.5rem;font-size:.95rem;line-height:1.65;">
      Styles you've saved. Share this page with us when you book — we'll make your vision come to life.
    </p>
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;" id="hero-actions">
      <button class="btn btn-gold" id="share-moodboard-btn">
        <i data-lucide="share-2" style="width:18px;height:18px;"></i>
        Share Moodboard
      </button>
      <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-outline-white">
        <i data-lucide="plus" style="width:18px;height:18px;"></i>
        Add More Styles
      </a>
    </div>
  </div>
</section>

<section style="padding:3rem 0 5rem;background:#F8FAF9;">
  <div class="container">

    <!-- Toolbar -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.75rem;flex-wrap:wrap;gap:1rem;">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.25rem;margin:0;color:var(--gyc-dark);">
        <span id="moodboard-count-label">0</span> Saved Style<span id="moodboard-plural">s</span>
      </h2>
      <div style="display:flex;gap:.5rem;">
        <button class="btn btn-outline-green btn-sm" id="book-all-btn">
          <i data-lucide="calendar-check" style="width:14px;height:14px;"></i>
          Book a Style
        </button>
        <button class="btn btn-outline-green btn-sm" id="clear-all-btn" style="color:var(--gyc-terra);border-color:var(--gyc-terra);">
          <i data-lucide="trash-2" style="width:14px;height:14px;"></i>
          Clear All
        </button>
      </div>
    </div>

    <!-- Share link box (hidden until Share clicked) -->
    <div id="share-box" style="display:none;background:#fff;border:1.5px solid var(--gyc-green-200);border-radius:var(--gyc-radius-lg);padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
      <p style="font-size:.85rem;color:#555;margin-bottom:.75rem;">Copy this link to share your moodboard with your stylist:</p>
      <div style="display:flex;gap:.5rem;">
        <input type="url" id="share-url" class="form-control" readonly style="font-size:.83rem;flex:1;">
        <button class="btn btn-green btn-sm" onclick="copyShareUrl()">Copy</button>
      </div>
    </div>

    <!-- Empty state -->
    <div id="moodboard-empty" style="text-align:center;padding:5rem 2rem;">
      <div style="font-size:4rem;margin-bottom:1rem;">💆</div>
      <h3 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.75rem;">Your moodboard is empty</h3>
      <p style="color:#888;font-size:.9rem;max-width:360px;margin:0 auto 1.5rem;line-height:1.65;">
        Browse our gallery and click the bookmark icon on any style you love to save it here.
      </p>
      <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-green btn-lg">
        <i data-lucide="image" style="width:18px;height:18px;"></i>
        Browse Gallery
      </a>
    </div>

    <!-- Moodboard grid (populated by JS) -->
    <div id="moodboard-grid" class="moodboard-grid" style="display:none;"></div>

    <!-- CTA after grid -->
    <div id="moodboard-cta" style="display:none;margin-top:3rem;text-align:center;padding:2.5rem;background:var(--gyc-dark);border-radius:var(--gyc-radius-lg);">
      <h3 style="font-family:'Playfair Display',serif;font-size:1.4rem;color:#fff;margin-bottom:.75rem;">Love what you see?</h3>
      <p style="color:rgba(255,255,255,.72);margin-bottom:1.5rem;font-size:.92rem;">
        Book an appointment and show your stylist this moodboard. We will recreate your favourite look.
      </p>
      <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
        <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold btn-lg">
          <i data-lucide="calendar-check" style="width:18px;height:18px;"></i>
          Book an Appointment
        </a>
        <?php
        $waPhone = getSetting('site_whatsapp');
        if ($waPhone):
            $waUrl = whatsappMessage($waPhone, 'Hi! I would like to book an appointment. I have saved some styles on my moodboard.');
        ?>
        <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          Book via WhatsApp
        </a>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const grid     = document.getElementById('moodboard-grid');
  const empty    = document.getElementById('moodboard-empty');
  const cta      = document.getElementById('moodboard-cta');
  const countLbl = document.getElementById('moodboard-count-label');
  const pluralEl = document.getElementById('moodboard-plural');

  function loadMoodboard() {
    if (!window.GYC_MOODBOARD) return;
    const slugs = GYC_MOODBOARD.getSlugs();
    const count = slugs.length;

    // Update count labels
    if (countLbl) countLbl.textContent = count;
    if (pluralEl) pluralEl.textContent = count === 1 ? '' : 's';

    if (count === 0) {
      if (grid)  { grid.style.display = 'none'; grid.innerHTML = ''; }
      if (empty) empty.style.display = 'block';
      if (cta)   cta.style.display   = 'none';
      return;
    }

    if (empty) empty.style.display = 'none';
    if (cta)   cta.style.display   = 'block';
    if (grid)  grid.style.display  = 'grid';

    // Fetch from API
    fetch(window.GYC_URL + '/api/moodboard-items.php?slugs=' + encodeURIComponent(slugs.join(',')))
      .then(r => r.json())
      .then(function (data) {
        if (!data.items || !data.items.length) {
          if (grid)  { grid.style.display = 'none'; grid.innerHTML = ''; }
          if (empty) empty.style.display = 'block';
          return;
        }
        grid.innerHTML = data.items.map(function (item) {
          return '<div class="moodboard-item" data-slug="' + escHtml(item.slug) + '">'
            + '<a href="' + escHtml(window.GYC_URL + '/style-detail.php?slug=' + item.slug) + '">'
            + '<img src="' + escHtml(item.image_url) + '" alt="' + escHtml(item.title) + '" loading="lazy">'
            + '</a>'
            + '<div style="position:absolute;bottom:0;left:0;right:0;padding:.75rem;background:linear-gradient(transparent,rgba(0,0,0,.65));color:#fff;">'
            + '<div style="font-size:.82rem;font-weight:600;margin-bottom:.25rem;">' + escHtml(item.title) + '</div>'
            + (item.price_from ? '<div style="font-size:.75rem;opacity:.85;">from ' + item.price_from_fmt + '</div>' : '')
            + '</div>'
            + '<button class="moodboard-remove" title="Remove from moodboard" onclick="removeMoodboardItem(\'' + escHtml(item.slug) + '\', this)">'
            + '<i data-lucide="x" style="width:12px;height:12px;"></i>'
            + '</button>'
            + '<a href="' + escHtml(window.GYC_URL + '/book-appointment.php?style_id=' + item.id) + '" '
            + 'style="position:absolute;bottom:.6rem;right:.6rem;font-size:.72rem;font-weight:700;background:var(--gyc-gold-500);color:var(--gyc-dark);padding:.2rem .55rem;border-radius:20px;text-decoration:none;">'
            + 'Book'
            + '</a>'
            + '</div>';
        }).join('');
        if (typeof lucide !== 'undefined') lucide.createIcons();
      })
      .catch(function () {
        // Fallback — show slugs without images
        if (grid) grid.innerHTML = '<p style="color:#888;font-size:.88rem;">Could not load moodboard. <a href="<?= SITE_URL ?>/gallery.php">Browse gallery</a></p>';
      });
  }

  function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  window.removeMoodboardItem = function(slug, btn) {
    if (window.GYC_MOODBOARD) GYC_MOODBOARD.remove(slug);
    const item = btn ? btn.closest('.moodboard-item') : null;
    if (item) {
      item.style.opacity = '0';
      item.style.transform = 'scale(.9)';
      item.style.transition = 'opacity .2s, transform .2s';
      setTimeout(function() { loadMoodboard(); }, 220);
    } else {
      loadMoodboard();
    }
  };

  // Clear all
  document.getElementById('clear-all-btn')?.addEventListener('click', function() {
    if (confirm('Clear your entire moodboard?')) {
      if (window.GYC_MOODBOARD) GYC_MOODBOARD.clearAll();
      loadMoodboard();
    }
  });

  // Book all (go to booking with first style)
  document.getElementById('book-all-btn')?.addEventListener('click', function() {
    window.location.href = '<?= SITE_URL ?>/book-appointment.php';
  });

  // Share moodboard
  document.getElementById('share-moodboard-btn')?.addEventListener('click', function() {
    const slugs = window.GYC_MOODBOARD ? GYC_MOODBOARD.getSlugs() : [];
    const box   = document.getElementById('share-box');
    const urlEl = document.getElementById('share-url');
    if (box && urlEl) {
      const shareUrl = '<?= SITE_URL ?>/moodboard.php?styles=' + slugs.join(',');
      urlEl.value = shareUrl;
      box.style.display = box.style.display === 'none' ? 'block' : 'none';
    }
  });

  window.copyShareUrl = function() {
    const urlEl = document.getElementById('share-url');
    if (!urlEl) return;
    urlEl.select();
    document.execCommand('copy');
    const btn = urlEl.nextElementSibling;
    if (btn) { btn.textContent = 'Copied!'; setTimeout(function() { btn.textContent = 'Copy'; }, 2000); }
  };

  // Check for ?styles= in URL (shared moodboard)
  const params = new URLSearchParams(window.location.search);
  const sharedStyles = params.get('styles');
  if (sharedStyles && window.GYC_MOODBOARD) {
    sharedStyles.split(',').filter(Boolean).forEach(function(s) {
      GYC_MOODBOARD.add(s.trim());
    });
  }

  // Load
  loadMoodboard();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
