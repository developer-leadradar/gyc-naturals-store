<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$orderNum = sanitize($_GET['order'] ?? '');

if (!$orderNum) {
    redirect(isLoggedIn() ? SITE_URL . '/customer-dashboard.php' : SITE_URL . '/login.php');
}

$order = getOrderByNumber($orderNum);

// Security: only show if logged in as this user, or if it's a guest order (no user_id)
if (!$order) {
    redirect(isLoggedIn() ? SITE_URL . '/customer-dashboard.php' : SITE_URL . '/login.php');
}
if ($order['user_id'] && (!isLoggedIn() || $_SESSION['user_id'] != $order['user_id'])) {
    if (!isAdmin()) {
        redirect(SITE_URL . '/login.php?redirect=' . urlencode('/order-details.php?order=' . $orderNum));
    }
}

$orderItems  = getOrderItems($order['id']);
$isNewOrder  = !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'paystack-verify') !== false;

// Admin WhatsApp notification URL
$waPhone    = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
$itemList   = implode(', ', array_column($orderItems, 'product_name'));
$waAdminMsg = "New Order: {$order['order_number']}\n"
            . "Customer: {$order['shipping_first_name']} {$order['shipping_last_name']}\n"
            . "Phone: {$order['shipping_phone']}\n"
            . "Total: ₦" . number_format($order['total']) . "\n"
            . "Items: $itemList\n"
            . "Deliver to: {$order['shipping_address']}, {$order['shipping_city']}, {$order['shipping_state']}";
$waAdminUrl = whatsappMessage($waPhone, $waAdminMsg);

$pageTitle = 'Order ' . $orderNum . ' — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>


<section style="padding:2.5rem 0 5rem;">
  <div class="container" style="max-width:820px;">

    <!-- Success banner (shown when just coming from checkout) -->
    <?php if ($order['payment_status'] === 'paid'): ?>
    <div style="background:linear-gradient(135deg,var(--gyc-green-700),var(--gyc-green-900));border-radius:var(--gyc-radius-lg);padding:2.5rem;text-align:center;color:#fff;margin-bottom:2.5rem;">
      <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,.15);border:3px solid rgba(255,255,255,.5);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
        <i data-lucide="check" style="width:32px;height:32px;color:#fff;stroke-width:3;"></i>
      </div>
      <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,3vw,2.2rem);color:#fff;margin:0 0 .75rem;">Order Confirmed!</h1>
      <p style="color:rgba(255,255,255,.8);max-width:440px;margin:0 auto .75rem;font-size:.95rem;">
        Payment received for <strong><?= htmlspecialchars($orderNum) ?></strong>. We are preparing your order.
      </p>
      <span style="font-size:.78rem;color:rgba(255,255,255,.6);">A confirmation email has been sent to <strong><?= htmlspecialchars($order['customer_email'] ?? '') ?></strong></span>
    </div>
    <?php else: ?>
    <h1 style="font-family:'Playfair Display',serif;font-size:1.75rem;margin-bottom:2rem;">Order Details</h1>
    <?php endif; ?>

    <!-- Order number + status -->
    <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem 2rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
      <div>
        <span style="font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gyc-green-500);">Order Number</span>
        <div style="font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:600;color:var(--gyc-dark);"><?= htmlspecialchars($orderNum) ?></div>
        <div style="font-size:.8rem;color:#888;margin-top:2px;">Placed <?= date('D jS M Y \a\t g:i A', strtotime($order['created_at'])) ?></div>
      </div>
      <div style="text-align:right;">
        <?php
        $statusMap = [
            'pending'    => ['Pending',    '#F59E0B'],
            'processing' => ['Processing', 'var(--gyc-green-600)'],
            'shipped'    => ['Shipped',    '#3B82F6'],
            'delivered'  => ['Delivered',  '#10B981'],
            'cancelled'  => ['Cancelled',  '#EF4444'],
            'refunded'   => ['Refunded',   '#9CA3AF'],
        ];
        $payStatusMap = [
            'pending'  => ['Payment Pending', '#F59E0B'],
            'paid'     => ['Paid', 'var(--gyc-green-600)'],
            'failed'   => ['Failed', '#EF4444'],
            'refunded' => ['Refunded', '#9CA3AF'],
        ];
        $os  = $statusMap[$order['status']]  ?? ['Unknown', '#888'];
        $ps  = $payStatusMap[$order['payment_status']] ?? ['Unknown', '#888'];
        ?>
        <div style="margin-bottom:.4rem;">
          <span style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#888;">ORDER STATUS</span>
          <span style="display:block;font-weight:700;font-size:.9rem;color:<?= $os[1] ?>;"><?= $os[0] ?></span>
        </div>
        <div>
          <span style="font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#888;">PAYMENT</span>
          <span style="display:block;font-weight:700;font-size:.9rem;color:<?= $ps[1] ?>;"><?= $ps[0] ?></span>
        </div>
      </div>
    </div>

    <!-- Order items -->
    <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;margin-bottom:1.5rem;">
      <div style="padding:1.25rem 1.75rem;border-bottom:1px solid var(--gyc-green-100);">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.05rem;margin:0;">Items Ordered (<?= count($orderItems) ?>)</h2>
      </div>
      <?php foreach ($orderItems as $oi): ?>
      <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.75rem;border-bottom:1px solid var(--gyc-green-100);">
        <img src="<?= htmlspecialchars($oi['image'] ?? '') ?>"
             alt="<?= htmlspecialchars($oi['product_name']) ?>"
             style="width:52px;height:52px;object-fit:cover;border-radius:var(--gyc-radius);background:var(--gyc-green-100);flex-shrink:0;">
        <div style="flex:1;min-width:0;">
          <div style="font-weight:600;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($oi['product_name']) ?></div>
          <div style="font-size:.78rem;color:#888;">Qty: <?= $oi['quantity'] ?></div>
        </div>
        <div style="text-align:right;flex-shrink:0;">
          <div style="font-weight:700;color:var(--gyc-green-700);"><?= formatPrice($oi['subtotal']) ?></div>
          <div style="font-size:.75rem;color:#aaa;"><?= formatPrice($oi['price_at_purchase']) ?> each</div>
        </div>
      </div>
      <?php endforeach; ?>
      <!-- Totals -->
      <div style="padding:1.25rem 1.75rem;">
        <div style="display:flex;flex-direction:column;gap:.4rem;font-size:.88rem;max-width:280px;margin-left:auto;">
          <div style="display:flex;justify-content:space-between;"><span style="color:#888;">Subtotal</span><span><?= formatPrice($order['subtotal']) ?></span></div>
          <div style="display:flex;justify-content:space-between;"><span style="color:#888;">Shipping</span><span style="color:var(--gyc-green-600);"><?= $order['shipping'] == 0 ? 'FREE' : formatPrice($order['shipping']) ?></span></div>
          <?php if ($order['discount'] > 0): ?>
          <div style="display:flex;justify-content:space-between;color:var(--gyc-terra);"><span>Discount</span><span>–<?= formatPrice($order['discount']) ?></span></div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;border-top:2px solid var(--gyc-green-100);padding-top:.6rem;margin-top:.25rem;">
            <strong style="font-size:1rem;">Total</strong>
            <strong style="font-family:'Playfair Display',serif;font-size:1.2rem;color:var(--gyc-green-700);"><?= formatPrice($order['total']) ?></strong>
          </div>
        </div>
      </div>
    </div>

    <!-- Shipping info -->
    <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem 1.75rem;margin-bottom:1.5rem;">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.05rem;margin-bottom:1rem;">Delivery Address</h2>
      <p style="font-size:.9rem;color:#444;line-height:1.75;margin:0;">
        <strong><?= htmlspecialchars($order['shipping_first_name'] . ' ' . $order['shipping_last_name']) ?></strong><br>
        <?= htmlspecialchars($order['shipping_address']) ?><br>
        <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_state']) ?>, <?= htmlspecialchars($order['shipping_country']) ?><br>
        <?php if ($order['shipping_phone']): ?>
        <?= htmlspecialchars($order['shipping_phone']) ?>
        <?php endif; ?>
      </p>
    </div>

    <!-- Actions -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem;">
      <a href="<?= htmlspecialchars($waAdminUrl) ?>" target="_blank" rel="noopener"
         class="btn btn-whatsapp btn-lg" style="justify-content:center;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Notify GYC Naturals
      </a>
      <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline-green btn-lg" style="justify-content:center;">
        <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
        Continue Shopping
      </a>
    </div>

    <!-- Need help? -->
    <div style="text-align:center;padding:2rem;background:var(--gyc-green-100);border-radius:var(--gyc-radius-lg);">
      <h3 style="font-size:1rem;font-weight:600;margin-bottom:.4rem;">Questions about your order?</h3>
      <p style="font-size:.85rem;color:#555;margin-bottom:1rem;">Contact us on WhatsApp or email and quote your order number <strong><?= htmlspecialchars($orderNum) ?></strong>.</p>
      <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap;">
        <?php
        $contactWaUrl = whatsappMessage($waPhone, 'Hi! I have a question about my order: ' . $orderNum);
        ?>
        <a href="<?= htmlspecialchars($contactWaUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          WhatsApp Us
        </a>
        <a href="mailto:<?= htmlspecialchars(getSetting('site_email') ?: SITE_EMAIL) ?>" class="btn btn-outline-green btn-sm">
          <i data-lucide="mail" style="width:15px;height:15px;"></i>
          Email Us
        </a>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
