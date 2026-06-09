<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$summary     = getCartSummary();
$cartItems   = $summary['items'];
$subtotal    = $summary['subtotal'];
$shipping    = $summary['shipping'];
$total       = $summary['total'];
$itemCount   = $summary['itemCount'];

$pageTitle = 'Your Bag — GYC Naturals Lagos';

require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<section style="padding:2.5rem 0 5rem;">
  <div class="container">
    <nav aria-label="Breadcrumb" style="font-size:.82rem;color:#888;margin-bottom:1.5rem;">
      <a href="<?= SITE_URL ?>">Home</a> / <a href="<?= SITE_URL ?>/shop.php">Shop</a> / <span style="color:var(--gyc-dark);">Your Bag</span>
    </nav>

    <h1 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:2rem;">
      Your Bag
      <span style="font-size:1rem;color:#888;font-family:Inter,sans-serif;font-weight:400;">(<?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?>)</span>
    </h1>

    <?php if (empty($cartItems)): ?>
    <!-- Empty cart -->
    <div style="text-align:center;padding:5rem 2rem;">
      <div style="font-size:4rem;margin-bottom:1rem;">🛍️</div>
      <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.75rem;">Your bag is empty</h2>
      <p style="color:#888;max-width:360px;margin:0 auto 1.5rem;line-height:1.65;">
        Looks like you haven't added anything yet. Browse our natural hair products.
      </p>
      <a href="<?= SITE_URL ?>/shop.php" class="btn btn-green btn-lg">
        <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
        Continue Shopping
      </a>
    </div>

    <?php else: ?>
    <!-- Cart layout -->
    <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:2.5rem;align-items:start;">

      <!-- Cart items -->
      <div>
        <table class="cart-table">
          <thead>
            <tr>
              <th>Product</th>
              <th style="text-align:center;">Qty</th>
              <th style="text-align:right;">Price</th>
              <th style="text-align:right;">Total</th>
              <th style="width:40px;"></th>
            </tr>
          </thead>
          <tbody id="cart-tbody">
            <?php foreach ($cartItems as $item): ?>
            <tr id="cart-row-<?= $item['id'] ?>">
              <td>
                <div style="display:flex;align-items:center;gap:1rem;">
                  <img src="<?= htmlspecialchars($item['image']) ?>"
                       alt="<?= htmlspecialchars($item['name']) ?>"
                       class="cart-product-img">
                  <div>
                    <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>"
                       style="font-weight:600;color:var(--gyc-dark);font-size:.9rem;">
                      <?= htmlspecialchars($item['name']) ?>
                    </a>
                    <?php if ($item['bundle_name']): ?>
                    <div style="font-size:.75rem;color:var(--gyc-green-500);">Part of: <?= htmlspecialchars($item['bundle_name']) ?></div>
                    <?php endif; ?>
                    <div style="font-size:.75rem;color:#888;"><?= formatPrice($item['price']) ?> each</div>
                  </div>
                </div>
              </td>
              <td style="text-align:center;">
                <div class="qty-control" style="display:inline-flex;">
                  <button type="button" onclick="updateQty(<?= $item['id'] ?>, -1)">−</button>
                  <input type="number" value="<?= $item['quantity'] ?>" min="1"
                         max="<?= $item['stock_quantity'] ?>"
                         id="qty-<?= $item['id'] ?>"
                         style="width:44px;"
                         onchange="setQty(<?= $item['id'] ?>, this.value)">
                  <button type="button" onclick="updateQty(<?= $item['id'] ?>, 1)">+</button>
                </div>
              </td>
              <td style="text-align:right;font-size:.9rem;"><?= formatPrice($item['price']) ?></td>
              <td style="text-align:right;font-weight:700;color:var(--gyc-green-700);"
                  id="row-total-<?= $item['id'] ?>">
                <?= formatPrice($item['price'] * $item['quantity']) ?>
              </td>
              <td style="text-align:center;">
                <button onclick="removeItem(<?= $item['id'] ?>)"
                        style="background:none;border:none;cursor:pointer;color:#bbb;padding:.25rem;"
                        title="Remove">
                  <i data-lucide="trash-2" style="width:16px;height:16px;"></i>
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--gyc-green-100);">
          <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline-green btn-sm">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
            Continue Shopping
          </a>
          <a href="<?= SITE_URL ?>/quiz.php" class="btn btn-outline-green btn-sm">
            <i data-lucide="sparkles" style="width:14px;height:14px;"></i>
            Not sure? Take Hair Quiz
          </a>
        </div>
      </div>

      <!-- Order summary sidebar -->
      <div>
        <div class="order-summary-card" id="order-summary">
          <h3 style="font-family:'Playfair Display',serif;font-size:1.15rem;margin-bottom:1.25rem;color:var(--gyc-dark);">Order Summary</h3>

          <div style="display:flex;flex-direction:column;gap:.6rem;margin-bottom:1.25rem;">
            <div style="display:flex;justify-content:space-between;font-size:.88rem;">
              <span style="color:#666;">Subtotal (<?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?>)</span>
              <span id="summary-subtotal"><?= formatPrice($subtotal) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.88rem;">
              <span style="color:#666;">Shipping</span>
              <span id="summary-shipping">
                <?= $shipping === 0 ? '<span style="color:var(--gyc-green-600);font-weight:600;">FREE</span>' : formatPrice($shipping) ?>
              </span>
            </div>
            <?php if ($shipping === 0): ?>
            <div style="font-size:.75rem;color:var(--gyc-green-600);margin-top:-4px;">
              ✓ Free delivery on orders over ₦50,000
            </div>
            <?php else: ?>
            <div style="font-size:.75rem;color:#888;margin-top:-4px;">
              Add <?= formatPrice(50000 - $subtotal) ?> more for free delivery
            </div>
            <?php endif; ?>
          </div>

          <div style="border-top:2px solid var(--gyc-green-200);padding-top:1rem;display:flex;justify-content:space-between;align-items:baseline;margin-bottom:1.5rem;">
            <span style="font-weight:700;font-size:1rem;color:var(--gyc-dark);">Total</span>
            <span style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:var(--gyc-green-700);" id="summary-total"><?= formatPrice($total) ?></span>
          </div>

          <a href="<?= SITE_URL ?>/checkout.php" class="btn btn-gold btn-lg" style="width:100%;justify-content:center;margin-bottom:.75rem;">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            Proceed to Checkout
          </a>

          <?php
          $waPhone = getSetting('site_whatsapp');
          if ($waPhone):
              $itemNames = implode(', ', array_column($cartItems, 'name'));
              $waMsg = 'Hi! I would like to order: ' . $itemNames . '. Can you help me place the order?';
              $waUrl = whatsappMessage($waPhone, $waMsg);
          ?>
          <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener"
             class="btn btn-whatsapp" style="width:100%;justify-content:center;margin-bottom:1.25rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            Order via WhatsApp
          </a>
          <?php endif; ?>

          <!-- Trust -->
          <div style="display:flex;flex-direction:column;gap:.4rem;font-size:.77rem;color:#666;">
            <div style="display:flex;align-items:center;gap:.4rem;"><i data-lucide="lock" style="width:13px;height:13px;color:var(--gyc-green-500);"></i>Secure checkout with Paystack</div>
            <div style="display:flex;align-items:center;gap:.4rem;"><i data-lucide="truck" style="width:13px;height:13px;color:var(--gyc-green-500);"></i>Lagos delivery 1–2 business days</div>
            <div style="display:flex;align-items:center;gap:.4rem;"><i data-lucide="refresh-ccw" style="width:13px;height:13px;color:var(--gyc-green-500);"></i>14-day returns policy</div>
          </div>
        </div>
      </div>
    </div><!-- /grid -->
    <?php endif; ?>
  </div>
</section>

<script>
function updateQty(itemId, delta) {
  const input = document.getElementById('qty-' + itemId);
  if (!input) return;
  const newQty = Math.max(1, Math.min(parseInt(input.max) || 99, parseInt(input.value) + delta));
  input.value = newQty;
  setQty(itemId, newQty);
}

function setQty(itemId, qty) {
  fetch('<?= SITE_URL ?>/api/update-cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: 'action=update&product_id=' + itemId + '&qty=' + Math.max(1, parseInt(qty))
  })
  .then(r => r.json())
  .then(function(data) {
    if (data.success) refreshCart(data);
  });
}

function removeItem(itemId) {
  const row = document.getElementById('cart-row-' + itemId);
  if (row) { row.style.opacity = '0.3'; row.style.pointerEvents = 'none'; }
  fetch('<?= SITE_URL ?>/api/update-cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: 'action=remove&product_id=' + itemId
  })
  .then(r => r.json())
  .then(function(data) {
    if (data.success) {
      if (row) row.remove();
      refreshCart(data);
      if (data.cart_count === 0) location.reload();
    }
  });
}

function refreshCart(data) {
  const subtotalEl = document.getElementById('summary-subtotal');
  const totalEl    = document.getElementById('summary-total');
  if (subtotalEl && data.subtotal) subtotalEl.textContent = data.subtotal;
  if (totalEl    && data.total)    totalEl.textContent    = data.total;
  // Update cart count badge in header
  document.querySelectorAll('.cart-count').forEach(function(el) { el.textContent = data.cart_count || 0; });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
