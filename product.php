<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug    = sanitize($_GET['slug'] ?? '');
$product = $slug ? getProductBySlug($slug) : null;

if (!$product || !$product['is_active']) {
    redirect(SITE_URL . '/shop.php');
}

$related  = getRelatedProducts($product['id'], $product['category_id'], 4);
$reviews  = getProductReviews($product['id']);
$stats    = getReviewStats($product['id']);
$bundles  = getProductBundles($product['id']);

// Additional product images (comma-separated in extra_images column if it exists)
$extraImages = [];
if (!empty($product['extra_images'])) {
    $extraImages = array_filter(array_map('trim', explode(',', $product['extra_images'])));
}
$allImages = array_merge([$product['image']], $extraImages);

$pageTitle       = htmlspecialchars($product['name']) . ' — GYC Naturals | Calabar Natural Hair Shop';
$pageDescription = $product['description']
    ? htmlspecialchars(substr($product['description'], 0, 155)) . '…'
    : 'Buy ' . $product['name'] . ' at GYC Naturals Calabar. Fast delivery across Nigeria.';
$ogImage = $product['image'];

// JSON-LD Product schema
$productJsonLd = json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => $product['name'],
    'description' => $product['description'] ?? '',
    'image'       => $product['image'] ?: '',
    'sku'         => $product['sku'] ?? '',
    'brand'       => ['@type' => 'Brand', 'name' => 'GYC Naturals'],
    'offers'      => [
        '@type'         => 'Offer',
        'priceCurrency' => 'NGN',
        'price'         => $product['price'],
        'availability'  => ($product['stock_quantity'] ?? 0) > 0
            ? 'https://schema.org/InStock'
            : 'https://schema.org/OutOfStock',
        'seller'        => ['@type' => 'Organization', 'name' => 'GYC Naturals'],
        'url'           => SITE_URL . '/product.php?slug=' . urlencode($product['slug']),
    ],
    'aggregateRating' => ($stats['total'] ?? 0) > 0 ? [
        '@type'       => 'AggregateRating',
        'ratingValue' => round($stats['average'] ?? 0, 1),
        'reviewCount' => (int)($stats['total'] ?? 0),
        'bestRating'  => 5,
        'worstRating' => 1,
    ] : null,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>
<script type="application/ld+json"><?= $productJsonLd ?></script>

<div style="min-height:72px;"></div>

<section style="padding:2.5rem 0 5rem;">
  <div class="container">

    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" style="font-size:.82rem;color:#888;margin-bottom:2rem;">
      <a href="<?= SITE_URL ?>">Home</a> /
      <a href="<?= SITE_URL ?>/shop.php">Shop</a> /
      <?php if ($product['category_name']): ?>
      <a href="<?= SITE_URL ?>/shop.php?category=<?= urlencode(strtolower(str_replace(' ', '-', $product['category_name']))) ?>"><?= htmlspecialchars($product['category_name']) ?></a> /
      <?php endif; ?>
      <span style="color:var(--gyc-dark);"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <div class="product-detail-grid">

      <!-- Gallery -->
      <div>
        <div class="product-gallery-main" id="gallery-main">
          <img src="<?= htmlspecialchars($allImages[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="eager" id="main-image">
        </div>
        <?php if (count($allImages) > 1): ?>
        <div class="product-gallery-thumbs">
          <?php foreach ($allImages as $i => $img): ?>
          <div class="product-gallery-thumb <?= $i === 0 ? 'active' : '' ?>" onclick="setMainImage(this, '<?= htmlspecialchars($img) ?>')">
            <img src="<?= htmlspecialchars($img) ?>" alt="View <?= $i + 1 ?>" loading="lazy">
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Product info -->
      <div>
        <!-- Badges -->
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.75rem;">
          <?php if ($product['is_featured']): ?>
          <span style="font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;background:var(--gyc-gold-100);color:var(--gyc-gold-700);padding:.25rem .65rem;border-radius:20px;">⭐ Best Seller</span>
          <?php endif; ?>
          <?php if ($product['category_name']): ?>
          <span style="font-size:.72rem;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--gyc-green-500);"><?= htmlspecialchars($product['category_name']) ?></span>
          <?php endif; ?>
        </div>

        <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,3vw,2.1rem);color:var(--gyc-dark);margin:0 0 .75rem;line-height:1.2;">
          <?= htmlspecialchars($product['name']) ?>
        </h1>

        <!-- Rating -->
        <?php if ($stats['total'] > 0): ?>
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem;">
          <div style="display:flex;gap:2px;">
            <?php for ($i = 1; $i <= 5; $i++): ?>
            <i data-lucide="star" style="width:16px;height:16px;color:<?= $i <= round($stats['average']) ? '#F59E0B' : '#DDD' ?>;fill:<?= $i <= round($stats['average']) ? '#F59E0B' : 'none' ?>;"></i>
            <?php endfor; ?>
          </div>
          <span style="font-size:.85rem;color:#888;"><?= number_format($stats['average'], 1) ?> (<?= $stats['total'] ?> review<?= $stats['total'] !== 1 ? 's' : '' ?>)</span>
          <a href="#reviews-section" style="font-size:.82rem;color:var(--gyc-green-600);text-decoration:underline;">Write a review</a>
        </div>
        <?php endif; ?>

        <!-- Price -->
        <div style="margin-bottom:1.5rem;">
          <span style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--gyc-green-700);font-weight:700;"><?= formatPrice($product['price']) ?></span>
          <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
          <span style="text-decoration:line-through;color:#bbb;font-size:1.1rem;margin-left:.5rem;"><?= formatPrice($product['compare_price']) ?></span>
          <span style="background:var(--gyc-terra);color:#fff;font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:20px;margin-left:.5rem;">
            -<?= round((1 - $product['price'] / $product['compare_price']) * 100) ?>%
          </span>
          <?php endif; ?>
          <?php if ($product['volume_ml']): ?>
          <span style="font-size:.8rem;color:#888;margin-left:.5rem;">/ <?= $product['volume_ml'] ?>ml</span>
          <?php endif; ?>
        </div>

        <!-- Key ingredients highlight -->
        <?php if ($product['key_ingredient']): ?>
        <div style="background:var(--gyc-green-100);border-radius:var(--gyc-radius);padding:.75rem 1rem;margin-bottom:1.25rem;font-size:.85rem;">
          <strong style="color:var(--gyc-green-700);">Key Ingredient:</strong>
          <span style="color:#333;"><?= htmlspecialchars($product['key_ingredient']) ?></span>
        </div>
        <?php endif; ?>

        <!-- Hair type / concern chips -->
        <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;">
          <?php if ($product['hair_type']): ?>
          <span style="font-size:.75rem;font-weight:600;background:#E5F0EA;color:var(--gyc-green-700);padding:.25rem .7rem;border-radius:20px;">
            <?= htmlspecialchars($product['hair_type']) ?> Hair
          </span>
          <?php endif; ?>
          <?php if ($product['concern']): ?>
          <span class="concern-chip"><?= ucwords(htmlspecialchars($product['concern'])) ?></span>
          <?php endif; ?>
          <?php if ($product['scent']): ?>
          <span style="font-size:.75rem;font-weight:600;background:#FEF3C7;color:#92400E;padding:.25rem .7rem;border-radius:20px;">
            <?= ucwords(htmlspecialchars($product['scent'])) ?> scent
          </span>
          <?php endif; ?>
        </div>

        <!-- Stock -->
        <?php if ($product['stock_quantity'] <= 0): ?>
        <div class="alert alert-danger" style="margin-bottom:1rem;">
          <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <span>Out of stock — check back soon.</span>
        </div>
        <?php elseif ($product['stock_quantity'] <= 5): ?>
        <div class="alert alert-warning" style="margin-bottom:1rem;">
          <i data-lucide="alert-triangle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <span>Only <?= $product['stock_quantity'] ?> left in stock!</span>
        </div>
        <?php endif; ?>

        <!-- Add to cart -->
        <?php if ($product['stock_quantity'] > 0): ?>
        <div style="display:flex;gap:.75rem;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;">
          <div class="qty-control">
            <button type="button" onclick="changeQty(-1)">−</button>
            <input type="number" id="qty-input" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
            <button type="button" onclick="changeQty(1)">+</button>
          </div>
          <button class="btn btn-gold btn-lg add-to-cart-btn"
                  style="flex:1;justify-content:center;"
                  id="atc-btn" data-product-id="<?= $product['id'] ?>">
            <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
            Add to Bag
          </button>
          <button class="btn btn-green btn-lg"
                  style="flex:1;justify-content:center;"
                  onclick="buyNow(<?= $product['id'] ?>)">
            <i data-lucide="zap" style="width:18px;height:18px;"></i>
            Buy Now
          </button>
        </div>
        <script>
        function buyNow(productId) {
          var qty = parseInt(document.getElementById('qty-input')?.value || 1);
          var btn = event.currentTarget;
          btn.disabled = true;
          fetch(window.GYC_URL + '/api/add-to-cart.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'product_id=' + productId + '&quantity=' + qty
          }).then(r => r.json()).then(function(data) {
            if (data.success) { window.location.href = window.GYC_URL + '/checkout.php'; }
            else { btn.disabled = false; if (typeof showToast !== 'undefined') showToast(data.message || 'Error', 'error'); }
          }).catch(function() { btn.disabled = false; });
        }
        </script>
        <?php endif; ?>

        <!-- Secondary actions -->
        <div style="display:flex;gap:.75rem;margin-bottom:1.75rem;">
          <button class="btn btn-outline-green" style="flex:1;justify-content:center;"
                  onclick="toggleWishlistFromProduct(<?= $product['id'] ?>, this)">
            <i data-lucide="heart" style="width:16px;height:16px;"></i>
            Save to Wishlist
          </button>
          <?php
          $waPhone = getSetting('site_whatsapp');
          if ($waPhone):
              $waMsg = 'Hi! I have a question about: ' . $product['name'] . ' (' . SITE_URL . '/product.php?slug=' . $product['slug'] . ')';
              $waUrl = whatsappMessage($waPhone, $waMsg);
          ?>
          <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp" style="flex:1;justify-content:center;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Ask a Question
          </a>
          <?php endif; ?>
        </div>

        <!-- Delivery / assurance strip -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
          <?php
          $assurances = [
            ['truck', 'Fast Delivery', 'Nationwide delivery in 1–3 days'],
            ['shield-check', '100% Natural', 'No harsh chemicals'],
            ['refresh-ccw', 'Easy Returns', '14-day return policy'],
            ['award', 'GYC Approved', 'Tested on African hair'],
          ];
          foreach ($assurances as $a):
          ?>
          <div style="display:flex;align-items:flex-start;gap:.5rem;font-size:.78rem;">
            <i data-lucide="<?= $a[0] ?>" style="width:16px;height:16px;color:var(--gyc-green-600);flex-shrink:0;margin-top:1px;"></i>
            <div>
              <strong style="display:block;color:var(--gyc-dark);"><?= $a[1] ?></strong>
              <span style="color:#888;"><?= $a[2] ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div><!-- /product-detail-grid -->

    <!-- Tabs: Description / Ingredients / How to Use / Reviews -->
    <div class="product-tabs" style="margin-top:4rem;">
      <div class="product-tab-nav">
        <button class="product-tab-btn active" data-tab="description">Description</button>
        <button class="product-tab-btn" data-tab="ingredients">Ingredients</button>
        <button class="product-tab-btn" data-tab="howto">How to Use</button>
        <button class="product-tab-btn" data-tab="reviews" id="reviews-tab-btn">
          Reviews (<?= $stats['total'] ?>)
        </button>
      </div>

      <!-- Description -->
      <div class="product-tab-panel active" id="tab-description">
        <div style="max-width:720px;">
          <?php if ($product['description']): ?>
          <div class="blog-post-body"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
          <?php else: ?>
          <p style="color:#888;">No description available.</p>
          <?php endif; ?>
          <?php if ($product['suitable_for']): ?>
          <div style="margin-top:1.25rem;padding:1.1rem 1.25rem;background:var(--gyc-green-100);border-radius:var(--gyc-radius);">
            <strong style="font-size:.85rem;color:var(--gyc-green-700);">Suitable for:</strong>
            <span style="font-size:.88rem;color:#333;"> <?= htmlspecialchars($product['suitable_for']) ?></span>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Ingredients -->
      <div class="product-tab-panel" id="tab-ingredients">
        <div style="max-width:720px;">
          <?php if ($product['ingredients']): ?>
          <p style="font-size:.88rem;color:#555;line-height:1.8;"><?= nl2br(htmlspecialchars($product['ingredients'])) ?></p>
          <?php else: ?>
          <p style="color:#888;">Ingredient list not available. Contact us for details.</p>
          <?php endif; ?>
          <p style="font-size:.78rem;color:#999;margin-top:1rem;">
            Always patch test before use. Discontinue if irritation occurs.
            For external use only. Keep out of reach of children.
          </p>
        </div>
      </div>

      <!-- How to Use -->
      <div class="product-tab-panel" id="tab-howto">
        <div style="max-width:720px;">
          <?php if ($product['how_to_use']): ?>
          <div class="blog-post-body"><?= nl2br(htmlspecialchars($product['how_to_use'])) ?></div>
          <?php else: ?>
          <ol style="padding-left:1.5rem;font-size:.9rem;line-height:1.8;color:#444;">
            <li>Shake well before use.</li>
            <li>Apply a small amount to clean, damp hair.</li>
            <li>Work through from roots to tips.</li>
            <li>Style as desired. No need to rinse unless specified.</li>
          </ol>
          <?php endif; ?>
        </div>
      </div>

      <!-- Reviews -->
      <div class="product-tab-panel" id="tab-reviews" id="reviews-section">
        <div style="max-width:780px;">

          <!-- Rating overview -->
          <?php if ($stats['total'] > 0): ?>
          <div style="display:grid;grid-template-columns:auto 1fr;gap:2rem;align-items:center;padding:1.5rem;background:var(--gyc-green-100);border-radius:var(--gyc-radius-lg);margin-bottom:2rem;">
            <div style="text-align:center;">
              <div style="font-family:'Playfair Display',serif;font-size:3.5rem;color:var(--gyc-dark);font-weight:700;line-height:1;"><?= number_format($stats['average'], 1) ?></div>
              <div style="display:flex;gap:2px;justify-content:center;margin:.25rem 0;">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <i data-lucide="star" style="width:16px;height:16px;color:<?= $i <= round($stats['average']) ? '#F59E0B' : '#DDD' ?>;fill:<?= $i <= round($stats['average']) ? '#F59E0B' : 'none' ?>;"></i>
                <?php endfor; ?>
              </div>
              <div style="font-size:.8rem;color:#888;"><?= $stats['total'] ?> review<?= $stats['total'] !== 1 ? 's' : '' ?></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:.35rem;">
              <?php for ($i = 5; $i >= 1; $i--):
                $pct = $stats['total'] > 0 ? ($stats['breakdown'][$i] / $stats['total'] * 100) : 0;
              ?>
              <div style="display:flex;align-items:center;gap:.75rem;font-size:.8rem;">
                <span style="width:10px;text-align:right;color:#888;"><?= $i ?></span>
                <i data-lucide="star" style="width:12px;height:12px;color:#F59E0B;fill:#F59E0B;flex-shrink:0;"></i>
                <div style="flex:1;height:8px;background:#fff;border-radius:99px;overflow:hidden;">
                  <div style="width:<?= round($pct) ?>%;height:100%;background:#F59E0B;border-radius:99px;"></div>
                </div>
                <span style="width:24px;color:#888;"><?= $stats['breakdown'][$i] ?></span>
              </div>
              <?php endfor; ?>
            </div>
          </div>

          <!-- Review list -->
          <div style="display:flex;flex-direction:column;gap:1.5rem;margin-bottom:2rem;">
            <?php foreach ($reviews as $rev): ?>
            <div style="padding:1.25rem;border:1px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);">
              <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;flex-wrap:wrap;gap:.5rem;">
                <strong style="font-size:.9rem;color:var(--gyc-dark);"><?= htmlspecialchars($rev['first_name'] . ' ' . substr($rev['last_name'], 0, 1) . '.') ?></strong>
                <div style="display:flex;gap:2px;">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i data-lucide="star" style="width:13px;height:13px;color:<?= $i <= $rev['rating'] ? '#F59E0B' : '#DDD' ?>;fill:<?= $i <= $rev['rating'] ? '#F59E0B' : 'none' ?>;"></i>
                  <?php endfor; ?>
                </div>
              </div>
              <?php if ($rev['title']): ?>
              <strong style="font-size:.88rem;display:block;margin-bottom:.35rem;"><?= htmlspecialchars($rev['title']) ?></strong>
              <?php endif; ?>
              <p style="font-size:.88rem;color:#555;line-height:1.65;margin:0 0 .4rem;"><?= nl2br(htmlspecialchars($rev['body'])) ?></p>
              <span style="font-size:.75rem;color:#bbb;"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <p style="color:#888;margin-bottom:2rem;">No reviews yet. Be the first to review this product!</p>
          <?php endif; ?>

          <!-- Write review form -->
          <div id="write-review">
            <h3 style="font-family:'Playfair Display',serif;font-size:1.15rem;margin-bottom:1.25rem;">Write a Review</h3>
            <?php if (!isLoggedIn()): ?>
            <div class="alert alert-info">
              <i data-lucide="info" style="width:16px;height:16px;flex-shrink:0;"></i>
              <div><a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode('/product.php?slug='.$product['slug'].'#reviews-section') ?>">Sign in</a> to leave a review.</div>
            </div>
            <?php else: ?>
            <form id="review-form">
              <?= csrfInput() ?>
              <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
              <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Rating <span class="required">*</span></label>
                <div class="star-rating" id="star-rating-input" style="display:flex;gap:.35rem;cursor:pointer;">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i data-lucide="star" data-val="<?= $i ?>" style="width:28px;height:28px;color:#DDD;cursor:pointer;" onclick="setStarRating(<?= $i ?>)"></i>
                  <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="rating-input" value="">
              </div>
              <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Review Title</label>
                <input type="text" name="title" class="form-control" placeholder="Summarize your experience">
              </div>
              <div class="form-group" style="margin-bottom:1rem;">
                <label class="form-label">Your Review <span class="required">*</span></label>
                <textarea name="body" class="form-control" rows="4" placeholder="Tell others what you think about this product…" required></textarea>
              </div>
              <button type="submit" class="btn btn-green" id="review-submit-btn">
                <i data-lucide="send" style="width:16px;height:16px;"></i>
                Submit Review
              </button>
              <div id="review-msg" style="margin-top:.75rem;"></div>
            </form>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div><!-- /product-tabs -->

    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <div style="margin-top:5rem;">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1.75rem;color:var(--gyc-dark);">You Might Also Like</h2>
      <div class="products-grid">
        <?php foreach ($related as $rel): ?>
        <article class="product-card">
          <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($rel['slug']) ?>" class="product-card-img-wrap">
            <img src="<?= htmlspecialchars($rel['image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" loading="lazy" class="product-card-img">
          </a>
          <div class="product-card-body">
            <h3 class="product-card-name"><a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($rel['slug']) ?>"><?= htmlspecialchars($rel['name']) ?></a></h3>
            <div class="product-card-footer">
              <span class="product-price"><?= formatPrice($rel['price']) ?></span>
              <button class="btn btn-gold btn-sm add-to-cart-btn" data-product-id="<?= $rel['id'] ?>">Add</button>
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
// Gallery thumb swap
function setMainImage(thumb, src) {
  document.getElementById('main-image').src = src;
  document.querySelectorAll('.product-gallery-thumb').forEach(function(t) { t.classList.remove('active'); });
  thumb.classList.add('active');
}

// Quantity control
function changeQty(delta) {
  const input = document.getElementById('qty-input');
  if (!input) return;
  const newVal = Math.max(1, Math.min(parseInt(input.max) || 99, parseInt(input.value) + delta));
  input.value = newVal;
}

// Tab switcher
document.querySelectorAll('.product-tab-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    const tabId = btn.dataset.tab;
    document.querySelectorAll('.product-tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.product-tab-panel').forEach(function(p) { p.classList.remove('active'); });
    btn.classList.add('active');
    const panel = document.getElementById('tab-' + tabId);
    if (panel) panel.classList.add('active');
  });
});

// Link from rating area to reviews tab
document.querySelectorAll('a[href="#reviews-section"]').forEach(function(a) {
  a.addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelector('[data-tab="reviews"]')?.click();
    document.getElementById('write-review')?.scrollIntoView({ behavior: 'smooth' });
  });
});

// Star rating input
function setStarRating(val) {
  document.getElementById('rating-input').value = val;
  document.querySelectorAll('#star-rating-input [data-lucide="star"]').forEach(function(star, i) {
    const filled = i < val;
    star.style.color = filled ? '#F59E0B' : '#DDD';
    star.setAttribute('fill', filled ? '#F59E0B' : 'none');
  });
  if (typeof lucide !== 'undefined') lucide.createIcons();
}

// Add to cart
document.addEventListener('DOMContentLoaded', function() {
  const atcBtn = document.getElementById('atc-btn');
  if (atcBtn) {
    atcBtn.addEventListener('click', function() {
      const qty = parseInt(document.getElementById('qty-input')?.value) || 1;
      addToCart(atcBtn.dataset.productId, qty, atcBtn);
    });
  }
  document.querySelectorAll('.add-to-cart-btn:not(#atc-btn)').forEach(function(btn) {
    btn.addEventListener('click', function() { addToCart(btn.dataset.productId, 1, btn); });
  });
});

// Review form submission
const reviewForm = document.getElementById('review-form');
if (reviewForm) {
  reviewForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(reviewForm);
    const msgEl    = document.getElementById('review-msg');
    const submitBtn = document.getElementById('review-submit-btn');
    if (!formData.get('rating')) { msgEl.innerHTML = '<span style="color:var(--gyc-terra);">Please select a star rating.</span>'; return; }
    submitBtn.disabled = true;
    fetch('<?= SITE_URL ?>/api/submit-review.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    })
    .then(r => r.json())
    .then(function(data) {
      submitBtn.disabled = false;
      if (data.success) {
        msgEl.innerHTML = '<div class="alert alert-success"><i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i> Review submitted! It will appear after approval.</div>';
        reviewForm.reset();
        setStarRating(0);
        if (typeof lucide !== 'undefined') lucide.createIcons();
      } else {
        msgEl.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Something went wrong.') + '</div>';
      }
    })
    .catch(function() {
      submitBtn.disabled = false;
      msgEl.innerHTML = '<div class="alert alert-danger">Network error. Please try again.</div>';
    });
  });
}

function toggleWishlistFromProduct(productId, btn) {
  if (!<?= isLoggedIn() ? 'true' : 'false' ?>) {
    window.location.href = '<?= SITE_URL ?>/login.php?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
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
      btn.innerHTML = '<i data-lucide="heart" style="width:16px;height:16px;fill:var(--gyc-terra);"></i> Saved to Wishlist';
      btn.style.borderColor = 'var(--gyc-terra)';
      btn.style.color = 'var(--gyc-terra)';
    } else {
      btn.innerHTML = '<i data-lucide="heart" style="width:16px;height:16px;"></i> Save to Wishlist';
      btn.style.borderColor = '';
      btn.style.color = '';
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
