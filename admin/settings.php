<?php
define('GYC_ACCESS', true);
$adminPageTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';

$db      = getDB();
$success = '';
$error   = '';

// Load all settings into $settings array — uses correct table: site_settings, col: setting_val
$rows     = $db->fetchAll("SELECT setting_key, setting_val FROM site_settings");
$settings = [];
foreach ($rows as $row) {
    $settings[$row['setting_key']] = $row['setting_val'];
}

function saveSetting($db, $key, $value) {
    $existing = $db->fetchOne("SELECT id FROM site_settings WHERE setting_key = ?", [$key]);
    if ($existing) {
        $db->update('site_settings', ['setting_val' => $value], 'setting_key = ?', [$key]);
    } else {
        $db->insert('site_settings', ['setting_key' => $key, 'setting_val' => $value]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = sanitize($_POST['section'] ?? '');

    if ($section === 'general') {
        $keys = ['site_name','site_tagline','site_email','contact_email','site_phone','site_whatsapp','site_address','opening_hours'];
        foreach ($keys as $k) {
            saveSetting($db, $k, trim(sanitize($_POST[$k] ?? '')));
        }
        $success = 'General settings saved.';
    } elseif ($section === 'payment') {
        $keys = ['paystack_public_key','paystack_secret_key','bank_name','bank_account_name','bank_account_number'];
        foreach ($keys as $k) {
            saveSetting($db, $k, trim($_POST[$k] ?? ''));
        }
        $success = 'Payment settings saved.';
    } elseif ($section === 'shipping') {
        $keys = ['free_shipping_threshold','default_shipping_fee'];
        foreach ($keys as $k) {
            saveSetting($db, $k, trim(sanitize($_POST[$k] ?? '')));
        }
        $success = 'Shipping settings saved.';
    } elseif ($section === 'email') {
        $keys = ['resend_api_key','smtp_host','smtp_port','smtp_user','smtp_pass','from_email','from_name'];
        foreach ($keys as $k) {
            saveSetting($db, $k, trim($_POST[$k] ?? ''));
        }
        $success = 'Email settings saved.';
    } elseif ($section === 'social') {
        $keys = ['instagram_url','facebook_url','tiktok_url','twitter_url','youtube_url'];
        foreach ($keys as $k) {
            saveSetting($db, $k, trim(sanitize($_POST[$k] ?? '')));
        }
        $success = 'Social links saved.';
    } elseif ($section === 'admin_password') {
        $current = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $admin   = getCurrentUser();
        if (!password_verify($current, $admin['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newPass !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hashed = password_hash($newPass, PASSWORD_HASH_ALGO, ['cost' => PASSWORD_HASH_COST]);
            $db->update('users', ['password' => $hashed], 'id = ?', [$admin['id']]);
            $success = 'Password updated.';
        }
    }

    // Reload settings after save
    $rows = $db->fetchAll("SELECT setting_key, setting_val FROM site_settings");
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_val'];
    }
}

$s = fn($k, $default = '') => htmlspecialchars($settings[$k] ?? $default);
?>

<?php if ($success): ?>
<div class="alert alert-success" style="margin-bottom:1.5rem;"><i data-lucide="check-circle" style="width:15px;height:15px;"></i> <?= htmlspecialchars($success) ?></div>
<?php elseif ($error): ?>
<div class="alert alert-danger" style="margin-bottom:1.5rem;"><i data-lucide="alert-circle" style="width:15px;height:15px;"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:200px 1fr;gap:2rem;align-items:start;">

  <!-- Nav -->
  <nav style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;overflow:hidden;position:sticky;top:80px;">
    <?php $tabs = [
      'general'  => ['settings','General'],
      'payment'  => ['credit-card','Payments'],
      'shipping' => ['package','Shipping'],
      'email'    => ['mail','Email / SMTP'],
      'social'   => ['share-2','Social Links'],
      'security' => ['lock','Security'],
    ];
    $activeTab = sanitize($_GET['tab'] ?? 'general');
    foreach ($tabs as $tid => [$icon, $label]): ?>
    <a href="?tab=<?= $tid ?>" style="display:flex;align-items:center;gap:.6rem;padding:.75rem 1.1rem;text-decoration:none;font-size:.84rem;font-weight:<?= $activeTab === $tid ? '700' : '500' ?>;color:<?= $activeTab === $tid ? 'var(--gyc-green-700)' : '#374151' ?>;background:<?= $activeTab === $tid ? 'var(--gyc-green-100)' : '' ?>;border-left:3px solid <?= $activeTab === $tid ? 'var(--gyc-green-700)' : 'transparent' ?>;">
      <i data-lucide="<?= $icon ?>" style="width:15px;height:15px;flex-shrink:0;"></i>
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Forms -->
  <div style="display:flex;flex-direction:column;gap:1.5rem;">

    <?php if ($activeTab === 'general'): ?>
    <form method="POST" style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.75rem;">
      <input type="hidden" name="section" value="general">
      <h2 style="font-size:.95rem;font-weight:700;margin-bottom:1.5rem;">General Settings</h2>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">Site Name</label><input type="text" name="site_name" class="form-control" value="<?= $s('site_name','GYC Naturals') ?>"></div>
        <div class="form-group"><label class="form-label">Tagline</label><input type="text" name="site_tagline" class="form-control" value="<?= $s('site_tagline') ?>"></div>
        <div class="form-group"><label class="form-label">Site Email (from address)</label><input type="email" name="site_email" class="form-control" value="<?= $s('site_email','hello@gycnaturals.com') ?>"></div>
        <div class="form-group"><label class="form-label">Contact/Notification Email</label><input type="email" name="contact_email" class="form-control" value="<?= $s('contact_email') ?>"></div>
        <div class="form-group"><label class="form-label">Phone Number</label><input type="text" name="site_phone" class="form-control" value="<?= $s('site_phone') ?>" placeholder="+234 xxx xxx xxxx"></div>
        <div class="form-group"><label class="form-label">WhatsApp Number</label><input type="text" name="site_whatsapp" class="form-control" value="<?= $s('site_whatsapp') ?>" placeholder="+234 xxx xxx xxxx"><p class="form-hint">Include country code. Used for WhatsApp links throughout the site.</p></div>
      </div>
      <div class="form-group"><label class="form-label">Physical Address</label><input type="text" name="site_address" class="form-control" value="<?= $s('site_address','Big Qua Mall, Ediba Road, Calabar, Cross River State') ?>"></div>
      <div class="form-group"><label class="form-label">Opening Hours</label><input type="text" name="opening_hours" class="form-control" value="<?= $s('opening_hours','Mon–Sat: 8:00 AM – 7:00 PM | Sun: 10:00 AM – 5:00 PM') ?>"></div>
      <button type="submit" class="btn btn-green">Save General Settings</button>
    </form>

    <?php elseif ($activeTab === 'payment'): ?>
    <form method="POST" style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.75rem;">
      <input type="hidden" name="section" value="payment">
      <h2 style="font-size:.95rem;font-weight:700;margin-bottom:1.5rem;">Payment Settings</h2>
      <div style="background:#FEF9EC;border:1px solid #F59E0B;border-radius:8px;padding:.85rem 1rem;font-size:.82rem;color:#92400E;margin-bottom:1.5rem;">
        ⚠ Use test keys during development. Switch to live keys before going live.
      </div>
      <div class="form-group"><label class="form-label">Paystack Public Key</label><input type="text" name="paystack_public_key" class="form-control" value="<?= $s('paystack_public_key') ?>" placeholder="pk_test_…"></div>
      <div class="form-group">
        <label class="form-label">Paystack Secret Key</label>
        <input type="password" name="paystack_secret_key" class="form-control" value="<?= $s('paystack_secret_key') ?>" placeholder="sk_test_…">
        <p class="form-hint">Never share your secret key. It is stored encrypted in the database.</p>
      </div>
      <hr style="margin:1.5rem 0;border-color:#E5E7EB;">
      <h3 style="font-size:.88rem;font-weight:700;margin-bottom:1rem;">Bank Transfer Details</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" value="<?= $s('bank_name') ?>" placeholder="GTBank"></div>
        <div class="form-group"><label class="form-label">Account Name</label><input type="text" name="bank_account_name" class="form-control" value="<?= $s('bank_account_name') ?>" placeholder="GYC Naturals Ltd"></div>
        <div class="form-group"><label class="form-label">Account Number</label><input type="text" name="bank_account_number" class="form-control" value="<?= $s('bank_account_number') ?>"></div>
      </div>
      <button type="submit" class="btn btn-green">Save Payment Settings</button>
    </form>

    <?php elseif ($activeTab === 'shipping'): ?>
    <form method="POST" style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.75rem;">
      <input type="hidden" name="section" value="shipping">
      <h2 style="font-size:.95rem;font-weight:700;margin-bottom:1.5rem;">Shipping Settings</h2>
      <div class="form-group">
        <label class="form-label">Free Shipping Threshold (₦)</label>
        <input type="number" name="free_shipping_threshold" class="form-control" min="0"
               value="<?= $s('free_shipping_threshold','50000') ?>">
        <p class="form-hint">Orders at or above this amount get free shipping. Set to 0 to always charge.</p>
      </div>
      <div class="form-group">
        <label class="form-label">Default Shipping Fee (₦)</label>
        <input type="number" name="default_shipping_fee" class="form-control" min="0"
               value="<?= $s('default_shipping_fee','2500') ?>">
      </div>
      <button type="submit" class="btn btn-green">Save Shipping Settings</button>
    </form>

    <?php elseif ($activeTab === 'email'): ?>
    <form method="POST" style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.75rem;">
      <input type="hidden" name="section" value="email">
      <h2 style="font-size:.95rem;font-weight:700;margin-bottom:1.5rem;">Email / SMTP Settings</h2>
      <div style="background:var(--gyc-green-100);border-radius:8px;padding:.85rem 1rem;font-size:.82rem;color:var(--gyc-green-700);margin-bottom:1.5rem;">
        ℹ Resend API key takes priority. SMTP is used as fallback if Resend is not configured.
      </div>
      <div class="form-group"><label class="form-label">Resend API Key</label><input type="password" name="resend_api_key" class="form-control" value="<?= $s('resend_api_key') ?>" placeholder="re_…"></div>
      <div class="form-group"><label class="form-label">From Name</label><input type="text" name="from_name" class="form-control" value="<?= $s('from_name','GYC Naturals') ?>"></div>
      <div class="form-group"><label class="form-label">From Email</label><input type="email" name="from_email" class="form-control" value="<?= $s('from_email','hello@gycnaturals.com') ?>"></div>
      <hr style="margin:1.5rem 0;border-color:#E5E7EB;">
      <h3 style="font-size:.88rem;font-weight:700;margin-bottom:1rem;">SMTP Fallback</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="form-group"><label class="form-label">SMTP Host</label><input type="text" name="smtp_host" class="form-control" value="<?= $s('smtp_host') ?>" placeholder="smtp.gmail.com"></div>
        <div class="form-group"><label class="form-label">SMTP Port</label><input type="number" name="smtp_port" class="form-control" value="<?= $s('smtp_port','587') ?>"></div>
        <div class="form-group"><label class="form-label">SMTP Username</label><input type="text" name="smtp_user" class="form-control" value="<?= $s('smtp_user') ?>"></div>
        <div class="form-group"><label class="form-label">SMTP Password</label><input type="password" name="smtp_pass" class="form-control" value="<?= $s('smtp_pass') ?>"></div>
      </div>
      <button type="submit" class="btn btn-green">Save Email Settings</button>
    </form>

    <?php elseif ($activeTab === 'social'): ?>
    <form method="POST" style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.75rem;">
      <input type="hidden" name="section" value="social">
      <h2 style="font-size:.95rem;font-weight:700;margin-bottom:1.5rem;">Social Media Links</h2>
      <?php foreach ([
        'instagram_url' => ['instagram','Instagram','https://instagram.com/gycnaturals'],
        'facebook_url'  => ['facebook','Facebook','https://facebook.com/gycnaturals'],
        'tiktok_url'    => ['video','TikTok','https://tiktok.com/@gycnaturals'],
        'twitter_url'   => ['twitter','X / Twitter','https://x.com/gycnaturals'],
        'youtube_url'   => ['youtube','YouTube','https://youtube.com/@gycnaturals'],
      ] as $k => [$icon, $label, $placeholder]): ?>
      <div class="form-group">
        <label class="form-label" style="display:flex;align-items:center;gap:.4rem;">
          <i data-lucide="<?= $icon ?>" style="width:14px;height:14px;color:var(--gyc-green-600);"></i> <?= $label ?>
        </label>
        <input type="url" name="<?= $k ?>" class="form-control" value="<?= $s($k) ?>" placeholder="<?= $placeholder ?>">
      </div>
      <?php endforeach; ?>
      <button type="submit" class="btn btn-green">Save Social Links</button>
    </form>

    <?php elseif ($activeTab === 'security'): ?>
    <form method="POST" style="background:#fff;border:1.5px solid #E5E7EB;border-radius:12px;padding:1.75rem;">
      <input type="hidden" name="section" value="admin_password">
      <h2 style="font-size:.95rem;font-weight:700;margin-bottom:1.5rem;">Change Admin Password</h2>
      <div class="form-group"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required placeholder="Your current password"></div>
      <div class="form-group"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required minlength="8" placeholder="At least 8 characters"></div>
      <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required placeholder="Repeat new password"></div>
      <button type="submit" class="btn btn-green">Update Password</button>
    </form>
    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
