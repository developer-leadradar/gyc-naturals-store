<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$limit    = 9;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $limit;
$category = sanitize($_GET['cat'] ?? '');
$search   = sanitize($_GET['q']   ?? '');

// Fetch posts
$db     = getDB();
$sql    = "SELECT * FROM blog_posts WHERE status = 'published'";
$params = [];
if ($category) { $sql .= " AND category = ?";           $params[] = $category; }
if ($search)   { $sql .= " AND (title LIKE ? OR excerpt LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
$countSql    = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$total       = (int)($db->fetchOne($countSql, $params)['COUNT(*)'] ?? 0);
$sql        .= " ORDER BY published_at DESC LIMIT ? OFFSET ?";
$params[]    = $limit;
$params[]    = $offset;
$posts       = $db->fetchAll($sql, $params);

$totalPages  = (int)ceil($total / $limit);
$categories  = getBlogCategories();

// Featured post (latest) when no filters
$featured    = null;
if (!$category && !$search && $page === 1 && !empty($posts)) {
    $featured = array_shift($posts);
}

$pageTitle       = 'Natural Hair Blog — GYC Naturals';
$pageDescription = 'Expert tips, tutorials, and inspiration for natural hair care, braiding styles, and African fashion from GYC Naturals Lagos.';
require_once __DIR__ . '/includes/header.php';

// Category display names
$catNames = [
    'hair-care'       => 'Hair Care Tips',
    'braiding'        => 'Braiding & Styles',
    'products'        => 'Product Reviews',
    'wellness'        => 'Hair Wellness',
    'tutorials'       => 'Tutorials',
    'fashion'         => 'African Fashion',
    'gyc-news'        => 'GYC News',
];
$catLabel = fn($c) => $catNames[$c] ?? ucwords(str_replace(['-','_'], ' ', $c));
?>

<div style="min-height:72px;"></div>

<!-- ── HERO ── -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900) 0%,var(--gyc-green-700) 100%);padding:5rem 0 4rem;color:#fff;">
  <div class="container" style="max-width:820px;text-align:center;">
    <p style="font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">The GYC Journal</p>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3.2rem);margin-bottom:1.2rem;line-height:1.2;">Natural Hair Stories &amp; Tips</h1>
    <p style="font-size:1rem;opacity:.85;line-height:1.7;margin-bottom:2rem;">From scalp health to style inspo — everything you need for your natural hair journey.</p>
    <!-- Search -->
    <form method="GET" style="display:flex;gap:.5rem;max-width:480px;margin:0 auto;">
      <?php if ($category): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($category) ?>"><?php endif; ?>
      <input type="text" name="q" class="form-control" placeholder="Search articles…"
             value="<?= htmlspecialchars($search) ?>"
             style="flex:1;background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.25);color:#fff;::placeholder{color:rgba(255,255,255,.6);}">
      <button type="submit" class="btn btn-gold">Search</button>
    </form>
  </div>
</section>

<!-- ── CATEGORY FILTER ── -->
<?php if (!empty($categories)): ?>
<section style="background:#fff;border-bottom:1px solid var(--gyc-green-100);padding:.75rem 0;overflow-x:auto;">
  <div class="container">
    <div style="display:flex;gap:.5rem;flex-wrap:nowrap;white-space:nowrap;align-items:center;">
      <a href="<?= SITE_URL ?>/blog.php<?= $search ? '?q='.urlencode($search) : '' ?>"
         class="btn btn-sm <?= !$category ? 'btn-green' : 'btn-outline-green' ?>" style="font-size:.8rem;">All Posts</a>
      <?php foreach ($categories as $cat): ?>
      <a href="<?= SITE_URL ?>/blog.php?cat=<?= urlencode($cat['category']) ?><?= $search ? '&q='.urlencode($search) : '' ?>"
         class="btn btn-sm <?= $category === $cat['category'] ? 'btn-green' : 'btn-outline-green' ?>" style="font-size:.8rem;">
        <?= htmlspecialchars($catLabel($cat['category'])) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── CONTENT ── -->
<section style="padding:4rem 0 5rem;background:#F8FAF9;">
  <div class="container">

    <?php if ($search): ?>
    <p style="font-size:.9rem;color:#6B7280;margin-bottom:2rem;">
      <?= $total ?> result<?= $total !== 1 ? 's' : '' ?> for "<strong><?= htmlspecialchars($search) ?></strong>"
      <a href="<?= SITE_URL ?>/blog.php" style="margin-left:.5rem;color:var(--gyc-terra);">Clear</a>
    </p>
    <?php endif; ?>

    <?php if (empty($posts) && !$featured): ?>
    <div style="text-align:center;padding:5rem 0;color:#888;">
      <i data-lucide="file-text" style="width:48px;height:48px;opacity:.3;margin-bottom:1rem;"></i>
      <h3 style="font-family:'Playfair Display',serif;margin-bottom:.5rem;">No articles found</h3>
      <p style="margin-bottom:1.25rem;">Try a different search or browse all posts.</p>
      <a href="<?= SITE_URL ?>/blog.php" class="btn btn-green">View All Posts</a>
    </div>

    <?php else: ?>

    <!-- ── FEATURED POST (first page, no filters) ── -->
    <?php if ($featured): ?>
    <article style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-xl);overflow:hidden;margin-bottom:3rem;display:grid;grid-template-columns:1fr 1fr;min-height:380px;">
      <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($featured['slug']) ?>"
         style="overflow:hidden;display:block;position:relative;">
        <?php if ($featured['featured_image']): ?>
        <img src="<?= htmlspecialchars($featured['featured_image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>"
             style="width:100%;height:100%;object-fit:cover;transition:transform .5s;" class="blog-featured-img">
        <?php else: ?>
        <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--gyc-green-700),var(--gyc-green-900));display:flex;align-items:center;justify-content:center;">
          <i data-lucide="feather" style="width:64px;height:64px;opacity:.3;color:#fff;"></i>
        </div>
        <?php endif; ?>
        <span style="position:absolute;top:1rem;left:1rem;background:var(--gyc-gold);color:#fff;font-size:.72rem;font-weight:700;padding:.3rem .75rem;border-radius:20px;text-transform:uppercase;letter-spacing:.08em;">Featured</span>
      </a>
      <div style="padding:2.5rem;display:flex;flex-direction:column;justify-content:center;">
        <?php if ($featured['category']): ?>
        <a href="<?= SITE_URL ?>/blog.php?cat=<?= urlencode($featured['category']) ?>"
           style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-600);margin-bottom:.5rem;text-decoration:none;">
          <?= htmlspecialchars($catLabel($featured['category'])) ?>
        </a>
        <?php endif; ?>
        <h2 style="font-family:'Playfair Display',serif;font-size:1.65rem;line-height:1.3;margin-bottom:.85rem;">
          <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($featured['slug']) ?>" style="color:var(--gyc-dark);text-decoration:none;">
            <?= htmlspecialchars($featured['title']) ?>
          </a>
        </h2>
        <?php if ($featured['excerpt']): ?>
        <p style="color:#6B7280;line-height:1.7;font-size:.92rem;margin-bottom:1.5rem;"><?= htmlspecialchars($featured['excerpt']) ?></p>
        <?php endif; ?>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
          <div style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:#9CA3AF;">
            <i data-lucide="user" style="width:14px;height:14px;"></i>
            <?= htmlspecialchars($featured['author'] ?? 'GYC Naturals') ?>
          </div>
          <div style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:#9CA3AF;">
            <i data-lucide="calendar" style="width:14px;height:14px;"></i>
            <?= $featured['published_at'] ? date('jS M Y', strtotime($featured['published_at'])) : '' ?>
          </div>
          <?php if ($featured['read_time']): ?>
          <div style="display:flex;align-items:center;gap:.4rem;font-size:.8rem;color:#9CA3AF;">
            <i data-lucide="clock" style="width:14px;height:14px;"></i>
            <?= (int)$featured['read_time'] ?> min read
          </div>
          <?php endif; ?>
        </div>
        <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($featured['slug']) ?>"
           class="btn btn-green" style="margin-top:1.5rem;align-self:flex-start;">Read Article →</a>
      </div>
    </article>
    <?php endif; ?>

    <!-- ── POSTS GRID ── -->
    <?php if (!empty($posts)): ?>
    <div class="products-grid" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr));">
      <?php foreach ($posts as $post): ?>
      <article style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;display:flex;flex-direction:column;transition:box-shadow .2s;">
        <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>"
           style="display:block;overflow:hidden;aspect-ratio:16/9;position:relative;">
          <?php if ($post['featured_image']): ?>
          <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
               style="width:100%;height:100%;object-fit:cover;transition:transform .4s;" class="blog-card-img">
          <?php else: ?>
          <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--gyc-green-100),var(--gyc-green-200));display:flex;align-items:center;justify-content:center;">
            <i data-lucide="feather" style="width:40px;height:40px;opacity:.3;color:var(--gyc-green-700);"></i>
          </div>
          <?php endif; ?>
        </a>
        <div style="padding:1.4rem;flex:1;display:flex;flex-direction:column;">
          <?php if ($post['category']): ?>
          <a href="<?= SITE_URL ?>/blog.php?cat=<?= urlencode($post['category']) ?>"
             style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-600);margin-bottom:.4rem;text-decoration:none;">
            <?= htmlspecialchars($catLabel($post['category'])) ?>
          </a>
          <?php endif; ?>
          <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;line-height:1.4;margin-bottom:.6rem;">
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>" style="color:var(--gyc-dark);text-decoration:none;"><?= htmlspecialchars($post['title']) ?></a>
          </h3>
          <?php if ($post['excerpt']): ?>
          <p style="font-size:.84rem;color:#6B7280;line-height:1.65;margin-bottom:1rem;flex:1;"><?= htmlspecialchars(substr($post['excerpt'], 0, 120)) ?>…</p>
          <?php endif; ?>
          <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:.75rem;border-top:1px solid var(--gyc-green-100);">
            <span style="font-size:.75rem;color:#9CA3AF;"><?= $post['published_at'] ? date('j M Y', strtotime($post['published_at'])) : '' ?></span>
            <?php if ($post['read_time']): ?>
            <span style="font-size:.75rem;color:#9CA3AF;"><?= (int)$post['read_time'] ?> min</span>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── PAGINATION ── -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:.5rem;margin-top:3rem;">
      <?php
      $qParts = array_filter(['cat' => $category, 'q' => $search]);
      for ($p = 1; $p <= $totalPages; $p++):
        $href = SITE_URL . '/blog.php?' . http_build_query(array_merge($qParts, ['page' => $p]));
      ?>
      <a href="<?= $href ?>" class="btn btn-sm <?= $p === $page ? 'btn-green' : 'btn-outline-green' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
  </div>
</section>

<!-- ── NEWSLETTER CTA ── -->
<section style="background:var(--gyc-green-900);padding:4.5rem 0;color:#fff;text-align:center;">
  <div class="container" style="max-width:560px;">
    <i data-lucide="mail" style="width:40px;height:40px;color:var(--gyc-gold);margin-bottom:1rem;"></i>
    <h2 style="font-family:'Playfair Display',serif;font-size:1.85rem;margin-bottom:.75rem;">Get Hair Tips in Your Inbox</h2>
    <p style="opacity:.8;margin-bottom:2rem;">Join 1,000+ naturals getting weekly tips, product reviews, and exclusive GYC deals.</p>
    <form id="newsletter-footer-form" style="display:flex;gap:.5rem;max-width:420px;margin:0 auto;">
      <input type="email" id="nl-blog-email" class="form-control" placeholder="Your email address"
             style="flex:1;background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.25);color:#fff;">
      <button type="submit" class="btn btn-gold">Subscribe</button>
    </form>
    <p style="font-size:.72rem;opacity:.55;margin-top:.75rem;">No spam. Unsubscribe any time.</p>
  </div>
</section>

<style>
.blog-featured-img:hover, .blog-card-img:hover { transform: scale(1.04); }
</style>

<script>
document.getElementById('newsletter-footer-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const email = document.getElementById('nl-blog-email').value.trim();
  if (!email) return;
  // Stub — wire to api/subscribe.php when ready
  this.innerHTML = '<p style="color:var(--gyc-gold);font-weight:600;">✓ You\'re subscribed! Check your inbox.</p>';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
