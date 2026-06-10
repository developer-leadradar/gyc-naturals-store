<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/email-templates.php';

$sent  = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $email = strtolower(trim(sanitize($_POST['email'] ?? '')));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $user = getDB()->fetchOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            getDB()->update('users', [
                'reset_token'         => hash('sha256', $token),
                'reset_token_expires' => $expires,
            ], 'id = ?', [$user['id']]);

            $resetUrl  = SITE_URL . '/reset-password.php?token=' . urlencode($token) . '&email=' . urlencode($email);
            $emailBody = emailPasswordReset($user['first_name'], $resetUrl);
            sendEmail($email, 'Reset Your GYC Naturals Password', $emailBody);
        }
        // Always show success to prevent email enumeration
        $sent = true;
    }
}

$pageTitle = 'Forgot Password — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container">
    <div style="max-width:440px;margin:0 auto;">

      <div style="text-align:center;margin-bottom:2rem;">
        <a href="<?= SITE_URL ?>">
          <img src="<?= SITE_URL ?>/assets/images/gyc-logo-horizontal.svg" alt="GYC Naturals" style="height:48px;">
        </a>
      </div>

      <div class="auth-card">
        <div style="text-align:center;margin-bottom:1.5rem;">
          <div style="width:56px;height:56px;border-radius:50%;background:var(--gyc-green-100);display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;">
            <i data-lucide="key" style="width:24px;height:24px;color:var(--gyc-green-600);"></i>
          </div>
          <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;color:var(--gyc-dark);margin:0 0 .3rem;">Forgot Password?</h1>
          <p style="font-size:.88rem;color:#888;">Enter your email and we'll send a reset link</p>
        </div>

        <?php if ($sent): ?>
        <div class="alert alert-success">
          <i data-lucide="mail" style="width:18px;height:18px;flex-shrink:0;"></i>
          <div>
            <strong>Check your inbox!</strong><br>
            <span style="font-size:.85rem;">If that email is registered with us, a password reset link has been sent. Check your spam folder too.</span>
          </div>
        </div>
        <div style="text-align:center;margin-top:1.25rem;">
          <a href="<?= SITE_URL ?>/login.php" class="btn btn-green" style="justify-content:center;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Back to Sign In
          </a>
        </div>

        <?php else: ?>

        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:1.25rem;">
          <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
          <?= csrfInput() ?>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required
                   placeholder="The email on your account"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus>
          </div>
          <button type="submit" class="btn btn-green btn-lg" style="width:100%;justify-content:center;">
            Send Reset Link
            <i data-lucide="send" style="width:18px;height:18px;"></i>
          </button>
        </form>

        <div style="text-align:center;margin-top:1.25rem;">
          <a href="<?= SITE_URL ?>/login.php" style="font-size:.85rem;color:#888;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;vertical-align:middle;"></i>
            Back to Sign In
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
