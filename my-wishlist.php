<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$user = getCurrentUser();
$wishlistItems = getDB()->fetchAll(
    "SELECT w.id as wish_id, p.* FROM wishlist w
     JOIN products p ON w.product_id = p.id
     WHERE w.user_id = ? AND p.is_active = 1
     ORDER BY w.created_at DESC",
    [$user['id']]
);

$pageTitle = 'My Wishlist — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>
<div style="min-height:72px;"></div>
<section style="padding:2.5rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div style="max-width:960px;margin:0 auto;">
      <div>
        <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1.5rem;">
          My Wishlist (<?= count($wishlistItems) ?>)
        </h1>

        <?php if (empty($wishlistItems)): ?>
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:3rem;text-align:center;">
          <i data-lucide="heart" style="width:48px;height:48px;opacity:.3;margin-bottom:1rem;"></i>
          <h3 style="font-family:'Playfair Display',serif;margin-bottom:.5rem;">Your wishlist is empty</h3>
          <p style="color:#888;font-size:.9rem;margin-bottom:1.25rem;">Save products you love by clicking the heart icon.</p>
          <a href="<?= SITE_URL ?>/shop.php" class="btn btn-green">Browse Products</a>
        </div>
        <?php else: ?>
        <div class="products-grid">
          <?php foreach ($wishlistItems as $prod): ?>
          <article class="product-card">
            <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>" class="product-card-img-wrap">
              <img src="<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" class="product-card-img">
              <button class="product-wishlist saved"
                      data-product-id="<?= $prod['id'] ?>"
                      onclick="removeWishlist(<?= $prod['id'] ?>, this); event.preventDefault();"
                      title="Remove from wishlist">
                <i data-lucide="heart" style="width:16px;height:16px;fill:var(--gyc-terra);color:var(--gyc-terra);"></i>
              </button>
            </a>
            <div class="product-card-body">
              <?php if ($prod['category_name']): ?>
              <span class="product-tag"><?= htmlspecialchars($prod['category_name']) ?></span>
              <?php endif; ?>
              <h3 class="product-card-name">
                <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>"><?= htmlspecialchars($prod['name']) ?></a>
              </h3>
              <div class="product-card-footer">
                <span class="product-price"><?= formatPrice($prod['price']) ?></span>
                <?php if ($prod['stock_quantity'] > 0): ?>
                <button class="btn btn-gold btn-sm add-to-cart-btn" data-product-id="<?= $prod['id'] ?>">Add to Bag</button>
                <?php else: ?>
                <span style="font-size:.78rem;color:#EF4444;font-weight:600;">Sold Out</span>
                <?php endif; ?>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
    btn.addEventListener('click', function() { addToCart(btn.dataset.productId, 1, btn); });
  });
});

function removeWishlist(productId, btn) {
  const card = btn.closest('article');
  if (card) { card.style.opacity = '0.4'; card.style.pointerEvents = 'none'; }
  fetch('<?= SITE_URL ?>/api/add-to-wishlist.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: 'product_id=' + productId
  })
  .then(r => r.json())
  .then(function() {
    if (card) { card.style.transition = 'opacity .3s'; card.style.opacity = '0'; setTimeout(function() { card.remove(); }, 300); }
  });
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
