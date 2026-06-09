<?php
// Dashboard sidebar — shared by all customer account pages
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$dashLinks = [
    ['customer-dashboard', 'layout-dashboard', 'Overview'],
    ['my-orders',          'package',          'My Orders'],
    ['my-appointments',    'calendar',         'Appointments'],
    ['my-wishlist',        'heart',            'Wishlist'],
    ['my-profile',         'user',             'My Profile'],
];
?>
<aside class="dash-sidebar">
  <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--gyc-green-100);">
    <div style="font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.25rem;">Account</div>
    <div style="font-weight:600;font-size:.92rem;color:var(--gyc-dark);"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
    <div style="font-size:.75rem;color:#888;"><?= htmlspecialchars($user['email']) ?></div>
  </div>
  <?php foreach ($dashLinks as $link): ?>
  <a href="<?= SITE_URL ?>/<?= $link[0] ?>.php"
     class="dash-sidebar-link <?= $currentPage === $link[0] ? 'active' : '' ?>">
    <i data-lucide="<?= $link[1] ?>" style="width:16px;height:16px;flex-shrink:0;"></i>
    <?= $link[2] ?>
  </a>
  <?php endforeach; ?>
  <a href="<?= SITE_URL ?>/logout.php" class="dash-sidebar-link" style="color:var(--gyc-terra);">
    <i data-lucide="log-out" style="width:16px;height:16px;flex-shrink:0;"></i>
    Sign Out
  </a>
</aside>
