<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) { redirect(SITE_URL . '/blog.php'); exit; }

$post = getBlogPostBySlug($slug);
if (!$post) {
    http_response_code(404);
    $pageTitle = '404 — Post Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div style="min-height:72px;"></div><div class="container" style="text-align:center;padding:6rem 1rem;">
        <h1 style="font-family:\'Playfair Display\',serif;font-size:2.5rem;margin-bottom:1rem;">Post Not Found</h1>
        <p style="color:#6B7280;margin-bottom:2rem;">This article may have been moved or deleted.</p>
        <a href="' . SITE_URL . '/blog.php" class="btn btn-green">← Back to Blog</a>
        </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Increment view count
getDB()->query("UPDATE blog_posts SET view_count = COALESCE(view_count,0)+1 WHERE id = ?", [$post['id']]);

// Related posts (same category, excluding current)
$related = getDB()->fetchAll(
    "SELECT id, title, slug, featured_image, published_at, read_time, category
     FROM blog_posts
     WHERE status = 'published' AND id != ? AND category = ?
     ORDER BY published_at DESC LIMIT 3",
    [$post['id'], $post['category'] ?? '']
);
if (empty($related)) {
    $related = getDB()->fetchAll(
        "SELECT id, title, slug, featured_image, published_at, read_time, category
         FROM blog_posts
         WHERE status = 'published' AND id != ?
         ORDER BY published_at DESC LIMIT 3",
        [$post['id']]
    );
}

$catNames = [
    'hair-care' => 'Hair Care Tips',
    'braiding'  => 'Braiding & Styles',
    'products'  => 'Product Reviews',
    'wellness'  => 'Hair Wellness',
    'tutorials' => 'Tutorials',
    'fashion'   => 'African Fashion',
    'gyc-news'  => 'GYC News',
];
$catLabel = fn($c) => $catNames[$c] ?? ucwords(str_replace(['-','_'], ' ', (string)$c));

// Parse tags
$tags = [];
if (!empty($post['tags'])) {
    $tags = array_filter(array_map('trim', explode(',', $post['tags'])));
}

$pageTitle       = htmlspecialchars($post['title']) . ' — GYC Naturals';
$pageDescription = $post['excerpt'] ? htmlspecialchars($post['excerpt']) : 'Read this article on the GYC Naturals natural hair blog.';
$ogImage         = $post['featured_image'] ?? '';

// JSON-LD Article schema
$articleJsonLd = json_encode([
    '@context'        => 'https://schema.org',
    '@type'           => 'Article',
    'headline'        => $post['title'],
    'description'     => $post['excerpt'] ?? '',
    'image'           => $post['featured_image'] ?? SITE_URL . '/assets/images/og-default.jpg',
    'author'          => [
        '@type' => 'Person',
        'name'  => $post['author'] ?? 'GYC Naturals Team',
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name'  => 'GYC Naturals',
        'logo'  => ['@type' => 'ImageObject', 'url' => SITE_URL . '/assets/images/gyc-logo.png'],
    ],
    'datePublished' => $post['published_at'] ? date('c', strtotime($post['published_at'])) : '',
    'dateModified'  => $post['published_at'] ? date('c', strtotime($post['published_at'])) : '',
    'url'           => SITE_URL . '/blog-post.php?slug=' . urlencode($post['slug']),
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => SITE_URL . '/blog-post.php?slug=' . urlencode($post['slug'])],
    'keywords'      => $post['tags'] ?? '',
    'articleSection'=> $catLabel($post['category'] ?? ''),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>
<script type="application/ld+json"><?= $articleJsonLd ?></script>

<div style="min-height:72px;"></div>

<!-- ── HERO / FEATURED IMAGE ── -->
<?php if ($post['featured_image']): ?>
<div style="height:clamp(240px,40vw,500px);overflow:hidden;position:relative;">
  <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
       style="width:100%;height:100%;object-fit:cover;">
  <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,rgba(0,0,0,.55) 100%);"></div>
</div>
<?php else: ?>
<div style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));height:220px;"></div>
<?php endif; ?>

<!-- ── ARTICLE BODY ── -->
<section style="padding:3rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 300px;gap:3rem;align-items:start;">

      <!-- ── MAIN COLUMN ── -->
      <main>
        <!-- Breadcrumb -->
        <nav style="display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:#9CA3AF;margin-bottom:1.5rem;flex-wrap:wrap;">
          <a href="<?= SITE_URL ?>" style="color:var(--gyc-green-600);text-decoration:none;">Home</a>
          <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
          <a href="<?= SITE_URL ?>/blog.php" style="color:var(--gyc-green-600);text-decoration:none;">Blog</a>
          <?php if ($post['category']): ?>
          <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
          <a href="<?= SITE_URL ?>/blog.php?cat=<?= urlencode($post['category']) ?>" style="color:var(--gyc-green-600);text-decoration:none;"><?= htmlspecialchars($catLabel($post['category'])) ?></a>
          <?php endif; ?>
          <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
          <span><?= htmlspecialchars(substr($post['title'], 0, 40)) ?>…</span>
        </nav>

        <!-- Category pill -->
        <?php if ($post['category']): ?>
        <a href="<?= SITE_URL ?>/blog.php?cat=<?= urlencode($post['category']) ?>"
           style="display:inline-block;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-600);background:var(--gyc-green-100);padding:.25rem .65rem;border-radius:20px;text-decoration:none;margin-bottom:1rem;">
          <?= htmlspecialchars($catLabel($post['category'])) ?>
        </a>
        <?php endif; ?>

        <!-- Title -->
        <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.65rem,3.5vw,2.5rem);line-height:1.25;margin-bottom:1.25rem;color:var(--gyc-dark);">
          <?= htmlspecialchars($post['title']) ?>
        </h1>

        <!-- Meta row -->
        <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;padding-bottom:1.25rem;border-bottom:2px solid var(--gyc-green-100);margin-bottom:2rem;">
          <div style="display:flex;align-items:center;gap:.5rem;">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--gyc-green-700);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;">
              <?= strtoupper(substr($post['author'] ?? 'G', 0, 1)) ?>
            </div>
            <div>
              <div style="font-weight:600;font-size:.85rem;color:var(--gyc-dark);"><?= htmlspecialchars($post['author'] ?? 'GYC Naturals') ?></div>
              <div style="font-size:.72rem;color:#9CA3AF;">Author</div>
            </div>
          </div>
          <?php if ($post['published_at']): ?>
          <div style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;color:#9CA3AF;">
            <i data-lucide="calendar" style="width:14px;height:14px;"></i>
            <?= date('jS M Y', strtotime($post['published_at'])) ?>
          </div>
          <?php endif; ?>
          <?php if ($post['read_time']): ?>
          <div style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;color:#9CA3AF;">
            <i data-lucide="clock" style="width:14px;height:14px;"></i>
            <?= (int)$post['read_time'] ?> min read
          </div>
          <?php endif; ?>
          <?php if (!empty($post['view_count'])): ?>
          <div style="display:flex;align-items:center;gap:.35rem;font-size:.8rem;color:#9CA3AF;">
            <i data-lucide="eye" style="width:14px;height:14px;"></i>
            <?= number_format($post['view_count']) ?> views
          </div>
          <?php endif; ?>
        </div>

        <!-- Body -->
        <div class="blog-post-body">
          <?= $post['body'] /* Already HTML from CMS — do not escape */ ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
        <div style="margin-top:2.5rem;padding-top:1.5rem;border-top:1px solid var(--gyc-green-100);display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;">
          <span style="font-size:.78rem;font-weight:600;color:#9CA3AF;margin-right:.25rem;">Tags:</span>
          <?php foreach ($tags as $tag): ?>
          <a href="<?= SITE_URL ?>/blog.php?q=<?= urlencode($tag) ?>"
             style="display:inline-block;font-size:.75rem;background:var(--gyc-green-100);color:var(--gyc-green-700);padding:.2rem .6rem;border-radius:20px;text-decoration:none;font-weight:600;">
            #<?= htmlspecialchars($tag) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Share -->
        <div style="margin-top:2rem;padding:1.5rem;background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);">
          <p style="font-size:.85rem;font-weight:700;color:var(--gyc-dark);margin-bottom:1rem;">Share this article</p>
          <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
            <?php
            $shareUrl   = SITE_URL . '/blog-post.php?slug=' . urlencode($post['slug']);
            $shareTitle = urlencode($post['title']);
            $waShareUrl = 'https://wa.me/?text=' . urlencode($post['title'] . ' — ' . $shareUrl);
            ?>
            <a href="<?= $waShareUrl ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
              Share on WhatsApp
            </a>
            <button onclick="copyPostLink(this)" class="btn btn-outline-green btn-sm">
              <i data-lucide="link" style="width:14px;height:14px;"></i>
              Copy Link
            </button>
          </div>
        </div>

        <!-- Author bio -->
        <div style="margin-top:2rem;padding:1.5rem;background:var(--gyc-green-100);border-radius:var(--gyc-radius-lg);display:flex;gap:1.25rem;align-items:flex-start;">
          <div style="width:52px;height:52px;border-radius:50%;background:var(--gyc-green-700);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1.1rem;flex-shrink:0;">
            <?= strtoupper(substr($post['author'] ?? 'G', 0, 1)) ?>
          </div>
          <div>
            <div style="font-weight:700;color:var(--gyc-dark);margin-bottom:.25rem;"><?= htmlspecialchars($post['author'] ?? 'GYC Naturals Team') ?></div>
            <div style="font-size:.82rem;color:#374151;line-height:1.6;">
              A passionate hair care expert at GYC Naturals, dedicated to celebrating natural African beauty through education, quality products, and transformative salon experiences.
            </div>
          </div>
        </div>

      </main>

      <!-- ── SIDEBAR ── -->
      <aside style="position:sticky;top:90px;">

        <!-- Book CTA -->
        <div style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));border-radius:var(--gyc-radius-lg);padding:1.75rem;color:#fff;margin-bottom:1.5rem;text-align:center;">
          <i data-lucide="scissors" style="width:32px;height:32px;color:var(--gyc-gold);margin-bottom:.75rem;"></i>
          <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:.5rem;">Book Your Style</h3>
          <p style="font-size:.82rem;opacity:.8;margin-bottom:1.25rem;line-height:1.6;">Transform your crown at our Victoria Island salon.</p>
          <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold w-full" style="font-size:.85rem;">Book Appointment</a>
        </div>

        <!-- Related posts -->
        <?php if (!empty($related)): ?>
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem;margin-bottom:1.5rem;">
          <h3 style="font-family:'Playfair Display',serif;font-size:1rem;margin-bottom:1.25rem;">Related Articles</h3>
          <div style="display:flex;flex-direction:column;gap:1rem;">
            <?php foreach ($related as $rel): ?>
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($rel['slug']) ?>"
               style="display:flex;gap:.75rem;text-decoration:none;align-items:flex-start;">
              <?php if ($rel['featured_image']): ?>
              <img src="<?= htmlspecialchars($rel['featured_image']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>"
                   style="width:60px;height:60px;object-fit:cover;border-radius:var(--gyc-radius);flex-shrink:0;">
              <?php else: ?>
              <div style="width:60px;height:60px;border-radius:var(--gyc-radius);background:var(--gyc-green-100);flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="feather" style="width:20px;height:20px;color:var(--gyc-green-500);opacity:.5;"></i>
              </div>
              <?php endif; ?>
              <div>
                <div style="font-size:.83rem;font-weight:600;color:var(--gyc-dark);line-height:1.4;margin-bottom:.2rem;"><?= htmlspecialchars($rel['title']) ?></div>
                <?php if ($rel['read_time']): ?>
                <div style="font-size:.72rem;color:#9CA3AF;"><?= (int)$rel['read_time'] ?> min read</div>
                <?php endif; ?>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Shop CTA -->
        <div style="background:#FEF9EC;border:1.5px solid #F59E0B;border-radius:var(--gyc-radius-lg);padding:1.5rem;text-align:center;">
          <i data-lucide="shopping-bag" style="width:28px;height:28px;color:var(--gyc-gold);margin-bottom:.6rem;"></i>
          <h3 style="font-family:'Playfair Display',serif;font-size:1rem;margin-bottom:.4rem;">Shop Natural Products</h3>
          <p style="font-size:.8rem;color:#92400E;margin-bottom:1rem;">Our curated range — designed for African hair.</p>
          <a href="<?= SITE_URL ?>/shop.php" class="btn btn-gold w-full btn-sm">Browse Products</a>
        </div>

      </aside>
    </div>

    <!-- ── MORE ARTICLES ── -->
    <?php if (!empty($related)): ?>
    <div style="margin-top:4rem;padding-top:3rem;border-top:2px solid var(--gyc-green-100);">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:2rem;text-align:center;">More Articles You'll Love</h2>
      <div class="products-grid" style="grid-template-columns:repeat(auto-fill,minmax(280px,1fr));">
        <?php foreach ($related as $rel): ?>
        <article style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;">
          <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($rel['slug']) ?>" style="display:block;aspect-ratio:16/9;overflow:hidden;">
            <?php if ($rel['featured_image']): ?>
            <img src="<?= htmlspecialchars($rel['featured_image']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>"
                 style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
            <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--gyc-green-100),var(--gyc-green-200));"></div>
            <?php endif; ?>
          </a>
          <div style="padding:1.25rem;">
            <?php if ($rel['category']): ?>
            <span style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-600);"><?= htmlspecialchars($catLabel($rel['category'])) ?></span>
            <?php endif; ?>
            <h3 style="font-family:'Playfair Display',serif;font-size:1rem;line-height:1.35;margin:.4rem 0 .75rem;">
              <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($rel['slug']) ?>" style="color:var(--gyc-dark);text-decoration:none;"><?= htmlspecialchars($rel['title']) ?></a>
            </h3>
            <div style="display:flex;justify-content:space-between;font-size:.75rem;color:#9CA3AF;">
              <span><?= $rel['published_at'] ? date('j M Y', strtotime($rel['published_at'])) : '' ?></span>
              <?php if ($rel['read_time']): ?><span><?= (int)$rel['read_time'] ?> min</span><?php endif; ?>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<script>
function copyPostLink(btn) {
  const url = '<?= addslashes(SITE_URL . '/blog-post.php?slug=' . urlencode($post['slug'])) ?>';
  if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(function() {
      btn.textContent = 'Link Copied!';
      setTimeout(function() { btn.innerHTML = '<i data-lucide="link" style="width:14px;height:14px;"></i> Copy Link'; lucide.createIcons(); }, 2500);
    });
  } else {
    const ta = document.createElement('textarea');
    ta.value = url;
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    btn.textContent = 'Copied!';
    setTimeout(function() { btn.textContent = 'Copy Link'; }, 2500);
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
