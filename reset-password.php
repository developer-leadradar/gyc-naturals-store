<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$token = sanitize($_GET['token'] ?? '');
$email = strtolower(trim(sanitize($_GET['email'] ?? '')));
$error = '';

// Validate token
$user = null;
if ($token && $email) {
    $hashed = hash('sha256', $token);
    $user   = getDB()->fetchOne(
        "SELECT * FROM users WHERE email = ? AND reset_token = ? AND reset_token_expires > NOW() AND is_active = 1",
        [$email, $hashed]
    );
}

if (!$user) {
    redirect(SITE_URL . '/forgot-password.php?invalid=1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hashed = password_hash($password, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
        getDB()->update('users', [
            'password'            => $hashed,
            'reset_token'         => null,
            'reset_token_expires' => null,
        ], 'id = ?', [$user['id']]);
        redirect(SITE_URL . '/login.php?reset=1');
    }
}

$pageTitle = 'Reset Password — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>


<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container">
    <div style="max-width:440px;margin:0 auto;">

      <div style="text-align:center;margin-bottom:2rem;">
        <a href="<?= SITE_URL ?>">
          <img src="<?= SITE_URL ?>/assets/images/gyc-logo-horizontal.svg" alt="GYC Naturals" style="height:48px;">
        </a>
      </div>

      <div class="auth-card">
        <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;text-align:center;margin-bottom:.35rem;">Set New Password</h1>
        <p style="font-size:.88rem;color:#888;text-align:center;margin-bottom:1.75rem;">
          For <strong><?= htmlspecialchars($user['email']) ?></strong>
        </p>

        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:1.25rem;">
          <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= SITE_URL ?>/reset-password.php?token=<?= urlencode($token) ?>&email=<?= urlencode($email) ?>">
          <?= csrfInput() ?>

          <div class="form-group">
            <label class="form-label">New Password <span class="required">*</span></label>
            <input type="password" name="password" class="form-control" required
                   placeholder="At least 8 characters" minlength="8" autofocus>
          </div>

          <div class="form-group">
            <label class="form-label">Confirm New Password <span class="required">*</span></label>
            <input type="password" name="confirm" class="form-control" required
                   placeholder="Repeat your new password">
          </div>

          <button type="submit" class="btn btn-green btn-lg" style="width:100%;justify-content:center;">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            Set New Password
          </button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
