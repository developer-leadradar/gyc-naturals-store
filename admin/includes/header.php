<?php
defined('GYC_ACCESS') or define('GYC_ACCESS', true);
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
requireAdmin();

$adminPageTitle = $adminPageTitle ?? 'Dashboard';
$currentPath    = $_SERVER['REQUEST_URI'];
$_unreadMessages = (int)(getDB()->fetchOne("SELECT COUNT(*) c FROM contact_messages WHERE is_read=0")['c'] ?? 0);
$_pendingTestimonials = (int)(getDB()->fetchOne("SELECT COUNT(*) c FROM testimonials WHERE is_approved=0")['c'] ?? 0);

function adminNavLink($href, $label, $icon, $current, $badge = 0) {
    $active  = (strpos($current, $href) !== false) ? ' active' : '';
    $badgeHtml = $badge > 0
        ? "<span style='margin-left:auto;background:#EF4444;color:#fff;font-size:.65rem;font-weight:700;border-radius:10px;padding:.1rem .4rem;min-width:18px;text-align:center;'>$badge</span>"
        : '';
    return "<a href=\"$href\" class=\"sidebar-link$active\" style=\"display:flex;align-items:center;gap:.6rem;\">
              <i data-lucide=\"$icon\" style=\"width:16px;height:16px;flex-shrink:0;\"></i>
              <span>$label</span>$badgeHtml
            </a>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($adminPageTitle) ?> — GYC Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
  <link rel="icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
</head>
<body class="admin-body">
<div class="admin-layout">

<!-- SIDEBAR -->
<aside class="admin-sidebar" id="admin-sidebar">

  <div class="sidebar-logo">
    <img src="<?= SITE_URL ?>/assets/images/gyc-logo-horizontal.svg" alt="GYC Naturals"
         style="filter:brightness(0) invert(1); height:38px; width:auto;">
  </div>
  <div class="sidebar-kente"></div>

  <nav class="sidebar-nav">
    <!-- Dashboard -->
    <?= adminNavLink(SITE_URL.'/admin/index.php', 'Dashboard', 'layout-dashboard', $currentPath) ?>

    <!-- Gallery -->
    <div class="sidebar-section-label">Gallery</div>
    <?= adminNavLink(SITE_URL.'/admin/gallery.php',            'Gallery Images',    'image', $currentPath) ?>
    <?= adminNavLink(SITE_URL.'/admin/add-gallery.php',        'Add New Style',     'plus-circle', $currentPath) ?>
    <?= adminNavLink(SITE_URL.'/admin/gallery-categories.php', 'Gallery Categories','folder', $currentPath) ?>

    <!-- Appointments -->
    <div class="sidebar-section-label">Appointments</div>
    <?= adminNavLink(SITE_URL.'/admin/appointments.php', 'All Appointments', 'calendar', $currentPath) ?>
    <?= adminNavLink(SITE_URL.'/admin/waitlist.php',     'Waiting List',     'clock', $currentPath) ?>

    <!-- Shop -->
    <div class="sidebar-section-label">Shop</div>
    <?= adminNavLink(SITE_URL.'/admin/products.php',    'Products',    'package', $currentPath) ?>
    <?= adminNavLink(SITE_URL.'/admin/add-product.php', 'Add Product', 'plus-circle', $currentPath) ?>
    <?= adminNavLink(SITE_URL.'/admin/bundles.php',     'Bundles',     'gift', $currentPath) ?>
    <?= adminNavLink(SITE_URL.'/admin/categories.php',  'Categories',  'tag', $currentPath) ?>

    <!-- Orders -->
    <div class="sidebar-section-label">Orders</div>
    <?= adminNavLink(SITE_URL.'/admin/orders.php', 'All Orders', 'shopping-cart', $currentPath) ?>

    <!-- Customers -->
    <div class="sidebar-section-label">Customers</div>
    <?= adminNavLink(SITE_URL.'/admin/customers.php', 'All Customers', 'users', $currentPath) ?>

    <!-- Communications -->
    <div class="sidebar-section-label">Communications</div>
    <?= adminNavLink(SITE_URL.'/admin/messages.php', 'Messages', 'mail', $currentPath, $_unreadMessages) ?>

    <!-- Content -->
    <div class="sidebar-section-label">Content</div>
    <?= adminNavLink(SITE_URL.'/admin/testimonials.php', 'Testimonials','star', $currentPath, $_pendingTestimonials) ?>

    <!-- Reports & Settings -->
    <div class="sidebar-section-label">System</div>
    <?= adminNavLink(SITE_URL.'/admin/reports.php',  'Reports',  'bar-chart-2', $currentPath) ?>
    <?= adminNavLink(SITE_URL.'/admin/settings.php', 'Settings', 'settings', $currentPath) ?>
  </nav>

  <div class="sidebar-signout">
    <a href="<?= SITE_URL ?>/admin/logout.php">
      <i data-lucide="log-out" style="width:16px;height:16px;"></i>
      Sign Out
    </a>
  </div>
</aside>

<!-- MAIN AREA -->
<div class="admin-main">

  <!-- Top bar -->
  <div class="admin-topbar">
    <div style="display:flex;align-items:center;gap:1rem;">
      <button onclick="document.getElementById('admin-sidebar').classList.toggle('open')"
              class="admin-mobile-toggle" id="sidebar-toggle" aria-label="Open menu">
        <i data-lucide="menu" style="width:22px;height:22px;"></i>
      </button>
      <span style="font-weight:600;font-size:1rem;"><?= htmlspecialchars($adminPageTitle) ?></span>
    </div>
    <div style="display:flex;align-items:center;gap:1rem;">
      <a href="<?= SITE_URL ?>/" target="_blank"
         style="font-size:0.8rem;color:#888;text-decoration:none;display:flex;align-items:center;gap:0.3rem;">
        <i data-lucide="external-link" style="width:14px;height:14px;"></i>
        View Site
      </a>
      <span style="font-size:0.82rem;color:#555;">
        👋 <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
      </span>
    </div>
  </div>

  <!-- Page content -->
  <div class="admin-content">
<?php
// Flash messages
if (!empty($_SESSION['flash'])): ?>
<div class="alert alert-<?= $_SESSION['flash']['type'] ?? 'info' ?>" style="margin-bottom:1.5rem;">
  <?= htmlspecialchars($_SESSION['flash']['message']) ?>
</div>
<?php unset($_SESSION['flash']); endif; ?>
