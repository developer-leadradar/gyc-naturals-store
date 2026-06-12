<?php
defined('GYC_ACCESS') or define('GYC_ACCESS', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$cartCount      = getCartCount();
$navWishCount   = (isLoggedIn() && !isAdmin()) ? (int)(getDB()->fetchOne("SELECT COUNT(*) as c FROM wishlist WHERE user_id=?", [$_SESSION['user_id'] ?? 0])['c'] ?? 0) : 0;
$moodboardNote  = ''; // Set to 'yes' for pages that show moodboard count via JS

// Page-specific OG defaults
$ogTitle       = $ogTitle       ?? SITE_NAME . ' — Grow Your Crown';
$ogDescription = $ogDescription ?? 'Professional African hair braiding, natural hair products & fashion. Book your appointment online.';
$ogImage       = $ogImage       ?? SITE_URL . '/assets/images/og-default.jpg';
$ogUrl         = $ogUrl         ?? SITE_URL . $_SERVER['REQUEST_URI'];
$pageTitle     = $pageTitle     ?? SITE_NAME;
csrfToken(); // ensure gyc_csrf cookie is set before HTML output begins
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($ogDescription) ?>">

  <!-- OpenGraph -->
  <meta property="og:type"        content="website">
  <meta property="og:site_name"   content="GYC Naturals">
  <meta property="og:title"       content="<?= htmlspecialchars($ogTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($ogDescription) ?>">
  <meta property="og:image"       content="<?= htmlspecialchars($ogImage) ?>">
  <meta property="og:url"         content="<?= htmlspecialchars($ogUrl) ?>">
  <meta name="twitter:card"       content="summary_large_image">
  <meta name="twitter:title"      content="<?= htmlspecialchars($ogTitle) ?>">
  <meta name="twitter:image"      content="<?= htmlspecialchars($ogImage) ?>">

  <!-- PWA -->
  <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
  <meta name="theme-color" content="#166534">
  <link rel="apple-touch-icon" href="<?= SITE_URL ?>/assets/images/icon-192.png">
  <link rel="icon" href="<?= SITE_URL ?>/assets/images/favicon.ico" type="image/x-icon">
  <link rel="sitemap" type="application/xml" href="<?= SITE_URL ?>/sitemap.php">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

  <!-- CSS -->
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/home-additions.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/pages.css">
  <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/responsive.css">

  <!-- Paystack -->
  <script src="https://js.paystack.co/v1/inline.js" defer></script>

  <!-- Lucide Icons -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
</head>
<body>

<!-- Kente stripe -->
<div class="kente-stripe"></div>

<!-- NAVIGATION -->
<header class="site-header">
  <nav class="nav-inner">
    <!-- Logo -->
    <a href="<?= SITE_URL ?>/" class="nav-logo" aria-label="GYC Naturals Home">
      <img src="<?= SITE_URL ?>/assets/images/gyc-logo-horizontal.svg"
           alt="GYC Naturals"
           width="200" height="50">
    </a>

    <!-- Desktop links -->
    <div class="nav-links">
      <a href="<?= SITE_URL ?>/"                     class="<?= ($_SERVER['REQUEST_URI']==='/'||$_SERVER['REQUEST_URI']==='/index.php')?'active':'' ?>">Home</a>
      <a href="<?= SITE_URL ?>/gallery.php"          class="<?= (strpos($_SERVER['REQUEST_URI'],'gallery')!==false&&strpos($_SERVER['REQUEST_URI'],'admin')===false)?'active':'' ?>">Gallery</a>
      <a href="<?= SITE_URL ?>/shop.php"             class="<?= strpos($_SERVER['REQUEST_URI'],'/shop')!==false&&strpos($_SERVER['REQUEST_URI'],'clothing')===false?'active':'' ?>">Shop</a>
      <a href="<?= SITE_URL ?>/clothing.php"         class="<?= strpos($_SERVER['REQUEST_URI'],'clothing')!==false?'active':'' ?>">Clothing</a>
      <a href="<?= SITE_URL ?>/book-appointment.php" class="<?= strpos($_SERVER['REQUEST_URI'],'book')!==false?'active':'' ?>">Book GYC</a>
      <a href="<?= SITE_URL ?>/quiz.php"             class="<?= strpos($_SERVER['REQUEST_URI'],'quiz')!==false?'active':'' ?>">Hair Quiz</a>
      <a href="<?= SITE_URL ?>/about.php"            class="<?= strpos($_SERVER['REQUEST_URI'],'about')!==false?'active':'' ?>">About</a>
    </div>

    <!-- Actions -->
    <div class="nav-actions">
      <!-- Search -->
      <a href="<?= SITE_URL ?>/search.php" class="nav-icon-btn" aria-label="Search" title="Search">
        <i data-lucide="search" style="width:20px;height:20px;"></i>
      </a>

      <!-- Wishlist (logged in) -->
      <?php if (isLoggedIn() && !isAdmin()): ?>
      <a href="<?= SITE_URL ?>/my-wishlist.php" class="nav-icon-btn" aria-label="Wishlist" title="Wishlist" style="position:relative;">
        <i data-lucide="heart" style="width:20px;height:20px;"></i>
        <span class="nav-badge" id="wishlist-count" <?= $navWishCount > 0 ? '' : 'style="display:none"' ?>><?= $navWishCount ?></span>
      </a>
      <?php endif; ?>

      <!-- Cart -->
      <a href="<?= SITE_URL ?>/cart.php" class="nav-icon-btn" aria-label="Cart" title="Cart">
        <i data-lucide="shopping-bag" style="width:20px;height:20px;"></i>
        <?php if ($cartCount > 0): ?>
        <span class="nav-badge" id="cart-count"><?= $cartCount ?></span>
        <?php else: ?>
        <span class="nav-badge" id="cart-count" style="display:none"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>

      <!-- User -->
      <?php if (isLoggedIn()): ?>
      <a href="<?= isAdmin() ? SITE_URL.'/admin/index.php' : SITE_URL.'/customer-dashboard.php' ?>"
         class="nav-icon-btn" aria-label="Account" title="My Account">
        <i data-lucide="user-circle" style="width:20px;height:20px;"></i>
      </a>
      <a href="<?= SITE_URL ?>/logout.php" class="nav-icon-btn" aria-label="Logout" title="Logout">
        <i data-lucide="log-out" style="width:20px;height:20px;"></i>
      </a>
      <?php else: ?>
      <a href="<?= SITE_URL ?>/login.php" class="btn btn-green btn-sm" style="margin-left:0.25rem;">Login</a>
      <?php endif; ?>

      <!-- Hamburger -->
      <button class="nav-hamburger" id="nav-toggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- Mobile nav -->
  <div class="nav-mobile" id="nav-mobile">
    <a href="<?= SITE_URL ?>/">Home</a>
    <a href="<?= SITE_URL ?>/gallery.php">Gallery</a>
    <a href="<?= SITE_URL ?>/shop.php">Shop</a>
    <a href="<?= SITE_URL ?>/clothing.php">Clothing</a>
    <a href="<?= SITE_URL ?>/book-appointment.php">Book Appointment</a>
    <a href="<?= SITE_URL ?>/quiz.php">Hair Quiz</a>
    <a href="<?= SITE_URL ?>/about.php">About</a>
    <a href="<?= SITE_URL ?>/contact.php">Contact</a>
    <?php if (isLoggedIn()): ?>
    <a href="<?= isAdmin() ? SITE_URL.'/admin/index.php' : SITE_URL.'/customer-dashboard.php' ?>">My Account</a>
    <a href="<?= SITE_URL ?>/logout.php">Logout</a>
    <?php else: ?>
    <a href="<?= SITE_URL ?>/login.php" style="color:var(--gyc-green-700);font-weight:600;">Login / Register</a>
    <?php endif; ?>
  </div>
</header>

<!-- Toast container -->
<div class="toast-container" id="toast-container"></div>

<?php
// Flash messages from session
if (!empty($_SESSION['flash'])): ?>
<div style="max-width:1280px;margin:1rem auto;padding:0 1.5rem;">
  <div class="alert alert-<?= $_SESSION['flash']['type'] ?? 'info' ?>">
    <i data-lucide="<?= ($_SESSION['flash']['type']==='success')?'check-circle':'alert-circle' ?>" style="width:18px;height:18px;flex-shrink:0;"></i>
    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
  </div>
</div>
<?php unset($_SESSION['flash']); endif; ?>
