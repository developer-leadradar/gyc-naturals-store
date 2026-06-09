<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug  = sanitize($_GET['slug'] ?? '');
$style = $slug ? getGalleryImageBySlug($slug) : null;

if (!$style) {
    header('Location: ' . SITE_URL . '/gallery.php');
    exit;
}

// Related styles (same category, exclude current)
$related = getDB()->fetchAll(
    "SELECT * FROM gallery_images WHERE category_id=? AND id!=? AND is_active=1 ORDER BY RAND() LIMIT 4",
    [$style['category_id'], $style['id']]
);

$pageTitle       = htmlspecialchars($style['title']) . ' — Hair Style | GYC Naturals';
$pageDescription = htmlspecialchars($style['description'] ?? 'Professional ' . $style['title'] . ' at GYC Naturals Lagos. Book online.');
$pageImage       = $style['image_url'];

require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:60px;"></div>

<section style="padding:3rem 0 5rem;">
  <div class="container">
    <nav aria-label="Breadcrumb" style="font-size:0.82rem;color:#888;margin-bottom:2rem;">
      <a href="<?= SITE_URL ?>">Home</a> /
      <a href="<?= SITE_URL ?>/gallery.php">Gallery</a> /
      <?php if ($style['category_name']): ?>
      <a href="<?= SITE_URL ?>/gallery.php?category=<?= urlencode($style['category_slug'] ?? '') ?>"><?= htmlspecialchars($style['category_name']) ?></a> /
      <?php endif; ?>
      <span style="color:var(--gyc-dark);"><?= htmlspecialchars($style['title']) ?></span>
    </nav>

    <div style="display:grid;grid-template-columns:1.1fr 1fr;gap:4rem;align-items:start;">

      <!-- Images -->
      <div>
        <?php if ($style['before_image_url']): ?>
        <!-- Before/After slider -->
        <div class="ba-container" style="border-radius:var(--gyc-radius-lg);overflow:hidden;aspect-ratio:4/5;cursor:col-resize;" data-ba-id="detail-ba">
          <div class="ba-before">
            <img src="<?= htmlspecialchars($style['before_image_url']) ?>" alt="Before" loading="eager" draggable="false" style="width:100%;height:100%;object-fit:cover;">
            <span class="ba-label ba-label--before">Before</span>
          </div>
          <div class="ba-after" style="clip-path:inset(0 50% 0 0);">
            <img src="<?= htmlspecialchars($style['image_url']) ?>" alt="After" loading="eager" draggable="false" style="width:100%;height:100%;object-fit:cover;">
            <span class="ba-label ba-label--after">After</span>
          </div>
          <div class="ba-divider" style="left:50%;">
            <div class="ba-handle"><i data-lucide="chevrons-left-right" style="width:18px;height:18px;"></i></div>
          </div>
        </div>
        <p style="font-size:0.78rem;color:#888;text-align:center;margin-top:0.5rem;">Drag slider to see Before &amp; After</p>
        <?php else: ?>
        <div style="border-radius:var(--gyc-radius-lg);overflow:hidden;aspect-ratio:4/5;">
          <img src="<?= htmlspecialchars($style['image_url']) ?>"
               alt="<?= htmlspecialchars($style['title']) ?>"
               loading="eager"
               style="width:100%;height:100%;object-fit:cover;">
        </div>
        <?php endif; ?>
      </div>

      <!-- Info panel -->
      <div>
        <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:var(--gyc-green-500);">
          <?= htmlspecialchars($style['category_name'] ?? '') ?>
        </span>
        <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.75rem,3vw,2.4rem);color:var(--gyc-dark);margin:0.5rem 0 1rem;line-height:1.15;">
          <?= htmlspecialchars($style['title']) ?>
        </h1>

        <?php if ($style['price_from']): ?>
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
          <span style="font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--gyc-green-700);font-weight:700;">
            <?= formatPrice($style['price_from']) ?>
          </span>
          <?php if ($style['price_to'] && $style['price_to'] != $style['price_from']): ?>
          <span style="color:#888;font-size:0.9rem;">– <?= formatPrice($style['price_to']) ?></span>
          <?php endif; ?>
          <span style="font-size:0.78rem;color:#999;">starting price</span>
        </div>
        <?php endif; ?>

        <?php if ($style['description']): ?>
        <p style="color:#444;line-height:1.75;font-size:0.95rem;margin-bottom:1.5rem;">
          <?= nl2br(htmlspecialchars($style['description'])) ?>
        </p>
        <?php endif; ?>

        <!-- Details grid -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem;padding:1.25rem;background:var(--gyc-green-100);border-radius:var(--gyc-radius);">
          <?php if ($style['duration_hours']): ?>
          <div>
            <span style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--gyc-green-500);">Install Time</span>
            <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:2px;"><?= $style['duration_hours'] ?> hours</div>
          </div>
          <?php endif; ?>
          <?php if ($style['style_type']): ?>
          <div>
            <span style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--gyc-green-500);">Style Type</span>
            <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:2px;"><?= ucwords(str_replace('_', ' ', $style['style_type'])) ?></div>
          </div>
          <?php endif; ?>
          <div>
            <span style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--gyc-green-500);">Lasts</span>
            <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:2px;">6–10 weeks</div>
          </div>
          <div>
            <span style="font-size:0.7rem;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--gyc-green-500);">Deposit</span>
            <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:2px;">30% to confirm</div>
          </div>
        </div>

        <!-- Actions -->
        <div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.5rem;">
          <a href="<?= SITE_URL ?>/book-appointment.php?style_id=<?= $style['id'] ?>" class="btn btn-gold btn-lg" style="justify-content:center;">
            <i data-lucide="calendar-check" style="width:20px;height:20px;"></i>
            Book This Style
          </a>
          <button class="btn btn-outline-green btn-lg" id="save-moodboard"
                  onclick="toggleMoodboard('<?= htmlspecialchars($style['slug']) ?>', this)"
                  style="justify-content:center;">
            <i data-lucide="bookmark" style="width:20px;height:20px;" id="moodboard-icon"></i>
            <span id="moodboard-btn-text">Save to Moodboard</span>
          </button>
        </div>

        <!-- WhatsApp quick enquiry -->
        <?php
        $waPhone = getSetting('site_whatsapp');
        if ($waPhone):
            $waMsg = 'Hello! I am interested in booking: ' . $style['title'] . '. Could you tell me more?';
            $waUrl = whatsappMessage($waPhone, $waMsg);
        ?>
        <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp" style="width:100%;justify-content:center;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          Ask About This Style
        </a>
        <?php endif; ?>

        <!-- Social share -->
        <div style="display:flex;gap:0.75rem;margin-top:1.25rem;align-items:center;">
          <span style="font-size:0.78rem;color:#888;font-weight:600;">SHARE:</span>
          <a href="https://wa.me/?text=<?= urlencode('Check out this style: ' . $style['title'] . ' — ' . SITE_URL . '/style-detail.php?slug=' . $style['slug']) ?>"
             target="_blank" rel="noopener"
             style="color:#25D366;font-size:0.78rem;font-weight:600;">WhatsApp</a>
          <a href="https://www.instagram.com/"
             target="_blank" rel="noopener"
             style="color:#E1306C;font-size:0.78rem;font-weight:600;">Instagram</a>
        </div>
      </div>

    </div><!-- grid -->

    <!-- Related Styles -->
    <?php if (!empty($related)): ?>
    <div style="margin-top:5rem;">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1.75rem;color:var(--gyc-dark);">
        More <?= htmlspecialchars($style['category_name'] ?? 'Styles') ?>
      </h2>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;">
        <?php foreach ($related as $rel): ?>
        <article class="gallery-card">
          <a href="<?= SITE_URL ?>/style-detail.php?slug=<?= urlencode($rel['slug']) ?>" class="gallery-card-img-wrap">
            <img src="<?= htmlspecialchars($rel['image_url']) ?>"
                 alt="<?= htmlspecialchars($rel['title']) ?>"
                 loading="lazy" class="gallery-card-img" style="height:280px;object-fit:cover;">
            <div class="gallery-card-overlay">
              <div class="gallery-card-info">
                <h3 class="gallery-card-title" style="font-size:0.9rem;"><?= htmlspecialchars($rel['title']) ?></h3>
                <?php if ($rel['price_from']): ?><span class="gallery-card-price">from <?= formatPrice($rel['price_from']) ?></span><?php endif; ?>
              </div>
            </div>
          </a>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const slug = <?= json_encode($style['slug']) ?>;
  const btn  = document.getElementById('save-moodboard');
  const txt  = document.getElementById('moodboard-btn-text');
  const icon = document.getElementById('moodboard-icon');

  function updateBtn() {
    const saved = window.GYC_MOODBOARD && GYC_MOODBOARD.isSaved(slug);
    if (txt)  txt.textContent = saved ? 'Saved to Moodboard' : 'Save to Moodboard';
    if (btn)  btn.classList.toggle('btn-green', saved);
  }
  updateBtn();

  // Re-init before/after
  if (typeof initBeforeAfter === 'function') {
    document.querySelectorAll('.ba-container').forEach(initBeforeAfter);
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
