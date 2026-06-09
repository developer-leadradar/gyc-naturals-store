<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Reports';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Date range
$range      = sanitize($_GET['range'] ?? '30');
$dateFrom   = date('Y-m-d', strtotime("-{$range} days"));
$dateTo     = date('Y-m-d');
$prevFrom   = date('Y-m-d', strtotime("-" . ($range * 2) . " days"));
$prevTo     = date('Y-m-d', strtotime("-{$range} days"));

// Revenue
$revenue     = (float)($db->fetchOne("SELECT COALESCE(SUM(total),0) r FROM orders WHERE payment_status='paid' AND DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo])['r'] ?? 0);
$prevRevenue = (float)($db->fetchOne("SELECT COALESCE(SUM(total),0) r FROM orders WHERE payment_status='paid' AND DATE(created_at) BETWEEN ? AND ?", [$prevFrom, $prevTo])['r'] ?? 0);
$revChange   = $prevRevenue > 0 ? round((($revenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;

// Orders
$orderCount     = (int)($db->fetchOne("SELECT COUNT(*) c FROM orders WHERE DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo])['c'] ?? 0);
$prevOrderCount = (int)($db->fetchOne("SELECT COUNT(*) c FROM orders WHERE DATE(created_at) BETWEEN ? AND ?", [$prevFrom, $prevTo])['c'] ?? 0);
$ordChange      = $prevOrderCount > 0 ? round((($orderCount - $prevOrderCount) / $prevOrderCount) * 100, 1) : 0;

// New customers
$newCust     = (int)($db->fetchOne("SELECT COUNT(*) c FROM users WHERE role='customer' AND DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo])['c'] ?? 0);
$prevNewCust = (int)($db->fetchOne("SELECT COUNT(*) c FROM users WHERE role='customer' AND DATE(created_at) BETWEEN ? AND ?", [$prevFrom, $prevTo])['c'] ?? 0);
$custChange  = $prevNewCust > 0 ? round((($newCust - $prevNewCust) / $prevNewCust) * 100, 1) : 0;

// Appointments
$aptCount     = (int)($db->fetchOne("SELECT COUNT(*) c FROM appointments WHERE DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo])['c'] ?? 0);
$prevAptCount = (int)($db->fetchOne("SELECT COUNT(*) c FROM appointments WHERE DATE(created_at) BETWEEN ? AND ?", [$prevFrom, $prevTo])['c'] ?? 0);
$aptChange    = $prevAptCount > 0 ? round((($aptCount - $prevAptCount) / $prevAptCount) * 100, 1) : 0;

// Daily revenue for chart (last range days)
$dailyData = [];
for ($d = (int)$range - 1; $d >= 0; $d--) {
    $dt  = date('Y-m-d', strtotime("-{$d} days"));
    $rev = (float)($db->fetchOne("SELECT COALESCE(SUM(total),0) r FROM orders WHERE payment_status='paid' AND DATE(created_at)=?", [$dt])['r'] ?? 0);
    $dailyData[] = ['date' => $dt, 'label' => date('j M', strtotime($dt)), 'revenue' => $rev];
}

// Top products
$topProducts = $db->fetchAll(
    "SELECT p.name, p.image, SUM(oi.quantity) as units, SUM(oi.price * oi.quantity) as revenue
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     JOIN orders o ON oi.order_id = o.id
     WHERE o.payment_status = 'paid' AND DATE(o.created_at) BETWEEN ? AND ?
     GROUP BY p.id ORDER BY revenue DESC LIMIT 8",
    [$dateFrom, $dateTo]
);

// Orders by status
$byStatus = $db->fetchAll("SELECT status, COUNT(*) as cnt FROM orders WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY status", [$dateFrom, $dateTo]);
$statusMap = array_column($byStatus, 'cnt', 'status');

// Revenue by category
$catRevenue = $db->fetchAll(
    "SELECT COALESCE(c.name,'Uncategorised') as cat, SUM(oi.price * oi.quantity) as rev
     FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     LEFT JOIN categories c ON p.category_id = c.id
     JOIN orders o ON oi.order_id = o.id
     WHERE o.payment_status='paid' AND DATE(o.created_at) BETWEEN ? AND ?
     GROUP BY c.id ORDER BY rev DESC LIMIT 6",
    [$dateFrom, $dateTo]
);
?>

<!-- Date range selector -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div style="font-size:.85rem;color:#9CA3AF;">
    <?= date('j M Y', strtotime($dateFrom)) ?> — <?= date('j M Y') ?>
  </div>
  <form method="GET" style="display:flex;gap:.5rem;">
    <?php foreach ([7=>'7 days',14=>'14 days',30=>'30 days',60=>'60 days',90=>'90 days'] as $v => $l): ?>
    <a href="?range=<?= $v ?>" class="btn btn-sm <?= $range == $v ? 'btn-green' : 'btn-outline-green' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </form>
</div>

<!-- KPI row -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2rem;">
  <?php
  $kpis = [
    ['Revenue','₦'.number_format($revenue),$revChange,'trending-up','var(--gyc-green-700)','var(--gyc-green-100)'],
    ['Orders',$orderCount,$ordChange,'shopping-bag','#3B82F6','#EFF6FF'],
    ['New Customers',$newCust,$custChange,'users','#7C3AED','#F5F3FF'],
    ['Appointments',$aptCount,$aptChange,'calendar','#F59E0B','#FFFBEB'],
  ];
  foreach ($kpis as $k):
    $up = $k[2] >= 0;
  ?>
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.4rem;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;">
      <span style="font-size:.78rem;color:#9CA3AF;font-weight:600;text-transform:uppercase;letter-spacing:.05em;"><?= $k[0] ?></span>
      <div style="width:34px;height:34px;border-radius:8px;background:<?= $k[5] ?>;display:flex;align-items:center;justify-content:center;">
        <i data-lucide="<?= $k[3] ?>" style="width:16px;height:16px;color:<?= $k[4] ?>;"></i>
      </div>
    </div>
    <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;color:#1C1F1A;margin-bottom:.35rem;"><?= $k[1] ?></div>
    <?php if ($k[2] != 0): ?>
    <div style="font-size:.75rem;color:<?= $up ? '#10B981' : '#EF4444' ?>;font-weight:600;">
      <?= $up ? '▲' : '▼' ?> <?= abs($k[2]) ?>% vs previous period
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<!-- Revenue chart + status breakdown -->
<div style="display:grid;grid-template-columns:1fr 240px;gap:1.5rem;margin-bottom:2rem;">

  <!-- Line / bar chart -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
    <h2 style="font-size:.9rem;font-weight:700;margin-bottom:1.25rem;color:#1C1F1A;">Daily Revenue (₦)</h2>
    <canvas id="dailyChart" height="200"></canvas>
  </div>

  <!-- Order status donut (CSS) -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
    <h2 style="font-size:.9rem;font-weight:700;margin-bottom:1.25rem;">Orders by Status</h2>
    <?php
    $statusColors2 = ['pending'=>'#F59E0B','processing'=>'#3B82F6','shipped'=>'#8B5CF6','delivered'=>'#10B981','cancelled'=>'#EF4444','refunded'=>'#9CA3AF'];
    $totalOrds = array_sum($statusMap) ?: 1;
    foreach ($statusMap as $s => $cnt): ?>
    <div style="margin-bottom:.75rem;">
      <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
        <span style="font-size:.8rem;font-weight:600;color:#374151;"><?= ucfirst($s) ?></span>
        <span style="font-size:.8rem;color:#9CA3AF;"><?= $cnt ?></span>
      </div>
      <div style="height:6px;background:#F3F4F6;border-radius:3px;overflow:hidden;">
        <div style="height:100%;width:<?= round($cnt/$totalOrds*100) ?>%;background:<?= $statusColors2[$s] ?? '#9CA3AF' ?>;border-radius:3px;"></div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($statusMap)): ?>
    <p style="font-size:.82rem;color:#9CA3AF;">No orders in this period.</p>
    <?php endif; ?>
  </div>

</div>

<!-- Top products + category revenue -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

  <!-- Top products -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
    <div style="padding:1.1rem 1.5rem;border-bottom:1px solid #E5E7EB;font-weight:700;font-size:.88rem;">Top Products</div>
    <?php if (empty($topProducts)): ?>
    <p style="padding:1.5rem;color:#9CA3AF;font-size:.85rem;">No sales data for this period.</p>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;">
      <tbody>
        <?php foreach ($topProducts as $i => $tp): ?>
        <tr style="border-bottom:1px solid #F0F0F0;">
          <td style="padding:.7rem 1.25rem;font-size:.8rem;color:#9CA3AF;width:28px;"><?= $i+1 ?></td>
          <td style="padding:.7rem .5rem;">
            <img src="<?= htmlspecialchars($tp['image'] ?? '') ?>" alt="" style="width:36px;height:36px;border-radius:6px;object-fit:cover;border:1px solid #E5E7EB;">
          </td>
          <td style="padding:.7rem 1rem;">
            <div style="font-size:.83rem;font-weight:600;"><?= htmlspecialchars($tp['name']) ?></div>
            <div style="font-size:.72rem;color:#9CA3AF;"><?= number_format($tp['units']) ?> units</div>
          </td>
          <td style="padding:.7rem 1.25rem;text-align:right;font-size:.83rem;font-weight:700;color:var(--gyc-green-700);"><?= formatPrice($tp['revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <!-- Revenue by category -->
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.5rem;">
    <h2 style="font-size:.88rem;font-weight:700;margin-bottom:1.25rem;">Revenue by Category</h2>
    <?php
    $totalCatRev = array_sum(array_column($catRevenue, 'rev')) ?: 1;
    $catColors   = ['#166534','#3B82F6','#F59E0B','#8B5CF6','#EF4444','#10B981'];
    foreach ($catRevenue as $ci => $cr): ?>
    <div style="margin-bottom:1rem;">
      <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
        <span style="font-size:.82rem;font-weight:600;color:#374151;"><?= htmlspecialchars($cr['cat']) ?></span>
        <span style="font-size:.82rem;font-weight:700;color:var(--gyc-green-700);"><?= formatPrice($cr['rev']) ?></span>
      </div>
      <div style="height:8px;background:#F3F4F6;border-radius:4px;overflow:hidden;">
        <div style="height:100%;width:<?= round($cr['rev']/$totalCatRev*100) ?>%;background:<?= $catColors[$ci % count($catColors)] ?>;border-radius:4px;transition:width .5s;"></div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($catRevenue)): ?>
    <p style="font-size:.82rem;color:#9CA3AF;">No category data for this period.</p>
    <?php endif; ?>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const canvas = document.getElementById('dailyChart');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  const labels  = <?= json_encode(array_column($dailyData, 'label')) ?>;
  const revenue = <?= json_encode(array_column($dailyData, 'revenue')) ?>;
  const maxRev  = Math.max(...revenue, 1);
  const W = canvas.offsetWidth || 600;
  const H = 200;
  canvas.width = W; canvas.height = H;

  const padL = 52, padR = 15, padT = 20, padB = 36;
  const cW = W - padL - padR;
  const cH = H - padT - padB;
  const n  = labels.length;

  // Grid lines
  for (let i = 0; i <= 4; i++) {
    const y = padT + cH - (cH * i / 4);
    ctx.strokeStyle = '#F0F0F0'; ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(W - padR, y); ctx.stroke();
    ctx.fillStyle = '#9CA3AF'; ctx.font = '10px Inter,sans-serif'; ctx.textAlign = 'right';
    const v = maxRev * i / 4;
    ctx.fillText(v >= 1000 ? (v/1000).toFixed(0)+'k' : Math.round(v), padL-4, y+4);
  }

  // Area fill
  const pts = revenue.map((r, i) => ({x: padL + i*(cW/(n-1||1)), y: padT + cH - (r/maxRev)*cH}));
  ctx.beginPath();
  pts.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y));
  ctx.lineTo(pts[pts.length-1].x, padT+cH);
  ctx.lineTo(pts[0].x, padT+cH);
  ctx.closePath();
  const grad = ctx.createLinearGradient(0, padT, 0, padT+cH);
  grad.addColorStop(0, 'rgba(22,101,52,.25)');
  grad.addColorStop(1, 'rgba(22,101,52,.02)');
  ctx.fillStyle = grad; ctx.fill();

  // Line
  ctx.beginPath();
  pts.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y));
  ctx.strokeStyle = '#166534'; ctx.lineWidth = 2; ctx.stroke();

  // Dots + x-labels — skip if too many
  const step = n > 20 ? Math.ceil(n/10) : 1;
  pts.forEach(function(p, i) {
    ctx.beginPath(); ctx.arc(p.x, p.y, 3, 0, Math.PI*2);
    ctx.fillStyle = '#166534'; ctx.fill();
    if (i % step === 0) {
      ctx.fillStyle = '#9CA3AF'; ctx.font = '9px Inter,sans-serif'; ctx.textAlign = 'center';
      ctx.fillText(labels[i], p.x, H - padB + 14);
    }
  });

  if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
