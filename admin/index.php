<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// ── KPI stats ──
$todayStart     = date('Y-m-d 00:00:00');
$todayEnd       = date('Y-m-d 23:59:59');
$thisMonthStart = date('Y-m-01 00:00:00');

$totalOrders     = (int)($db->fetchOne("SELECT COUNT(*) c FROM orders")['c'] ?? 0);
$todayOrders     = (int)($db->fetchOne("SELECT COUNT(*) c FROM orders WHERE created_at BETWEEN ? AND ?", [$todayStart, $todayEnd])['c'] ?? 0);
$monthRevenue    = (float)($db->fetchOne("SELECT COALESCE(SUM(total),0) r FROM orders WHERE payment_status='paid' AND created_at >= ?", [$thisMonthStart])['r'] ?? 0);
$totalRevenue    = (float)($db->fetchOne("SELECT COALESCE(SUM(total),0) r FROM orders WHERE payment_status='paid'")['r'] ?? 0);
$totalCustomers  = (int)($db->fetchOne("SELECT COUNT(*) c FROM users WHERE role='customer'")['c'] ?? 0);
$newCustomers    = (int)($db->fetchOne("SELECT COUNT(*) c FROM users WHERE role='customer' AND created_at >= ?", [$thisMonthStart])['c'] ?? 0);
$pendingOrders   = (int)($db->fetchOne("SELECT COUNT(*) c FROM orders WHERE status='pending'")['c'] ?? 0);
$pendingApts     = (int)($db->fetchOne("SELECT COUNT(*) c FROM appointments WHERE status='pending'")['c'] ?? 0);
$lowStock        = (int)($db->fetchOne("SELECT COUNT(*) c FROM products WHERE stock_quantity <= 5 AND is_active=1")['c'] ?? 0);
$unreadMessages  = (int)($db->fetchOne("SELECT COUNT(*) c FROM contact_messages WHERE is_read=0")['c'] ?? 0);

// ── Recent orders ──
$recentOrders = $db->fetchAll(
    "SELECT o.*, CONCAT(u.first_name,' ',u.last_name) as customer_name
     FROM orders o LEFT JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC LIMIT 8"
);

// ── Recent appointments ──
$recentApts = $db->fetchAll(
    "SELECT a.*, gi.title as style_name
     FROM appointments a
     LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
     ORDER BY a.created_at DESC LIMIT 6"
);

// ── Revenue chart data (last 7 days) ──
$chartDays    = [];
$chartRevenue = [];
for ($d = 6; $d >= 0; $d--) {
    $dt    = date('Y-m-d', strtotime("-{$d} days"));
    $rev   = (float)($db->fetchOne(
        "SELECT COALESCE(SUM(total),0) r FROM orders WHERE payment_status='paid' AND DATE(created_at) = ?",
        [$dt]
    )['r'] ?? 0);
    $chartDays[]    = date('D', strtotime($dt));
    $chartRevenue[] = $rev;
}

$statusColors = ['pending'=>'#F59E0B','processing'=>'#3B82F6','shipped'=>'#8B5CF6','delivered'=>'#10B981','cancelled'=>'#EF4444','refunded'=>'#9CA3AF'];
$payColors    = ['pending'=>'#F59E0B','paid'=>'#10B981','failed'=>'#EF4444','refunded'=>'#9CA3AF'];
?>

<!-- ALERT CARDS -->
<?php if ($pendingOrders || $pendingApts || $lowStock || $unreadMessages): ?>
<div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1.75rem;">
  <?php if ($pendingOrders): ?>
  <a href="<?= SITE_URL ?>/admin/orders.php?status=pending" class="alert alert-warning" style="text-decoration:none;flex:0 0 auto;display:flex;align-items:center;gap:.5rem;padding:.6rem 1rem;font-size:.82rem;">
    <i data-lucide="shopping-cart" style="width:15px;height:15px;"></i>
    <strong><?= $pendingOrders ?></strong> pending order<?= $pendingOrders > 1 ? 's' : '' ?>
  </a>
  <?php endif; ?>
  <?php if ($pendingApts): ?>
  <a href="<?= SITE_URL ?>/admin/appointments.php?status=pending" class="alert alert-warning" style="text-decoration:none;flex:0 0 auto;display:flex;align-items:center;gap:.5rem;padding:.6rem 1rem;font-size:.82rem;">
    <i data-lucide="calendar" style="width:15px;height:15px;"></i>
    <strong><?= $pendingApts ?></strong> pending appointment<?= $pendingApts > 1 ? 's' : '' ?>
  </a>
  <?php endif; ?>
  <?php if ($lowStock): ?>
  <a href="<?= SITE_URL ?>/admin/products.php?stock=low" class="alert alert-danger" style="text-decoration:none;flex:0 0 auto;display:flex;align-items:center;gap:.5rem;padding:.6rem 1rem;font-size:.82rem;">
    <i data-lucide="alert-triangle" style="width:15px;height:15px;"></i>
    <strong><?= $lowStock ?></strong> low-stock product<?= $lowStock > 1 ? 's' : '' ?>
  </a>
  <?php endif; ?>
  <?php if ($unreadMessages): ?>
  <a href="<?= SITE_URL ?>/admin/messages.php" class="alert alert-info" style="text-decoration:none;flex:0 0 auto;display:flex;align-items:center;gap:.5rem;padding:.6rem 1rem;font-size:.82rem;">
    <i data-lucide="mail" style="width:15px;height:15px;"></i>
    <strong><?= $unreadMessages ?></strong> unread message<?= $unreadMessages > 1 ? 's' : '' ?>
  </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- KPI GRID -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2rem;">
  <?php $kpis = [
    ['₦'.number_format($monthRevenue),'Month Revenue','trending-up','var(--gyc-green-700)','var(--gyc-green-100)'],
    [$totalOrders,'Total Orders','shopping-bag','#3B82F6','#EFF6FF'],
    [$totalCustomers,'Customers','users','#7C3AED','#F5F3FF'],
    [$pendingOrders,'Pending Orders','clock','#F59E0B','#FFFBEB'],
  ];
  foreach ($kpis as $kpi): ?>
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.4rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
      <span style="font-size:.8rem;color:#9CA3AF;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $kpi[1] ?></span>
      <div style="width:36px;height:36px;border-radius:8px;background:<?= $kpi[4] ?>;display:flex;align-items:center;justify-content:center;">
        <i data-lucide="<?= $kpi[2] ?>" style="width:18px;height:18px;color:<?= $kpi[3] ?>;"></i>
      </div>
    </div>
    <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:#1C1F1A;"><?= $kpi[0] ?></div>
    <?php if ($kpi[1] === 'Month Revenue'): ?>
    <div style="font-size:.75rem;color:#9CA3AF;margin-top:.2rem;">All time: ₦<?= number_format($totalRevenue) ?></div>
    <?php elseif ($kpi[1] === 'Customers'): ?>
    <div style="font-size:.75rem;color:#9CA3AF;margin-top:.2rem;">+<?= $newCustomers ?> this month</div>
    <?php elseif ($kpi[1] === 'Total Orders'): ?>
    <div style="font-size:.75rem;color:#9CA3AF;margin-top:.2rem;"><?= $todayOrders ?> today</div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<!-- CHART + QUICK ACTIONS -->
<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;margin-bottom:2rem;">

  <!-- Revenue chart (last 7 days) -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;">
      <h2 style="font-size:.95rem;font-weight:700;color:#1C1F1A;">Revenue — Last 7 Days</h2>
      <a href="<?= SITE_URL ?>/admin/reports.php" style="font-size:.78rem;color:var(--gyc-green-600);">Full Report →</a>
    </div>
    <canvas id="revenueChart" style="display:block;width:100%;height:200px;"></canvas>
  </div>

  <!-- Quick actions -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
    <h2 style="font-size:.95rem;font-weight:700;margin-bottom:1.25rem;">Quick Actions</h2>
    <div style="display:flex;flex-direction:column;gap:.6rem;">
      <?php $actions = [
        [SITE_URL.'/admin/add-product.php',        'plus-circle',   'Add Product'],
        [SITE_URL.'/admin/add-gallery.php',         'image',         'Add Gallery Style'],
        [SITE_URL.'/admin/orders.php?status=pending','package',      'Process Orders'],
        [SITE_URL.'/admin/appointments.php',        'calendar',      'View Appointments'],
        [SITE_URL.'/admin/customers.php',           'users',         'View Customers'],
        [SITE_URL.'/admin/settings.php',            'settings',      'Settings'],
      ];
      foreach ($actions as $a): ?>
      <a href="<?= $a[0] ?>" style="display:flex;align-items:center;gap:.65rem;padding:.65rem .85rem;border:1px solid #E5E7EB;border-radius:8px;text-decoration:none;color:#1C1F1A;font-size:.83rem;font-weight:500;transition:background .15s;"
         onmouseover="this.style.background='#F8FAF9'" onmouseout="this.style.background=''">
        <i data-lucide="<?= $a[1] ?>" style="width:15px;height:15px;color:var(--gyc-green-600);flex-shrink:0;"></i>
        <?= htmlspecialchars($a[2]) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- RECENT ORDERS + APPOINTMENTS -->
<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:1.5rem;">

  <!-- Recent orders -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid #E5E7EB;">
      <h2 style="font-size:.95rem;font-weight:700;">Recent Orders</h2>
      <a href="<?= SITE_URL ?>/admin/orders.php" style="font-size:.78rem;color:var(--gyc-green-600);">View all →</a>
    </div>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#F8FAF9;border-bottom:1px solid #E5E7EB;">
          <th style="padding:.65rem 1.25rem;text-align:left;font-size:.75rem;font-weight:600;color:#9CA3AF;text-transform:uppercase;">Order</th>
          <th style="padding:.65rem 1.25rem;text-align:left;font-size:.75rem;font-weight:600;color:#9CA3AF;text-transform:uppercase;">Customer</th>
          <th style="padding:.65rem 1.25rem;text-align:left;font-size:.75rem;font-weight:600;color:#9CA3AF;text-transform:uppercase;">Total</th>
          <th style="padding:.65rem 1.25rem;text-align:left;font-size:.75rem;font-weight:600;color:#9CA3AF;text-transform:uppercase;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentOrders as $ord): ?>
        <tr style="border-bottom:1px solid #F0F0F0;" onmouseover="this.style.background='#FAFAFA'" onmouseout="this.style.background=''">
          <td style="padding:.7rem 1.25rem;">
            <a href="<?= SITE_URL ?>/admin/orders.php?view=<?= $ord['id'] ?>" style="font-size:.82rem;font-weight:600;color:var(--gyc-green-700);text-decoration:none;"><?= htmlspecialchars($ord['order_number']) ?></a>
            <div style="font-size:.72rem;color:#9CA3AF;"><?= date('j M', strtotime($ord['created_at'])) ?></div>
          </td>
          <td style="padding:.7rem 1.25rem;font-size:.82rem;color:#374151;"><?= htmlspecialchars($ord['customer_name'] ?? $ord['billing_name']) ?></td>
          <td style="padding:.7rem 1.25rem;font-size:.82rem;font-weight:600;color:#1C1F1A;"><?= formatPrice($ord['total']) ?></td>
          <td style="padding:.7rem 1.25rem;">
            <span style="font-size:.72rem;font-weight:700;padding:.2rem .6rem;border-radius:20px;background:<?= $payColors[$ord['payment_status']] ?? '#9CA3AF' ?>20;color:<?= $payColors[$ord['payment_status']] ?? '#9CA3AF' ?>;">
              <?= ucfirst($ord['payment_status']) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentOrders)): ?>
        <tr><td colspan="4" style="padding:2rem;text-align:center;color:#9CA3AF;font-size:.85rem;">No orders yet</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Recent appointments -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid #E5E7EB;">
      <h2 style="font-size:.95rem;font-weight:700;">Recent Appointments</h2>
      <a href="<?= SITE_URL ?>/admin/appointments.php" style="font-size:.78rem;color:var(--gyc-green-600);">View all →</a>
    </div>
    <div style="display:flex;flex-direction:column;">
      <?php foreach ($recentApts as $apt): ?>
      <div style="padding:1rem 1.5rem;border-bottom:1px solid #F0F0F0;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.25rem;">
          <span style="font-weight:600;font-size:.83rem;color:#1C1F1A;"><?= htmlspecialchars($apt['customer_name']) ?></span>
          <span style="font-size:.72rem;font-weight:700;color:<?= $statusColors[$apt['status']] ?? '#9CA3AF' ?>;">
            <?= ucfirst($apt['status']) ?>
          </span>
        </div>
        <div style="font-size:.78rem;color:#9CA3AF;">
          <?= htmlspecialchars($apt['style_name'] ?? 'Style TBD') ?> · <?= date('j M Y', strtotime($apt['requested_date'])) ?>
        </div>
        <?php
        $waPhone = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
        $waMsg   = "Hi {$apt['customer_name']}! Your appointment on " . date('D jS M', strtotime($apt['requested_date'])) . " is confirmed. See you at GYC Naturals! 🌿";
        $waUrl   = whatsappMessage($apt['customer_phone'] ?? $waPhone, $waMsg);
        ?>
        <div style="margin-top:.5rem;display:flex;gap:.5rem;">
          <a href="<?= SITE_URL ?>/admin/appointments.php?view=<?= $apt['id'] ?>" style="font-size:.72rem;color:var(--gyc-green-600);text-decoration:none;">View</a>
          <span style="color:#E5E7EB;">|</span>
          <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" style="font-size:.72rem;color:#25D366;text-decoration:none;">WhatsApp</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($recentApts)): ?>
      <div style="padding:2rem;text-align:center;color:#9CA3AF;font-size:.85rem;">No appointments yet</div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Minimal chart using inline SVG bars -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const canvas = document.getElementById('revenueChart');
  if (!canvas) return;
  const days    = <?= json_encode($chartDays) ?>;
  const revenue = <?= json_encode($chartRevenue) ?>;

  function draw() {
    const ctx     = canvas.getContext('2d');
    const dpr     = window.devicePixelRatio || 1;
    const cssW    = canvas.clientWidth || canvas.parentElement.clientWidth || 600;
    const cssH    = canvas.clientHeight || 200;
    canvas.width  = Math.round(cssW * dpr);
    canvas.height = Math.round(cssH * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, cssW, cssH);

    const maxRev = Math.max(...revenue, 1);
    const padL = 50, padR = 15, padT = 20, padB = 40;
    const chartW = cssW - padL - padR;
    const chartH = cssH - padT - padB;
    const n = days.length;
    const barW   = Math.max(8, Math.floor(chartW / n * .55));
    const barGap = chartW / n;

    ctx.strokeStyle = '#F0F0F0';
    ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
      const y = padT + chartH - (chartH * i / 4);
      ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(cssW - padR, y); ctx.stroke();
      ctx.fillStyle = '#9CA3AF';
      ctx.font = '10px Inter, sans-serif';
      ctx.textAlign = 'right';
      const val = Math.round(maxRev * i / 4);
      ctx.fillText(val >= 1000 ? (val/1000).toFixed(0) + 'k' : val, padL - 5, y + 4);
    }

    days.forEach(function(day, i) {
      const x    = padL + i * barGap + (barGap - barW) / 2;
      const barH = (revenue[i] / maxRev) * chartH;
      const y    = padT + chartH - barH;
      const grad = ctx.createLinearGradient(0, y, 0, y + barH);
      grad.addColorStop(0, '#166534');
      grad.addColorStop(1, '#40916C');
      ctx.fillStyle = grad;
      if (ctx.roundRect) { ctx.beginPath(); ctx.roundRect(x, y, barW, barH, [4, 4, 0, 0]); ctx.fill(); }
      else { ctx.fillRect(x, y, barW, barH); }
      ctx.fillStyle = '#9CA3AF';
      ctx.font = '11px Inter, sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(day, x + barW / 2, cssH - padB + 18);
    });
  }

  draw();
  let t; window.addEventListener('resize', function() { clearTimeout(t); t = setTimeout(draw, 100); });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
