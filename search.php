<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$q       = trim(sanitize($_GET['q'] ?? ''));
$results = [];
$type    = 'all';

if ($q !== '') {
    $like = '%' . $q . '%';

    // Products
    $products = getDB()->fetchAll(
        "SELECT id, name, slug, price, sale_price, image_url, 'product' AS result_type
         FROM products WHERE is_active=1 AND (name ILIKE $1 OR description ILIKE $1) ORDER BY name LIMIT 20",
        [$like]
    );

    // Gallery styles
    $styles = getDB()->fetchAll(
        "SELECT id, title AS name, slug, image_url, 'style' AS result_type
         FROM gallery_images WHERE is_active=1 AND (title ILIKE $1 OR description ILIKE $1) ORDER BY title LIMIT 10",
        [$like]
    );

    // Blog posts
    $posts = getDB()->fetchAll(
        "SELECT id, title AS name, slug, image_url, 'blog' AS result_type
         FROM blog_posts WHERE status='published' AND (title ILIKE $1 OR excerpt ILIKE $1) ORDER BY published_at DESC LIMIT 10",
        [$like]
    );

    $results = array_merge($products, $styles, $posts);
}

$pageTitle = $q ? 'Search: ' . htmlspecialchars($q) . ' — GYC Naturals' : 'Search — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>


<!-- Search Hero -->
<section style="background:var(--gyc-green-900);padding:3rem 0 2.5rem;">
  <div class="container">
    <h1 style="font-family:'Playfair Display',serif;font-size:2rem;color:#fff;margin-bottom:1.5rem;text-align:center;">
      <?= $q ? 'Results for "<span style="color:var(--gyc-gold)">' . htmlspecialchars($q) . '</span>"' : 'Search GYC Naturals' ?>
    </h1>
    <form method="GET" action="<?= SITE_URL ?>/search.php" style="max-width:600px;margin:0 auto;display:flex;gap:.5rem;">
      <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
             placeholder="Search products, styles, blog posts…"
             class="form-control" style="flex:1;font-size:1rem;padding:.75rem 1rem;"
             autofocus>
      <button type="submit" class="btn btn-gold" style="padding:.75rem 1.5rem;white-space:nowrap;">
        <i data-lucide="search" style="width:18px;height:18px;margin-right:.4rem;"></i>Search
      </button>
    </form>
  </div>
</section>

<!-- Results -->
<section style="padding:3rem 0 5rem;background:#F8FAF9;">
  <div class="container">

    <?php if ($q === ''): ?>
      <!-- No query yet — show popular categories -->
      <p style="text-align:center;color:#888;font-size:1rem;margin-bottom:2rem;">Start typing to search across products, styles and blog posts.</p>
      <div style="display:flex;flex-wrap:wrap;gap:.75rem;justify-content:center;">
        <?php foreach (['knotless braids','faux locs','growth oil','scalp treatment','box braids','hair butter'] as $term): ?>
        <a href="<?= SITE_URL ?>/search.php?q=<?= urlencode($term) ?>"
           style="background:#fff;border:1px solid var(--gyc-green-200);border-radius:2rem;padding:.45rem 1.1rem;font-size:.88rem;color:var(--gyc-green-700);text-decoration:none;">
          <?= htmlspecialchars($term) ?>
        </a>
        <?php endforeach; ?>
      </div>

    <?php elseif (empty($results)): ?>
      <div style="text-align:center;padding:3rem 0;">
        <i data-lucide="search-x" style="width:48px;height:48px;color:#ccc;display:block;margin:0 auto 1rem;"></i>
        <h2 style="font-size:1.3rem;color:#555;margin-bottom:.5rem;">No results found</h2>
        <p style="color:#888;font-size:.92rem;">Try different keywords, or <a href="<?= SITE_URL ?>/shop.php" style="color:var(--gyc-green-600);">browse all products</a>.</p>
      </div>

    <?php else: ?>
      <p style="color:#888;font-size:.88rem;margin-bottom:2rem;"><?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> found</p>

      <div class="products-grid">
        <?php foreach ($results as $item):
          $link  = match($item['result_type']) {
            'product' => SITE_URL . '/product.php?slug=' . urlencode($item['slug']),
            'style'   => SITE_URL . '/style-detail.php?slug=' . urlencode($item['slug']),
            'blog'    => SITE_URL . '/blog-post.php?slug=' . urlencode($item['slug']),
            default   => '#'
          };
          $badge = match($item['result_type']) {
            'product' => 'Product',
            'style'   => 'Style',
            'blog'    => 'Blog',
            default   => ''
          };
        ?>
        <div class="product-card">
          <a href="<?= $link ?>" style="display:block;text-decoration:none;color:inherit;">
            <div class="product-img-wrap">
              <?php if (!empty($item['image_url'])): ?>
              <img src="<?= htmlspecialchars($item['image_url']) ?>"
                   alt="<?= htmlspecialchars($item['name']) ?>"
                   loading="lazy"
                   style="width:100%;height:100%;object-fit:cover;">
              <?php else: ?>
              <div style="width:100%;height:100%;background:var(--gyc-green-50);display:flex;align-items:center;justify-content:center;">
                <i data-lucide="image" style="width:40px;height:40px;color:var(--gyc-green-200);"></i>
              </div>
              <?php endif; ?>
              <span style="position:absolute;top:.6rem;left:.6rem;background:var(--gyc-green-700);color:#fff;font-size:.7rem;font-weight:600;padding:.2rem .55rem;border-radius:2rem;text-transform:uppercase;">
                <?= $badge ?>
              </span>
            </div>
            <div class="product-info">
              <h3 class="product-name"><?= htmlspecialchars($item['name']) ?></h3>
              <?php if ($item['result_type'] === 'product'): ?>
              <div class="product-price">
                <?php if (!empty($item['sale_price']) && $item['sale_price'] < $item['price']): ?>
                  <span class="price-sale">₦<?= number_format($item['sale_price']) ?></span>
                  <span class="price-old">₦<?= number_format($item['price']) ?></span>
                <?php else: ?>
                  ₦<?= number_format($item['price']) ?>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
