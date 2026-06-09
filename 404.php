<?php
define('GYC_ACCESS', true);
http_response_code(404);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = '404 — Page Not Found | GYC Naturals';
$pageDescription = 'Oops! The page you\'re looking for can\'t be found. Browse our hair gallery, shop natural products, or book an appointment.';

require_once __DIR__ . '/includes/header.php';
?>

<main style="min-height:70vh;display:flex;align-items:center;justify-content:center;padding:4rem 1rem;">
  <div style="text-align:center;max-width:560px;margin:0 auto;">

    <!-- GYC Adinkra decoration -->
    <div style="font-size:5rem;line-height:1;margin-bottom:1rem;user-select:none;">🌿</div>

    <!-- 404 number in brand style -->
    <div style="font-family:'Playfair Display',serif;font-size:5.5rem;font-weight:700;color:var(--gyc-green-900);line-height:1;margin-bottom:.5rem;opacity:.15;position:relative;">
      404
    </div>

    <h1 style="font-family:'Playfair Display',serif;font-size:1.65rem;font-weight:700;color:var(--gyc-green-900);margin-bottom:.75rem;margin-top:-2rem;">
      This page wandered off…
    </h1>
    <p style="color:#6B7280;font-size:.95rem;line-height:1.7;margin-bottom:2rem;">
      Maybe the style got updated, the link changed, or it never existed. Let's get your crown back on track.
    </p>

    <!-- Primary CTAs -->
    <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;margin-bottom:2.5rem;">
      <a href="<?= SITE_URL ?>/" class="btn btn-green" style="padding:.7rem 1.5rem;">
        <i data-lucide="home" style="width:16px;height:16px;"></i>
        Back to Home
      </a>
      <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-outline-green" style="padding:.7rem 1.5rem;">
        <i data-lucide="image" style="width:16px;height:16px;"></i>
        Browse Gallery
      </a>
      <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold" style="padding:.7rem 1.5rem;">
        Book Appointment
      </a>
    </div>

    <!-- Quick links grid -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;max-width:420px;margin:0 auto;">
      <?php foreach ([
        ['Shop',      'shop.php',              'shopping-bag'],
        ['Services',  'services.php',          'scissors'],
        ['About Us',  'about.php',             'users'],
        ['Blog',      'blog.php',              'book-open'],
        ['FAQ',       'faq.php',               'help-circle'],
        ['Contact',   'contact.php',           'mail'],
      ] as [$label, $path, $icon]): ?>
      <a href="<?= SITE_URL ?>/<?= $path ?>" style="display:flex;flex-direction:column;align-items:center;gap:.4rem;padding:.85rem .5rem;background:#F8FAF9;border:1px solid #E5E7EB;border-radius:10px;text-decoration:none;color:#374151;font-size:.78rem;font-weight:500;transition:background .15s;" onmouseover="this.style.background='var(--gyc-green-100)'" onmouseout="this.style.background='#F8FAF9'">
        <i data-lucide="<?= $icon ?>" style="width:20px;height:20px;color:var(--gyc-green-700);"></i>
        <?= $label ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- WhatsApp help -->
    <div style="margin-top:2.5rem;padding:1.25rem;background:var(--gyc-green-100);border-radius:12px;display:flex;align-items:center;justify-content:center;gap:.75rem;flex-wrap:wrap;">
      <span style="font-size:.85rem;color:var(--gyc-green-700);">Need help? Chat with us directly:</span>
      <a href="<?= htmlspecialchars(whatsappMessage(getSetting('site_whatsapp') ?: SITE_WHATSAPP, 'Hi GYC Naturals! I followed a broken link and need some help. 🌿')) ?>"
         target="_blank" rel="noopener"
         class="btn btn-whatsapp btn-sm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        WhatsApp Us
      </a>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
