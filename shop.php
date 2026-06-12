<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Filters from URL — ?tab=bundles maps to bundles category
$tabParam      = sanitize($_GET['tab'] ?? '');
$activeCatSlug = sanitize($_GET['category'] ?? ($tabParam === 'bundles' ? 'kits-bundles' : ''));
$activeSort    = sanitize($_GET['sort']     ?? 'default');
$activeConcern = sanitize($_GET['concern']  ?? '');
$activeHair    = sanitize($_GET['hair']     ?? '');
$minPrice      = (int)($_GET['min_price']   ?? 0);
$maxPrice      = (int)($_GET['max_price']   ?? 0);
$searchQ       = sanitize($_GET['q']        ?? '');
$page          = max(1, (int)($_GET['page'] ?? 1));
$perPage       = 12;
$offset        = ($page - 1) * $perPage;

// Get active category object
$categories  = getAllCategories();
$activeCatId = 0;
foreach ($categories as $cat) {
    if ($cat['slug'] === $activeCatSlug) { $activeCatId = $cat['id']; break; }
}

// Build filters array
$filters = [];
if ($activeCatId)    $filters['category_id']   = $activeCatId;
if ($activeConcern)  $filters['concern']        = $activeConcern;
if ($activeHair)     $filters['hair_type']      = $activeHair;
if ($minPrice)       $filters['min_price']      = $minPrice;
if ($maxPrice)       $filters['max_price']      = $maxPrice;
if ($searchQ)        $filters['search']         = $searchQ;
if ($activeSort)     $filters['sort']           = $activeSort;

$total    = countProducts($filters);
$products = getAllProducts($filters, $perPage, $offset);
$pages    = (int)ceil($total / $perPage);

// Featured bundles
$bundles = getAllBundles();

$pageTitle       = 'Shop Natural Hair Products — GYC Naturals Calabar';
$pageDescription = 'Buy authentic African hair care products online. Shea butter, castor oil, growth serums, curl defining creams and more. Fast delivery across Nigeria.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:5rem 0 3rem;">
  <div class="container" style="text-align:center;color:#fff;">
    <span class="section-eyebrow" style="color:var(--gyc-gold-300);">Natural Hair Care</span>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,4vw,3rem);color:#fff;margin:.5rem 0 1rem;">
      The GYC Naturals Shop
    </h1>
    <p style="color:rgba(255,255,255,.78);max-width:500px;margin:0 auto;font-size:1rem;line-height:1.65;">
      Carefully curated natural hair products — from growth oils to deep conditioners — made for African hair textures.
    </p>

    <!-- Search bar -->
    <form action="<?= SITE_URL ?>/shop.php" method="GET" style="display:flex;max-width:420px;margin:1.75rem auto 0;gap:.5rem;">
      <input type="search" name="q" value="<?= htmlspecialchars($searchQ) ?>"
             placeholder="Search products…"
             class="form-control"
             style="background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.25);color:#fff;flex:1;">
      <button type="submit" class="btn btn-gold">
        <i data-lucide="search" style="width:18px;height:18px;"></i>
      </button>
    </form>
  </div>
</section>

<div class="container" style="padding:2.5rem 0 5rem;">
  <div style="display:grid;grid-template-columns:240px 1fr;gap:2.5rem;align-items:start;">

    <!-- Sidebar filters -->
    <aside>
      <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem;position:sticky;top:calc(var(--gyc-nav-height)+1rem);">
        <h3 style="font-size:.9rem;font-weight:700;color:var(--gyc-dark);margin-bottom:1.25rem;display:flex;align-items:center;gap:.5rem;">
          <i data-lucide="sliders-horizontal" style="width:16px;height:16px;color:var(--gyc-green-600);"></i>
          Filter Products
        </h3>

        <form id="filter-form" action="<?= SITE_URL ?>/shop.php" method="GET">
          <?php if ($searchQ): ?><input type="hidden" name="q" value="<?= htmlspecialchars($searchQ) ?>"><?php endif; ?>

          <!-- Categories -->
          <div style="margin-bottom:1.5rem;">
            <h4 style="font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.75rem;">Category</h4>
            <div style="display:flex;flex-direction:column;gap:.4rem;">
              <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;cursor:pointer;">
                <input type="radio" name="category" value="" <?= !$activeCatSlug ? 'checked' : '' ?> onchange="this.form.submit()">
                <span>All Products (<?= countProducts() ?>)</span>
              </label>
              <?php foreach ($categories as $cat): ?>
              <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;cursor:pointer;">
                <input type="radio" name="category" value="<?= $cat['slug'] ?>" <?= $activeCatSlug === $cat['slug'] ? 'checked' : '' ?> onchange="this.form.submit()">
                <span><?= htmlspecialchars($cat['name']) ?> (<?= countProducts(['category_id' => $cat['id']]) ?>)</span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Hair Type -->
          <div style="margin-bottom:1.5rem;">
            <h4 style="font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.75rem;">Hair Type</h4>
            <select name="hair" class="form-control" style="font-size:.83rem;" onchange="this.form.submit()">
              <option value="">All types</option>
              <option value="4C" <?= $activeHair === '4C' ? 'selected' : '' ?>>4C Coily</option>
              <option value="4B" <?= $activeHair === '4B' ? 'selected' : '' ?>>4B Coily</option>
              <option value="4A" <?= $activeHair === '4A' ? 'selected' : '' ?>>4A Curly-Coily</option>
              <option value="all" <?= $activeHair === 'all' ? 'selected' : '' ?>>All Hair Types</option>
            </select>
          </div>

          <!-- Concern -->
          <div style="margin-bottom:1.5rem;">
            <h4 style="font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.75rem;">Main Concern</h4>
            <div style="display:flex;flex-direction:column;gap:.4rem;">
              <?php
              $concerns = ['growth' => 'Growth', 'moisture' => 'Moisture', 'breakage' => 'Breakage & Strength', 'definition' => 'Curl Definition'];
              foreach ($concerns as $val => $label):
              ?>
              <label style="display:flex;align-items:center;gap:.5rem;font-size:.83rem;cursor:pointer;">
                <input type="checkbox" name="concern" value="<?= $val ?>" <?= $activeConcern === $val ? 'checked' : '' ?> onchange="this.form.submit()">
                <span><?= $label ?></span>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Price Range -->
          <div style="margin-bottom:1.5rem;">
            <h4 style="font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.75rem;">Price Range</h4>
            <div style="display:flex;gap:.5rem;align-items:center;">
              <input type="number" name="min_price" value="<?= $minPrice ?: '' ?>" placeholder="Min" class="form-control" style="font-size:.82rem;width:90px;">
              <span style="color:#888;font-size:.82rem;">–</span>
              <input type="number" name="max_price" value="<?= $maxPrice ?: '' ?>" placeholder="Max" class="form-control" style="font-size:.82rem;width:90px;">
            </div>
            <button type="submit" class="btn btn-green btn-sm" style="width:100%;margin-top:.75rem;justify-content:center;">Apply</button>
          </div>

          <?php if ($activeCatSlug || $activeConcern || $activeHair || $minPrice || $maxPrice): ?>
          <a href="<?= SITE_URL ?>/shop.php<?= $searchQ ? '?q='.urlencode($searchQ) : '' ?>"
             class="btn btn-outline-green btn-sm" style="width:100%;justify-content:center;margin-top:.5rem;">
            <i data-lucide="x" style="width:14px;height:14px;"></i>
            Clear Filters
          </a>
          <?php endif; ?>
        </form>

        <!-- Hair quiz CTA -->
        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gyc-green-100);text-align:center;">
          <div style="font-size:1.5rem;margin-bottom:.4rem;">🧬</div>
          <p style="font-size:.8rem;color:#555;margin-bottom:.75rem;line-height:1.5;">Not sure what you need? Take our free hair quiz.</p>
          <a href="<?= SITE_URL ?>/quiz.php" class="btn btn-gold btn-sm" style="width:100%;justify-content:center;">
            Take Hair Quiz
          </a>
        </div>
      </div>
    </aside>

    <!-- Product grid -->
    <div>
      <!-- Sort & count bar -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
        <div>
          <?php if ($searchQ): ?>
          <h2 style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin:0;">
            Results for "<em><?= htmlspecialchars($searchQ) ?></em>" — <?= $total ?> product<?= $total !== 1 ? 's' : '' ?>
          </h2>
          <?php else: ?>
          <p style="font-size:.88rem;color:#666;margin:0;"><?= $total ?> product<?= $total !== 1 ? 's' : '' ?></p>
          <?php endif; ?>
        </div>
        <form action="<?= SITE_URL ?>/shop.php" method="GET" style="display:flex;align-items:center;gap:.5rem;">
          <?php foreach ($_GET as $k => $v): if ($k !== 'sort' && $k !== 'page'): ?>
          <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>">
          <?php endif; endforeach; ?>
          <label style="font-size:.82rem;color:#888;">Sort by:</label>
          <select name="sort" class="form-control" style="font-size:.83rem;padding:.35rem .65rem;width:auto;" onchange="this.form.submit()">
            <option value="default"    <?= $activeSort === 'default'    ? 'selected' : '' ?>>Featured</option>
            <option value="newest"     <?= $activeSort === 'newest'     ? 'selected' : '' ?>>Newest</option>
            <option value="price_asc"  <?= $activeSort === 'price_asc'  ? 'selected' : '' ?>>Price: Low → High</option>
            <option value="price_desc" <?= $activeSort === 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
            <option value="rating"     <?= $activeSort === 'rating'     ? 'selected' : '' ?>>Top Rated</option>
          </select>
        </form>
      </div>

      <?php if (empty($products)): ?>
      <!-- Empty state -->
      <div style="text-align:center;padding:4rem 2rem;background:#fff;border-radius:var(--gyc-radius-lg);border:1.5px solid var(--gyc-green-100);">
        <i data-lucide="search-x" style="width:52px;height:52px;margin-bottom:1rem;opacity:.35;"></i>
        <h3 style="font-family:'Playfair Display',serif;margin-bottom:.5rem;">No products found</h3>
        <p style="color:#888;font-size:.9rem;margin-bottom:1.25rem;">Try adjusting your filters or search terms.</p>
        <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
          <a href="<?= SITE_URL ?>/shop.php" class="btn btn-green">View All Products</a>
          <a href="<?= SITE_URL ?>/quiz.php" class="btn btn-gold">Take Hair Quiz</a>
        </div>
      </div>

      <?php else: ?>
      <!-- Products -->
      <div class="products-grid">
        <?php foreach ($products as $prod): ?>
        <?php
        $stockStatus = '';
        if ($prod['stock_quantity'] <= 0) $stockStatus = 'out';
        elseif ($prod['stock_quantity'] <= 5) $stockStatus = 'low';
        ?>
        <article class="product-card">
          <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>" class="product-card-img-wrap">
            <img src="<?= htmlspecialchars($prod['image']) ?>"
                 alt="<?= htmlspecialchars($prod['name']) ?>"
                 loading="lazy"
                 class="product-card-img">
            <?php if ($prod['is_featured']): ?>
            <span class="product-badge product-badge--featured">⭐ Best Seller</span>
            <?php elseif ($stockStatus === 'low'): ?>
            <span class="product-badge product-badge--low">Only <?= $prod['stock_quantity'] ?> left</span>
            <?php elseif ($stockStatus === 'out'): ?>
            <span class="product-badge product-badge--low" style="background:#EF4444;">Out of Stock</span>
            <?php endif; ?>
            <button class="product-wishlist <?= isLoggedIn() ? '' : 'login-req' ?>"
                    data-product-id="<?= $prod['id'] ?>"
                    aria-label="Save to wishlist"
                    onclick="toggleWishlist(<?= $prod['id'] ?>, this); event.preventDefault();">
              <i data-lucide="heart" style="width:16px;height:16px;"></i>
            </button>
          </a>
          <div class="product-card-body">
            <?php if ($prod['category_name']): ?>
            <span class="product-tag"><?= htmlspecialchars($prod['category_name']) ?></span>
            <?php endif; ?>
            <h3 class="product-card-name">
              <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>"><?= htmlspecialchars($prod['name']) ?></a>
            </h3>
            <?php if ($prod['concern']): ?>
            <span class="concern-chip"><?= ucwords(htmlspecialchars($prod['concern'])) ?></span>
            <?php endif; ?>
            <?php if ($prod['rating'] > 0): ?>
            <div style="display:flex;align-items:center;gap:.25rem;margin:.3rem 0 .5rem;">
              <?php for ($i = 1; $i <= 5; $i++): ?>
              <i data-lucide="star" style="width:12px;height:12px;color:<?= $i <= round($prod['rating']) ? '#F59E0B' : '#DDD' ?>;fill:<?= $i <= round($prod['rating']) ? '#F59E0B' : 'none' ?>;"></i>
              <?php endfor; ?>
              <span style="font-size:.72rem;color:#888;">(<?= $prod['review_count'] ?>)</span>
            </div>
            <?php endif; ?>
            <div class="product-card-footer">
              <span class="product-price"><?= formatPrice($prod['price']) ?></span>
              <?php if ($stockStatus !== 'out'): ?>
              <button class="btn btn-gold btn-sm add-to-cart-btn"
                      data-product-id="<?= $prod['id'] ?>">
                Add to Bag
              </button>
              <?php else: ?>
              <span style="font-size:.78rem;color:#EF4444;font-weight:600;">Sold Out</span>
              <?php endif; ?>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pages > 1): ?>
      <nav style="display:flex;justify-content:center;margin-top:2.5rem;" aria-label="Shop pagination">
        <?php
        $baseUrl = SITE_URL . '/shop.php?';
        $qp = $_GET; unset($qp['page']);
        $baseUrl .= http_build_query($qp) . '&page=';
        echo pagination($page, $pages, $baseUrl);
        ?>
      </nav>
      <?php endif; ?>
      <?php endif; ?>

      <!-- Bundles strip -->
      <?php if (!empty($bundles) && !$searchQ): ?>
      <div style="margin-top:4rem;">
        <div class="section-header" style="margin-bottom:1.5rem;">
          <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;color:var(--gyc-dark);">Bundle &amp; Save</h2>
          <p style="font-size:.88rem;color:#666;">Get more of what your hair loves, at a better price.</p>
        </div>
        <div class="bundles-scroll">
          <?php foreach ($bundles as $bundle):
            $bPrice = getBundlePrice($bundle['id']);
          ?>
          <div class="bundle-card">
            <a href="<?= SITE_URL ?>/bundle.php?slug=<?= urlencode($bundle['slug']) ?>" class="bundle-card-img-wrap" style="display:block;height:200px;overflow:hidden;background:var(--gyc-green-100);border-radius:var(--gyc-radius) var(--gyc-radius) 0 0;">
              <img src="<?= htmlspecialchars($bundle['image'] ?? '') ?>" alt="<?= htmlspecialchars($bundle['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
            </a>
            <div style="padding:1.1rem 1.1rem 1.25rem;">
              <?php if ($bPrice && $bPrice['discount_pct'] > 0): ?>
              <span style="font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gyc-gold-700);">Save <?= round($bPrice['discount_pct']) ?>%</span>
              <?php endif; ?>
              <h3 style="font-family:'Playfair Display',serif;font-size:1.05rem;margin:.25rem 0 .4rem;line-height:1.25;">
                <a href="<?= SITE_URL ?>/bundle.php?slug=<?= urlencode($bundle['slug']) ?>" style="color:var(--gyc-dark);text-decoration:none;"><?= htmlspecialchars($bundle['name']) ?></a>
              </h3>
              <p style="font-size:.8rem;color:#666;margin-bottom:.75rem;line-height:1.4;"><?= htmlspecialchars($bundle['short_description'] ?? '') ?></p>
              <?php if ($bPrice): ?>
              <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.75rem;">
                <span style="font-family:'Playfair Display',serif;font-size:1.15rem;color:var(--gyc-green-700);font-weight:700;"><?= formatPrice($bPrice['total']) ?></span>
                <span style="text-decoration:line-through;color:#bbb;font-size:.82rem;"><?= formatPrice($bPrice['subtotal']) ?></span>
              </div>
              <?php endif; ?>
              <a href="<?= SITE_URL ?>/bundle.php?slug=<?= urlencode($bundle['slug']) ?>" class="btn btn-gold btn-sm" style="width:100%;justify-content:center;">Shop Bundle</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      addToCart(btn.dataset.productId, 1, btn);
    });
  });
});

function toggleWishlist(productId, btn) {
  if (btn.classList.contains('login-req')) {
    window.location.href = '<?= SITE_URL ?>/login.php?redirect=' + encodeURIComponent(window.location.href);
    return;
  }
  fetch('<?= SITE_URL ?>/api/add-to-wishlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: 'product_id=' + productId
  })
  .then(r => r.json())
  .then(function(data) {
    if (data.saved) {
      btn.style.background = 'var(--gyc-terra)';
      btn.style.color = '#fff';
    } else {
      btn.style.background = '';
      btn.style.color = '';
    }
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
