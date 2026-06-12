<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();
$user    = getCurrentUser();
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = sanitize($_POST['action'] ?? '');

    if ($action === 'profile') {
        $firstName = trim(sanitize($_POST['first_name'] ?? ''));
        $lastName  = trim(sanitize($_POST['last_name']  ?? ''));
        $phone     = trim(sanitize($_POST['phone']      ?? ''));

        if (!$firstName || !$lastName) {
            $error = 'Name is required.';
        } else {
            getDB()->update('users', [
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'phone'      => $phone,
            ], 'id = ?', [$user['id']]);
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $user    = getCurrentUser();
            $success = 'Profile updated successfully.';
        }

    } elseif ($action === 'password') {
        $current  = $_POST['current_password'] ?? '';
        $newPass  = $_POST['new_password']     ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPass !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            $hashed = password_hash($newPass, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
            getDB()->update('users', ['password' => $hashed], 'id = ?', [$user['id']]);
            $success = 'Password updated successfully.';
        }
    }
}

$pageTitle = 'My Profile — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>
<div style="min-height:72px;"></div>
<section style="padding:2.5rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div style="max-width:960px;margin:0 auto;">
      <div>
        <h1 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1.5rem;">My Profile</h1>

        <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem;">
          <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:1.5rem;">
          <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Profile info -->
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem 2rem;margin-bottom:1.5rem;">
          <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;">Personal Information</h2>
          <form method="POST">
            <?= csrfInput() ?>
            <input type="hidden" name="action" value="profile">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
              <div class="form-group">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($user['first_name']) ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($user['last_name']) ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled
                     style="background:var(--gyc-green-100);cursor:not-allowed;">
              <p class="form-hint">Email cannot be changed. Contact support if needed.</p>
            </div>
            <div class="form-group">
              <label class="form-label">Phone / WhatsApp</label>
              <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+234 xxx xxx xxxx">
            </div>
            <button type="submit" class="btn btn-green">Save Changes</button>
          </form>
        </div>

        <!-- Change password -->
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem 2rem;">
          <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;">Change Password</h2>
          <form method="POST">
            <?= csrfInput() ?>
            <input type="hidden" name="action" value="password">
            <div class="form-group">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control" required placeholder="Your current password">
            </div>
            <div class="form-group">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" required placeholder="At least 8 characters" minlength="8">
            </div>
            <div class="form-group">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat new password">
            </div>
            <button type="submit" class="btn btn-outline-green">Update Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
