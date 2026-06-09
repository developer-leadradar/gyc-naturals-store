<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$user   = getCurrentUser();
$orders = getOrdersByUser($user['id']);

$pageTitle = 'My Orders — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>
<div style="min-height:72px;"></div>
<section style="padding:2.5rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div class="dash-grid">
      <?php require __DIR__ . '/includes/dash-sidebar.php'; ?>
      <div>
        <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1.5rem;">My Orders</h1>
        <?php if (empty($orders)): ?>
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:3rem;text-align:center;">
          <i data-lucide="package-open" style="width:48px;height:48px;opacity:.3;margin-bottom:1rem;"></i>
          <h3 style="font-family:'Playfair Display',serif;margin-bottom:.5rem;">No orders yet</h3>
          <p style="color:#888;font-size:.9rem;margin-bottom:1.25rem;">When you place an order it will appear here.</p>
          <a href="<?= SITE_URL ?>/shop.php" class="btn btn-green">Start Shopping</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:1rem;">
          <?php foreach ($orders as $ord):
            $sc = ['pending'=>'#F59E0B','processing'=>'var(--gyc-green-600)','shipped'=>'#3B82F6','delivered'=>'#10B981','cancelled'=>'#EF4444','refunded'=>'#9CA3AF'];
            $pc = ['pending'=>'#F59E0B','paid'=>'var(--gyc-green-600)','failed'=>'#EF4444','refunded'=>'#9CA3AF'];
            $ois = getOrderItems($ord['id']);
          ?>
          <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid var(--gyc-green-100);gap:1rem;flex-wrap:wrap;background:var(--gyc-green-100);">
              <div>
                <a href="<?= SITE_URL ?>/order-details.php?order=<?= urlencode($ord['order_number']) ?>" style="font-weight:700;font-size:.92rem;color:var(--gyc-dark);"><?= htmlspecialchars($ord['order_number']) ?></a>
                <span style="font-size:.75rem;color:#888;margin-left:.75rem;"><?= date('jS M Y', strtotime($ord['created_at'])) ?></span>
              </div>
              <div style="display:flex;gap:.75rem;align-items:center;">
                <span style="font-size:.78rem;font-weight:700;color:<?= $sc[$ord['status']] ?? '#888' ?>;"><?= ucfirst($ord['status']) ?></span>
                <span style="font-size:.78rem;font-weight:700;color:<?= $pc[$ord['payment_status']] ?? '#888' ?>;"><?= ucfirst($ord['payment_status']) ?></span>
                <strong style="color:var(--gyc-green-700);"><?= formatPrice($ord['total']) ?></strong>
              </div>
            </div>
            <div style="padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
              <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <?php foreach (array_slice($ois, 0, 4) as $oi): ?>
                <div title="<?= htmlspecialchars($oi['product_name']) ?>" style="position:relative;">
                  <img src="<?= htmlspecialchars($oi['image'] ?? '') ?>" alt="<?= htmlspecialchars($oi['product_name']) ?>"
                       style="width:44px;height:44px;object-fit:cover;border-radius:var(--gyc-radius);border:1px solid var(--gyc-green-100);">
                  <span style="position:absolute;top:-5px;right:-5px;width:16px;height:16px;border-radius:50%;background:var(--gyc-dark);color:#fff;font-size:.62rem;font-weight:700;display:flex;align-items:center;justify-content:center;"><?= $oi['quantity'] ?></span>
                </div>
                <?php endforeach; ?>
                <?php if (count($ois) > 4): ?>
                <div style="width:44px;height:44px;border-radius:var(--gyc-radius);background:var(--gyc-green-100);display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:600;color:#888;">+<?= count($ois)-4 ?></div>
                <?php endif; ?>
              </div>
              <a href="<?= SITE_URL ?>/order-details.php?order=<?= urlencode($ord['order_number']) ?>" class="btn btn-outline-green btn-sm">View Details</a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
