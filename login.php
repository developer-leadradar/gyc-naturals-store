<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? SITE_URL . '/admin/index.php' : SITE_URL . '/customer-dashboard.php');
}

$redirect = sanitize($_GET['redirect'] ?? '');
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email    = strtolower(trim($_POST['email']    ?? ''));
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } elseif (login($email, $password)) {
        // Merge guest cart into user account
        mergeGuestCart($_SESSION['user_id']);

        if ($remember) {
            $token  = bin2hex(random_bytes(32));
            $expiry = time() + (30 * 24 * 3600);
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
            getDB()->update('users', ['remember_token' => hash('sha256', $token)], 'id = ?', [$_SESSION['user_id']]);
        }

        $dest = ($redirect && strpos($redirect, SITE_URL) === 0) ? $redirect : SITE_URL . '/customer-dashboard.php';
        if (isAdmin()) $dest = SITE_URL . '/admin/index.php';
        redirect($dest);
    } else {
        $error = 'Incorrect email or password. Please try again.';
    }
}

$pageTitle = 'Sign In — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container">
    <div style="max-width:460px;margin:0 auto;">

      <!-- Logo -->
      <div style="text-align:center;margin-bottom:2rem;">
        <a href="<?= SITE_URL ?>">
          <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="GYC Naturals" style="height:48px;">
        </a>
      </div>

      <div class="auth-card">
        <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--gyc-dark);margin-bottom:.35rem;text-align:center;">Welcome back</h1>
        <p style="font-size:.88rem;color:#888;text-align:center;margin-bottom:1.75rem;">Sign in to your GYC Naturals account</p>

        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:1.25rem;">
          <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($_GET['registered'])): ?>
        <div class="alert alert-success" style="margin-bottom:1.25rem;">
          <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          Account created! Please sign in.
        </div>
        <?php endif; ?>

        <?php if (!empty($_GET['reset'])): ?>
        <div class="alert alert-success" style="margin-bottom:1.25rem;">
          <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          Password reset successful. Sign in with your new password.
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= SITE_URL ?>/login.php<?= $redirect ? '?redirect=' . urlencode($redirect) : '' ?>">
          <?= csrfInput() ?>

          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="you@example.com" required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus>
          </div>

          <div class="form-group">
            <label class="form-label" style="display:flex;justify-content:space-between;">
              Password
              <a href="<?= SITE_URL ?>/forgot-password.php" style="font-size:.8rem;color:var(--gyc-green-600);">Forgot password?</a>
            </label>
            <div style="position:relative;">
              <input type="password" name="password" id="password-input" class="form-control" placeholder="Your password" required>
              <button type="button" onclick="togglePassword()" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#aaa;">
                <i data-lucide="eye" style="width:16px;height:16px;" id="eye-icon"></i>
              </button>
            </div>
          </div>

          <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;color:#555;margin-bottom:1.25rem;cursor:pointer;">
            <input type="checkbox" name="remember" style="width:15px;height:15px;">
            Keep me signed in for 30 days
          </label>

          <button type="submit" class="btn btn-green btn-lg" style="width:100%;justify-content:center;">
            Sign In
            <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
          </button>
        </form>

        <div style="text-align:center;margin-top:1.5rem;font-size:.88rem;color:#888;">
          Don't have an account?
          <a href="<?= SITE_URL ?>/register.php" style="color:var(--gyc-green-600);font-weight:600;">Create one</a>
        </div>

        <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gyc-green-100);text-align:center;">
          <a href="<?= SITE_URL ?>/shop.php" style="font-size:.82rem;color:#888;">
            <i data-lucide="shopping-bag" style="width:14px;height:14px;vertical-align:middle;"></i>
            Continue as guest
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function togglePassword() {
  const input = document.getElementById('password-input');
  const icon  = document.getElementById('eye-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.setAttribute('data-lucide', 'eye-off');
  } else {
    input.type = 'password';
    icon.setAttribute('data-lucide', 'eye');
  }
  if (typeof lucide !== 'undefined') lucide.createIcons();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
