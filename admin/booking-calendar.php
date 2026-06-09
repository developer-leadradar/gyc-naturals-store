<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Booking Calendar';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Fetch appointments for the selected month
$year  = (int)($_GET['year']  ?? date('Y'));
$month = (int)($_GET['month'] ?? date('n'));
if ($month < 1) { $month = 12; $year--; }
if ($month > 12) { $month = 1; $year++; }

$monthStart = sprintf('%04d-%02d-01', $year, $month);
$monthEnd   = date('Y-m-t', strtotime($monthStart));

$apts = $db->fetchAll(
    "SELECT a.*, gi.title as style_name
     FROM appointments a
     LEFT JOIN gallery_images gi ON a.gallery_image_id = gi.id
     WHERE a.requested_date BETWEEN ? AND ?
     AND a.status NOT IN ('cancelled')
     ORDER BY a.requested_date ASC, a.requested_time ASC",
    [$monthStart, $monthEnd]
);

// Group by date
$aptsByDate = [];
foreach ($apts as $apt) {
    $aptsByDate[$apt['requested_date']][] = $apt;
}

$statusColors = ['pending'=>'#F59E0B','confirmed'=>'#166534','completed'=>'#3B82F6','no_show'=>'#9CA3AF'];

// Calendar grid
$firstDow   = (int)date('N', strtotime($monthStart)); // 1=Mon … 7=Sun
$daysInMonth= (int)date('t', strtotime($monthStart));
$prevYear   = $month === 1 ? $year-1 : $year;
$prevMonth  = $month === 1 ? 12 : $month-1;
$nextYear   = $month === 12 ? $year+1 : $year;
$nextMonth  = $month === 12 ? 1 : $month+1;
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
  <div style="display:flex;align-items:center;gap:1rem;">
    <a href="?year=<?= $prevYear ?>&month=<?= $prevMonth ?>" class="btn btn-outline-green btn-sm">‹ Prev</a>
    <h2 style="font-size:1.1rem;font-weight:700;font-family:'Playfair Display',serif;"><?= date('F Y', strtotime($monthStart)) ?></h2>
    <a href="?year=<?= $nextYear ?>&month=<?= $nextMonth ?>" class="btn btn-outline-green btn-sm">Next ›</a>
  </div>
  <div style="display:flex;gap:.5rem;">
    <a href="?year=<?= date('Y') ?>&month=<?= date('n') ?>" class="btn btn-green btn-sm">Today</a>
    <a href="<?= SITE_URL ?>/admin/appointments.php" class="btn btn-outline-green btn-sm">List View</a>
  </div>
</div>

<!-- Calendar grid -->
<div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;">
  <!-- Day headers -->
  <div style="display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid #E5E7EB;">
    <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow): ?>
    <div style="padding:.65rem;text-align:center;font-size:.75rem;font-weight:700;color:#9CA3AF;text-transform:uppercase;"><?= $dow ?></div>
    <?php endforeach; ?>
  </div>
  <!-- Weeks -->
  <?php
  $col    = $firstDow;   // 1=Mon, first col offset
  $day    = 1;
  $weeks  = (int)ceil(($daysInMonth + $firstDow - 1) / 7);
  for ($w = 0; $w < $weeks; $w++):
  ?>
  <div style="display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid #E5E7EB;">
    <?php for ($c = 1; $c <= 7; $c++):
      $isBlank = ($w === 0 && $c < $firstDow) || $day > $daysInMonth;
      $dateStr  = $isBlank ? '' : sprintf('%04d-%02d-%02d', $year, $month, $day);
      $isToday  = $dateStr === date('Y-m-d');
      $dayApts  = $dateStr ? ($aptsByDate[$dateStr] ?? []) : [];
    ?>
    <div style="min-height:90px;padding:.5rem;border-right:1px solid #F0F0F0;<?= $isBlank ? 'background:#FAFAFA;' : '' ?><?= $isToday ? 'background:#F0FFF4;' : '' ?>">
      <?php if (!$isBlank): ?>
      <div style="font-size:.8rem;font-weight:<?= $isToday?'700':'500' ?>;color:<?= $isToday?'var(--gyc-green-700)':'#374151' ?>;margin-bottom:.35rem;"><?= $day ?></div>
      <?php foreach (array_slice($dayApts, 0, 3) as $apt): ?>
      <a href="<?= SITE_URL ?>/admin/appointments.php?view=<?= $apt['id'] ?>"
         style="display:block;font-size:.68rem;font-weight:600;padding:.2rem .35rem;border-radius:4px;margin-bottom:.2rem;text-decoration:none;background:<?= $statusColors[$apt['status']]??'#9CA3AF' ?>20;color:<?= $statusColors[$apt['status']]??'#9CA3AF' ?>;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
         title="<?= htmlspecialchars($apt['customer_name']) ?> — <?= htmlspecialchars($apt['style_name']??'') ?>">
        <?= $apt['requested_time'] ? date('g:i', strtotime($apt['requested_time'])).' ' : '' ?><?= htmlspecialchars(substr($apt['customer_name'],0,12)) ?>
      </a>
      <?php endforeach; ?>
      <?php if (count($dayApts) > 3): ?>
      <a href="<?= SITE_URL ?>/admin/appointments.php?date=<?= $dateStr ?>" style="font-size:.65rem;color:var(--gyc-green-600);text-decoration:none;">+<?= count($dayApts)-3 ?> more</a>
      <?php endif; ?>
      <?php $day++; ?>
      <?php endif; ?>
    </div>
    <?php endfor; ?>
  </div>
  <?php endfor; ?>
</div>

<!-- Month summary -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-top:1.5rem;">
  <?php
  $totalApts     = count($apts);
  $pendingCount  = count(array_filter($apts, fn($a) => $a['status'] === 'pending'));
  $confirmedCount= count(array_filter($apts, fn($a) => $a['status'] === 'confirmed'));
  $depositRevenue= array_sum(array_column(array_filter($apts, fn($a) => $a['deposit_paid']), 'deposit_amount'));
  $summary = [
    ['Total Bookings', $totalApts,  'calendar','var(--gyc-green-700)','var(--gyc-green-100)'],
    ['Confirmed',    $confirmedCount,'check-circle','#065F46','#ECFDF5'],
    ['Pending',      $pendingCount, 'clock','#92400E','#FFFBEB'],
    ['Deposits In', '₦'.number_format($depositRevenue),'trending-up','#1D4ED8','#EFF6FF'],
  ];
  foreach ($summary as $s): ?>
  <div style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.25rem;display:flex;align-items:center;gap:1rem;">
    <div style="width:40px;height:40px;border-radius:8px;background:<?= $s[4] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <i data-lucide="<?= $s[2] ?>" style="width:18px;height:18px;color:<?= $s[3] ?>;"></i>
    </div>
    <div>
      <div style="font-family:'Playfair Display',serif;font-size:1.3rem;font-weight:700;"><?= $s[1] ?></div>
      <div style="font-size:.75rem;color:#9CA3AF;"><?= $s[0] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
