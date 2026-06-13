<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in as admin → redirect
if (isAdmin()) {
    redirect(SITE_URL . '/admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (!$email || !$password) {
        $error = 'Email and password are required.';
    } else {
        if (login($email, $password)) {
            if (isAdmin()) {
                redirect(SITE_URL . '/admin/index.php');
                exit;
            }
            // Logged in but not admin — clear session without redirect
            unset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['user_name'], $_SESSION['user_role']);
            $error = 'You do not have admin access.';
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — GYC Naturals</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<link rel="icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js" defer></script>
<style>
  body { background:#0D2B1A; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:1rem; }
  .login-card { background:#fff; border-radius:16px; padding:2.5rem; width:100%; max-width:400px; box-shadow:0 8px 40px rgba(0,0,0,.3); }
</style>
</head>
<body>
<div class="login-card">
  <!-- Logo -->
  <div style="text-align:center;margin-bottom:1.5rem;">
    <img src="<?= SITE_URL ?>/assets/images/gyc-logo-horizontal.svg" alt="GYC Naturals" style="height:52px;width:auto;display:block;margin:0 auto;"
         onerror="this.style.display='none'">
    <div style="font-size:.72rem;color:#9CA3AF;margin-top:.6rem;text-transform:uppercase;letter-spacing:.12em;">Admin Panel</div>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger" style="margin-bottom:1.5rem;display:flex;align-items:center;gap:.5rem;font-size:.875rem;">
    <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group">
      <label class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" required autofocus
             placeholder="admin@gycnaturals.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Password</label>
      <div style="position:relative;">
        <input type="password" name="password" id="pwd" class="form-control" required placeholder="Your password">
        <button type="button" onclick="togglePwd()" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9CA3AF;">
          <i data-lucide="eye" id="eye-icon" style="width:18px;height:18px;"></i>
        </button>
      </div>
    </div>
    <button type="submit" class="btn btn-green" style="margin-top:.5rem;font-size:.95rem;padding:.8rem;width:100%;justify-content:center;display:flex;">
      Sign In to Admin
    </button>
  </form>

  <div style="text-align:center;margin-top:1.5rem;">
    <a href="<?= SITE_URL ?>/" style="font-size:.8rem;color:#9CA3AF;text-decoration:none;">← Back to Store</a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof lucide !== 'undefined') lucide.createIcons();
});
function togglePwd() {
  const p = document.getElementById('pwd');
  const i = document.getElementById('eye-icon');
  if (p.type === 'password') {
    p.type = 'text';
    i.setAttribute('data-lucide', 'eye-off');
  } else {
    p.type = 'password';
    i.setAttribute('data-lucide', 'eye');
  }
  if (typeof lucide !== 'undefined') lucide.createIcons();
}
</script>
</body>
</html>
