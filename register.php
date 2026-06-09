<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/email-templates.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/customer-dashboard.php');
}

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $firstName = trim(sanitize($_POST['first_name'] ?? ''));
    $lastName  = trim(sanitize($_POST['last_name']  ?? ''));
    $email     = strtolower(trim(sanitize($_POST['email']    ?? '')));
    $phone     = trim(sanitize($_POST['phone']     ?? ''));
    $password  = $_POST['password']  ?? '';
    $confirm   = $_POST['confirm']   ?? '';

    if (!$firstName || !$lastName) {
        $error = 'Please enter your full name.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = register([
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'email'          => $email,
            'phone'          => $phone,
            'password'       => $password,
            'role'           => 'customer',
            'is_active'      => 1,
            'email_verified' => 1, // Auto-verify for now (email verification optional)
        ]);

        if ($result['success']) {
            // Send branded welcome email
            $welcomeHtml = emailWelcome($firstName, $email);
            sendEmail($email, 'Welcome to GYC Naturals — Your Account is Ready! 🌿', $welcomeHtml);

            redirect(SITE_URL . '/login.php?registered=1');
        } else {
            $error = $result['message'];
        }
    }
}

$pageTitle = 'Create Account — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container">
    <div style="max-width:480px;margin:0 auto;">

      <div style="text-align:center;margin-bottom:2rem;">
        <a href="<?= SITE_URL ?>">
          <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="GYC Naturals" style="height:48px;">
        </a>
      </div>

      <div class="auth-card">
        <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem;color:var(--gyc-dark);margin-bottom:.35rem;text-align:center;">Create Your Account</h1>
        <p style="font-size:.88rem;color:#888;text-align:center;margin-bottom:1.75rem;">Join GYC Naturals for faster checkout and order tracking</p>

        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:1.25rem;">
          <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
          <?= csrfInput() ?>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            <div class="form-group">
              <label class="form-label">First Name <span class="required">*</span></label>
              <input type="text" name="first_name" class="form-control" required
                     value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Last Name <span class="required">*</span></label>
              <input type="text" name="last_name" class="form-control" required
                     value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Email Address <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" required placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Phone / WhatsApp</label>
            <input type="tel" name="phone" class="form-control" placeholder="+234 xxx xxx xxxx"
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">Password <span class="required">*</span></label>
            <input type="password" name="password" class="form-control" required
                   placeholder="At least 8 characters" minlength="8">
            <p class="form-hint">Use a mix of letters, numbers and symbols</p>
          </div>

          <div class="form-group">
            <label class="form-label">Confirm Password <span class="required">*</span></label>
            <input type="password" name="confirm" class="form-control" required placeholder="Repeat your password">
          </div>

          <label style="display:flex;align-items:flex-start;gap:.5rem;font-size:.82rem;color:#555;margin-bottom:1.5rem;cursor:pointer;line-height:1.5;">
            <input type="checkbox" name="agree" required style="width:15px;height:15px;margin-top:2px;flex-shrink:0;">
            I agree to GYC Naturals <a href="<?= SITE_URL ?>/terms.php" target="_blank">Terms of Service</a> and <a href="<?= SITE_URL ?>/privacy.php" target="_blank">Privacy Policy</a>
          </label>

          <button type="submit" class="btn btn-green btn-lg" style="width:100%;justify-content:center;">
            Create Account
            <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
          </button>
        </form>

        <div style="text-align:center;margin-top:1.5rem;font-size:.88rem;color:#888;">
          Already have an account?
          <a href="<?= SITE_URL ?>/login.php" style="color:var(--gyc-green-600);font-weight:600;">Sign in</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
