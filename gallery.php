<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Style Gallery — Browse Hair Styles | GYC Naturals Calabar';
$pageDescription = 'Browse our full gallery of box braids, knotless braids, cornrows, Senegalese twists, weaves and natural hair styles. Book your favourite style online.';
$pageKeywords    = 'box braids gallery Calabar, knotless braids styles, cornrow designs Nigeria, GYC Naturals gallery';

$categories     = getAllGalleryCategories(true);
$activeCategory = sanitize($_GET['category'] ?? 'all');
$activeCatObj   = null;

if ($activeCategory !== 'all') {
    foreach ($categories as $c) {
        if ($c['slug'] === $activeCategory) { $activeCatObj = $c; break; }
    }
}

$filters = [];
if ($activeCatObj) $filters['category_slug'] = $activeCategory;

$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$total   = countGalleryImages($filters);
$images  = getGalleryImages($filters, $perPage, $offset);
$pages   = (int)ceil($total / $perPage);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero" style="background: linear-gradient(135deg, var(--gyc-green-900) 0%, var(--gyc-green-700) 100%); padding: 5rem 0 3.5rem;">
  <div class="container" style="text-align:center;color:#fff;">
    <span class="section-eyebrow" style="color:var(--gyc-gold-300);">Our Work</span>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.5rem);color:#fff;margin:0.5rem 0 1rem;">
      Style Gallery
    </h1>
    <p style="color:rgba(255,255,255,0.78);max-width:550px;margin:0 auto 1.5rem;font-size:1rem;line-height:1.65;">
      Browse <?= $total ?>+ styles, save your favourites to your moodboard, and book the look you love.
    </p>
    <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold">Book Appointment</a>
      <a href="<?= SITE_URL ?>/moodboard.php" class="btn btn-outline-white">
        <i data-lucide="layout-grid" style="width:16px;height:16px;"></i>
        My Moodboard (<span id="moodboard-count-nav">0</span>)
      </a>
    </div>
  </div>
</section>

<!-- Gallery Content -->
<section style="padding:3rem 0 5rem;" aria-labelledby="gallery-heading">
  <div class="container">

    <!-- Category filter tabs -->
    <div class="filter-chips" role="tablist" aria-label="Filter by style category" style="margin-bottom:2rem;">
      <a href="<?= SITE_URL ?>/gallery.php"
         class="chip <?= $activeCategory === 'all' ? 'chip--active' : '' ?>"
         role="tab" aria-selected="<?= $activeCategory === 'all' ? 'true' : 'false' ?>">
        All Styles (<?= countGalleryImages() ?>)
      </a>
      <?php foreach ($categories as $cat): ?>
      <a href="<?= SITE_URL ?>/gallery.php?category=<?= urlencode($cat['slug']) ?>"
         class="chip <?= $activeCategory === $cat['slug'] ? 'chip--active' : '' ?>"
         role="tab" aria-selected="<?= $activeCategory === $cat['slug'] ? 'true' : 'false' ?>">
        <?= htmlspecialchars($cat['name']) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Gallery heading + sort -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap;">
      <div>
        <h2 id="gallery-heading" style="font-family:'Playfair Display',serif;font-size:1.4rem;color:var(--gyc-dark);margin:0;">
          <?= $activeCatObj ? htmlspecialchars($activeCatObj['name']) : 'All Styles' ?>
        </h2>
        <span style="font-size:0.82rem;color:#888;"><?= $total ?> styles available</span>
      </div>
      <a href="<?= SITE_URL ?>/moodboard.php" class="btn btn-outline-green btn-sm">
        <i data-lucide="heart" style="width:14px;height:14px;"></i>
        View Moodboard
      </a>
    </div>

    <!-- Gallery grid -->
    <?php if (empty($images)): ?>
    <div style="text-align:center;padding:4rem;color:#888;">
      <i data-lucide="image-off" style="width:48px;height:48px;margin-bottom:1rem;opacity:0.4;"></i>
      <p>No styles found in this category yet.</p>
      <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-green" style="margin-top:1rem;">View All Styles</a>
    </div>
    <?php else: ?>
    <div class="gallery-masonry" id="gallery-grid">
      <?php foreach ($images as $img): ?>
      <article class="gallery-card" data-category="<?= htmlspecialchars($img['category_slug'] ?? '') ?>">
        <a href="<?= SITE_URL ?>/style-detail.php?slug=<?= urlencode($img['slug']) ?>" class="gallery-card-img-wrap">
          <img src="<?= htmlspecialchars($img['image_url']) ?>"
               alt="<?= htmlspecialchars($img['title']) ?>"
               loading="lazy"
               width="400" height="500"
               class="gallery-card-img">
          <div class="gallery-card-overlay">
            <button class="gallery-bookmark"
                    data-slug="<?= htmlspecialchars($img['slug']) ?>"
                    aria-label="Save to moodboard"
                    onclick="toggleMoodboard('<?= htmlspecialchars($img['slug']) ?>', this); event.preventDefault(); event.stopPropagation();">
              <i data-lucide="bookmark" style="width:16px;height:16px;"></i>
            </button>
            <div class="gallery-card-info">
              <h3 class="gallery-card-title"><?= htmlspecialchars($img['title']) ?></h3>
              <span style="font-size:0.75rem;color:rgba(255,255,255,0.7);">
                <?= htmlspecialchars($img['category_name'] ?? '') ?>
                <?php if ($img['duration_hours']): ?>
                  · <?= $img['duration_hours'] ?>h
                <?php endif; ?>
              </span>
              <?php if ($img['price_from']): ?>
              <span class="gallery-card-price">from <?= formatPrice($img['price_from']) ?></span>
              <?php endif; ?>
              <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
                <a href="<?= SITE_URL ?>/style-detail.php?slug=<?= urlencode($img['slug']) ?>" class="btn btn-outline-white btn-sm" style="font-size:0.75rem;">Details</a>
                <a href="<?= SITE_URL ?>/book-appointment.php?style_id=<?= $img['id'] ?>" class="btn btn-gold btn-sm" style="font-size:0.75rem;" onclick="event.stopPropagation();">Book</a>
              </div>
            </div>
          </div>
        </a>
      </article>
      <?php endforeach; ?>
    </div><!-- #gallery-grid -->

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <nav class="pagination-wrap" style="margin-top:3rem;display:flex;justify-content:center;" aria-label="Gallery pagination">
      <?= pagination($page, $pages, SITE_URL . '/gallery.php?' . ($activeCategory !== 'all' ? 'category=' . urlencode($activeCategory) . '&' : '') . 'page=') ?>
    </nav>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</section>

<!-- CTA Strip -->
<section style="background:var(--gyc-green-100);padding:3rem 0;">
  <div class="container" style="text-align:center;">
    <h3 style="font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:0.75rem;">Can't decide? Build a Moodboard.</h3>
    <p style="color:#555;margin-bottom:1.5rem;max-width:420px;margin-left:auto;margin-right:auto;">
      Bookmark styles you love, then share the link with us when you book. We will make it happen.
    </p>
    <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/moodboard.php" class="btn btn-green">
        <i data-lucide="layout-grid" style="width:18px;height:18px;"></i>
        View My Moodboard
      </a>
      <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold">Book Appointment</a>
    </div>
  </div>
</section>

<script>
// Update moodboard count in nav
document.addEventListener('DOMContentLoaded', function () {
  const countEl = document.getElementById('moodboard-count-nav');
  if (countEl && window.GYC_MOODBOARD) {
    countEl.textContent = GYC_MOODBOARD.count();
  }
  // Mark bookmarked items
  document.querySelectorAll('.gallery-bookmark').forEach(function (btn) {
    const slug = btn.dataset.slug;
    if (window.GYC_MOODBOARD && GYC_MOODBOARD.isSaved(slug)) {
      btn.classList.add('saved');
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
