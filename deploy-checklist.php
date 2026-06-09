<?php
/**
 * GYC Naturals — Pre-Launch Deployment Checklist
 * Access: http://localhost/gyc-store/deploy-checklist.php
 * ⚠ DELETE THIS FILE BEFORE GOING LIVE — it exposes sensitive config info.
 */
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Only accessible from localhost or with a secret token
$token = $_GET['token'] ?? '';
$isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']);
$secretToken = 'gyc-deploy-2025';  // Change this before using

if (!$isLocal && $token !== $secretToken) {
    http_response_code(403);
    die('<h1>403 — Access Denied</h1><p>This page is only accessible locally or with a valid token.</p>');
}

// ─────────────────────────────────────────────────────────
// Run checks
// ─────────────────────────────────────────────────────────

$checks = [];

function addCheck(string $section, string $label, bool $pass, string $detail = '', string $fix = ''): void {
    global $checks;
    $checks[] = compact('section', 'label', 'pass', 'detail', 'fix');
}

// ── PHP Version ──
addCheck('Server', 'PHP ≥ 8.1', PHP_VERSION_ID >= 80100,
    'PHP ' . PHP_VERSION,
    'Upgrade PHP in XAMPP/cPanel to 8.1 or higher.');

// ── Required PHP Extensions ──
foreach (['pdo', 'pdo_mysql', 'mbstring', 'json', 'curl', 'gd', 'openssl', 'intl'] as $ext) {
    addCheck('Server', "PHP ext: {$ext}", extension_loaded($ext),
        extension_loaded($ext) ? 'Loaded ✓' : 'NOT loaded',
        "Enable extension={$ext} in php.ini");
}

// ── Database connection ──
try {
    $db = getDB();
    $db->fetchOne("SELECT 1");
    addCheck('Database', 'DB connection', true, 'Connected to ' . DB_NAME . ' on ' . DB_HOST);
} catch (Exception $e) {
    addCheck('Database', 'DB connection', false, $e->getMessage(), 'Check DB_HOST, DB_NAME, DB_USER, DB_PASS in config.php / .env');
}

// ── Required tables ──
$requiredTables = [
    'users','products','product_categories','gallery_images','gallery_categories',
    'appointments','booking_slots','orders','order_items','cart_items',
    'testimonials','blog_posts','bundles','bundle_items','site_settings',
    'contact_messages','wishlist','quiz_results','waitlist',
];
try {
    $existingTables = array_column(getDB()->fetchAll("SHOW TABLES"), 'Tables_in_' . DB_NAME);
    foreach ($requiredTables as $tbl) {
        $exists = in_array($tbl, $existingTables);
        addCheck('Database', "Table: {$tbl}", $exists,
            $exists ? 'Exists ✓' : 'MISSING',
            'Run install.php to create missing tables.');
    }
} catch (Exception $e) {
    addCheck('Database', 'Tables check', false, $e->getMessage());
}

// ── Site settings seeded ──
try {
    $settingCount = (int)(getDB()->fetchOne("SELECT COUNT(*) c FROM site_settings")['c'] ?? 0);
    addCheck('Database', 'Site settings seeded', $settingCount >= 10,
        "{$settingCount} settings found",
        'Run seed.php to populate settings.');
} catch (Exception $e) {
    addCheck('Database', 'Site settings seeded', false, $e->getMessage());
}

// ── Products seeded ──
try {
    $prodCount = (int)(getDB()->fetchOne("SELECT COUNT(*) c FROM products")['c'] ?? 0);
    addCheck('Database', 'Products seeded', $prodCount > 0,
        "{$prodCount} products in database",
        'Run seed.php to add demo products.');
} catch (Exception $e) {
    addCheck('Database', 'Products seeded', false, $e->getMessage());
}

// ── Config: SITE_URL ──
$siteUrlOk = !str_contains(SITE_URL, 'localhost') && str_starts_with(SITE_URL, 'https://');
addCheck('Configuration', 'SITE_URL uses HTTPS (production)',
    $siteUrlOk,
    SITE_URL,
    'Set SITE_URL=https://yourdomain.com in .env before deploying.');

// ── Config: Paystack keys ──
$hasPsPublic = !empty(PAYSTACK_PUBLIC_KEY) && PAYSTACK_PUBLIC_KEY !== '';
$hasPsSecret = !empty(PAYSTACK_SECRET_KEY) && PAYSTACK_SECRET_KEY !== '';
$psIsLive     = str_starts_with(PAYSTACK_PUBLIC_KEY, 'pk_live_');
addCheck('Configuration', 'Paystack public key set', $hasPsPublic,
    $hasPsPublic ? (str_starts_with(PAYSTACK_PUBLIC_KEY, 'pk_test_') ? '⚠ TEST key — replace with live before launch' : substr(PAYSTACK_PUBLIC_KEY, 0, 12) . '…') : 'NOT SET',
    'Set PAYSTACK_PUBLIC_KEY in .env');
addCheck('Configuration', 'Paystack secret key set', $hasPsSecret,
    $hasPsSecret ? 'Set (' . (str_starts_with(PAYSTACK_SECRET_KEY, 'sk_test_') ? '⚠ TEST key' : 'Live ✓') . ')' : 'NOT SET',
    'Set PAYSTACK_SECRET_KEY in .env');

// ── Config: Email ──
$hasResend = !empty(RESEND_API_KEY);
$hasSmtp   = !empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD);
addCheck('Configuration', 'Email configured (Resend or SMTP)',
    $hasResend || $hasSmtp,
    $hasResend ? 'Resend API key set ✓' : ($hasSmtp ? 'SMTP configured ✓' : 'Neither Resend nor SMTP set'),
    'Set RESEND_API_KEY in .env, or set SMTP_HOST/USERNAME/PASSWORD.');

// ── Config: Admin email ──
addCheck('Configuration', 'Admin email set',
    defined('ADMIN_EMAIL') && ADMIN_EMAIL !== 'admin@gycnaturals.com',
    defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'Not defined',
    'Set ADMIN_EMAIL in .env to receive contact form and order notifications.');

// ── Directories writable ──
$writableDirs = ['uploads', 'uploads/products', 'uploads/gallery', 'uploads/blog', 'uploads/avatars'];
foreach ($writableDirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    $exists   = is_dir($fullPath);
    $writable = $exists && is_writable($fullPath);
    if (!$exists) {
        addCheck('Files', "Dir: /{$dir}", false, 'Directory does not exist', "mkdir -p {$fullPath} && chmod 755 {$fullPath}");
    } else {
        addCheck('Files', "Dir: /{$dir} writable", $writable,
            $writable ? 'Writable ✓' : 'NOT writable',
            "chmod 755 {$fullPath}");
    }
}

// ── PWA icons exist ──
$iconSizes = [72, 96, 128, 192, 384, 512];
foreach ($iconSizes as $sz) {
    $iconPath = __DIR__ . "/assets/images/icon-{$sz}.png";
    addCheck('Assets', "PWA icon {$sz}×{$sz}", file_exists($iconPath),
        file_exists($iconPath) ? 'Exists ✓' : 'MISSING',
        'Run generate-icons.php to create missing icon sizes.');
}

// ── Service worker ──
addCheck('Assets', 'service-worker.js exists', file_exists(__DIR__ . '/service-worker.js'), '', 'Check that service-worker.js was not accidentally deleted.');
addCheck('Assets', 'manifest.json exists',     file_exists(__DIR__ . '/manifest.json'),     '', 'Check that manifest.json exists.');

// ── Security: dangerous files ──
$dangerousFiles = ['install.php', 'seed.php', 'generate-icons.php', 'deploy-checklist.php'];
foreach ($dangerousFiles as $f) {
    $exists = file_exists(__DIR__ . '/' . $f);
    // For this file itself, it's expected to exist now — warn instead of fail
    if ($f === 'deploy-checklist.php') {
        addCheck('Security', "Delete {$f} before going live", true,
            '⚠ This file (deploy-checklist.php) must be deleted before launch.', '');
    } else {
        addCheck('Security', "{$f} deleted", !$exists,
            $exists ? '⚠ FILE EXISTS — DELETE BEFORE LAUNCH' : 'Deleted ✓',
            "Delete {$f} from server: rm /path/to/{$f}");
    }
}

// ── Security: .env not accessible ──
addCheck('Security', '.env file present (for config)',
    file_exists(__DIR__ . '/.env'),
    file_exists(__DIR__ . '/.env') ? '.env found ✓' : 'Not found — using config.php defaults',
    'Create a .env file with production credentials (see .env.example).');

// ── HTTPS ──
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
addCheck('Security', 'HTTPS active', $isHttps || $isLocal,
    $isLocal ? 'Localhost — HTTPS not required here' : ($isHttps ? 'HTTPS ✓' : 'HTTP only — insecure!'),
    'Install an SSL certificate (Let\'s Encrypt is free) and force HTTPS in .htaccess.');

// ── .htaccess ──
addCheck('Security', '.htaccess present', file_exists(__DIR__ . '/.htaccess'),
    '', 'Restore .htaccess from the repository.');

// ── Error display off in production ──
$displayErrors = ini_get('display_errors');
addCheck('Security', 'display_errors OFF (production)',
    $displayErrors == '0' || $isLocal,
    $isLocal ? 'Localhost — display_errors allowed' : ($displayErrors == '0' ? 'Off ✓' : 'ON — fix for production!'),
    'Set display_errors = Off in php.ini or .htaccess: php_flag display_errors Off');

// ── Admin account ──
try {
    $adminUser = getDB()->fetchOne("SELECT id, email FROM users WHERE role='admin' LIMIT 1");
    addCheck('Admin', 'Admin user exists', (bool)$adminUser,
        $adminUser ? 'Admin: ' . $adminUser['email'] : 'No admin user found',
        'Run seed.php or manually INSERT into users with role=admin.');
} catch (Exception $e) {
    addCheck('Admin', 'Admin user exists', false, $e->getMessage());
}

// ─────────────────────────────────────────────────────────
// Tally
// ─────────────────────────────────────────────────────────
$total   = count($checks);
$passed  = count(array_filter($checks, fn($c) => $c['pass']));
$failed  = $total - $passed;
$pct     = $total > 0 ? round(($passed / $total) * 100) : 0;
$readyColor = $pct >= 90 ? '#16a34a' : ($pct >= 70 ? '#ca8a04' : '#dc2626');

// Group by section
$grouped = [];
foreach ($checks as $c) {
    $grouped[$c['section']][] = $c;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GYC Naturals — Deployment Checklist</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7f5; color: #1a1a1a; font-size: 15px; }
  .wrap { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }
  /* Header */
  .page-header { background: #14532d; color: #fff; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
  .page-header h1 { font-size: 1.5rem; font-weight: 700; }
  .page-header p { font-size: .85rem; opacity: .75; margin-top: .3rem; }
  /* Score ring */
  .score { text-align: center; }
  .score-num { font-size: 2.5rem; font-weight: 800; color: <?= $readyColor ?>; line-height: 1; }
  .score-label { font-size: .75rem; opacity: .7; margin-top: .25rem; }
  /* Summary bar */
  .summary { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
  .summary-card { flex: 1; min-width: 140px; background: #fff; border-radius: 10px; padding: 1rem 1.25rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); text-align: center; }
  .summary-card .num { font-size: 1.8rem; font-weight: 800; }
  .summary-card .lbl { font-size: .78rem; color: #6b7280; margin-top: .2rem; }
  /* Progress bar */
  .progress-wrap { background: #e5e7eb; border-radius: 99px; height: 8px; margin-bottom: 2rem; overflow: hidden; }
  .progress-fill { height: 100%; border-radius: 99px; background: <?= $readyColor ?>; width: <?= $pct ?>%; transition: width .6s; }
  /* Section */
  .section-block { background: #fff; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,.06); margin-bottom: 1.5rem; overflow: hidden; }
  .section-title { padding: .85rem 1.25rem; font-weight: 700; font-size: .88rem; letter-spacing: .04em; text-transform: uppercase; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: .6rem; }
  .section-pass { color: #166534; background: #f0fdf4; }
  .section-warn { color: #92400e; background: #fffbeb; }
  .section-fail { color: #991b1b; background: #fef2f2; }
  /* Check row */
  .check-row { display: flex; align-items: flex-start; gap: .85rem; padding: .7rem 1.25rem; border-bottom: 1px solid #f9fafb; }
  .check-row:last-child { border-bottom: none; }
  .check-icon { flex-shrink: 0; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .75rem; margin-top: 1px; }
  .icon-pass { background: #dcfce7; color: #15803d; }
  .icon-fail { background: #fee2e2; color: #dc2626; }
  .check-label { font-weight: 600; font-size: .875rem; color: #111; }
  .check-detail { font-size: .78rem; color: #6b7280; margin-top: .2rem; }
  .check-fix { font-size: .75rem; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; border-radius: 4px; padding: .3rem .6rem; margin-top: .4rem; display: inline-block; }
  /* Notice */
  .danger-notice { background: #fef2f2; border: 2px solid #fca5a5; border-radius: 10px; padding: 1rem 1.25rem; margin-bottom: 2rem; color: #991b1b; font-size: .875rem; line-height: 1.6; }
  .danger-notice strong { display: block; margin-bottom: .4rem; font-size: 1rem; }
  /* Footer */
  .page-footer { margin-top: 2.5rem; text-align: center; font-size: .78rem; color: #9ca3af; }
</style>
</head>
<body>
<div class="wrap">

  <div class="danger-notice">
    <strong>⚠️ Security Warning</strong>
    This file (<code>deploy-checklist.php</code>) exposes server configuration details. <strong>Delete it before going live.</strong>
    Do not share this URL publicly.
  </div>

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1>🚀 GYC Naturals — Deployment Checklist</h1>
      <p>Pre-launch verification for production deployment · <?= date('D, jS F Y \a\t H:i') ?></p>
    </div>
    <div class="score">
      <div class="score-num"><?= $pct ?>%</div>
      <div class="score-label"><?= $passed ?>/<?= $total ?> checks passed</div>
    </div>
  </div>

  <!-- Progress -->
  <div class="progress-wrap"><div class="progress-fill"></div></div>

  <!-- Summary -->
  <div class="summary">
    <div class="summary-card">
      <div class="num" style="color:#15803d;"><?= $passed ?></div>
      <div class="lbl">Passed</div>
    </div>
    <div class="summary-card">
      <div class="num" style="color:<?= $failed > 0 ? '#dc2626' : '#15803d' ?>;"><?= $failed ?></div>
      <div class="lbl">Failed</div>
    </div>
    <div class="summary-card">
      <div class="num"><?= $total ?></div>
      <div class="lbl">Total Checks</div>
    </div>
    <div class="summary-card">
      <div class="num" style="color:<?= $readyColor ?>;"><?= $pct >= 90 ? '✅' : ($pct >= 70 ? '⚠️' : '❌') ?></div>
      <div class="lbl"><?= $pct >= 90 ? 'Launch Ready' : ($pct >= 70 ? 'Nearly Ready' : 'Not Ready') ?></div>
    </div>
  </div>

  <!-- Grouped checks -->
  <?php foreach ($grouped as $sectionName => $sectionChecks):
    $sPass  = count(array_filter($sectionChecks, fn($c) => $c['pass']));
    $sTotal = count($sectionChecks);
    $allOk  = $sPass === $sTotal;
    $hasWarn = !$allOk && $sPass > 0;
    $cls    = $allOk ? 'section-pass' : ($hasWarn ? 'section-warn' : 'section-fail');
    $icon   = $allOk ? '✅' : ($hasWarn ? '⚠️' : '❌');
  ?>
  <div class="section-block">
    <div class="section-title <?= $cls ?>">
      <?= $icon ?> <?= htmlspecialchars($sectionName) ?>
      <span style="margin-left:auto;font-weight:400;opacity:.7;"><?= $sPass ?>/<?= $sTotal ?></span>
    </div>
    <?php foreach ($sectionChecks as $chk): ?>
    <div class="check-row">
      <div class="check-icon <?= $chk['pass'] ? 'icon-pass' : 'icon-fail' ?>">
        <?= $chk['pass'] ? '✓' : '✗' ?>
      </div>
      <div style="flex:1;min-width:0;">
        <div class="check-label"><?= htmlspecialchars($chk['label']) ?></div>
        <?php if ($chk['detail']): ?>
        <div class="check-detail"><?= htmlspecialchars($chk['detail']) ?></div>
        <?php endif; ?>
        <?php if (!$chk['pass'] && $chk['fix']): ?>
        <div class="check-fix">🔧 Fix: <?= htmlspecialchars($chk['fix']) ?></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <!-- Quick-start checklist for going live -->
  <div class="section-block" style="margin-top:2rem;">
    <div class="section-title" style="background:#eff6ff;color:#1e40af;">📋 Manual Go-Live Checklist</div>
    <?php
    $manual = [
      ['Replace Paystack TEST keys with LIVE keys in .env'],
      ['Replace Unsplash image URLs with real GYC Naturals photography'],
      ['Update SITE_URL in .env to your production domain (https://)'],
      ['Set ADMIN_EMAIL in .env to receive real order/contact notifications'],
      ['Configure Resend API key (or SMTP) in .env for email delivery'],
      ['Set up a daily database backup (cron: mysqldump)'],
      ['Enable SSL certificate — use Let\'s Encrypt (free)'],
      ['Force HTTPS in .htaccess — uncomment the RewriteRule redirect'],
      ['Delete install.php, seed.php, generate-icons.php, deploy-checklist.php from server'],
      ['Set session.cookie_secure = On in php.ini (requires HTTPS)'],
      ['Test a complete purchase flow end-to-end with Paystack test mode first'],
      ['Test appointment booking → confirmation email delivery'],
      ['Test contact form → admin notification + customer acknowledgement'],
      ['Verify WhatsApp number is correct in site_settings (admin → Settings)'],
      ['Submit sitemap.php URL to Google Search Console'],
      ['Verify robots.txt is accessible at /gyc-store/robots.txt'],
      ['Load test or run Lighthouse audit — aim for 90+ Performance score'],
      ['Set up uptime monitoring (UptimeRobot, Better Uptime — free tiers available)'],
    ];
    foreach ($manual as $i => $item):
    ?>
    <div class="check-row" style="gap:.6rem;">
      <input type="checkbox" id="m<?= $i ?>" style="width:16px;height:16px;flex-shrink:0;margin-top:2px;cursor:pointer;" onchange="saveChecked()">
      <label for="m<?= $i ?>" style="font-size:.875rem;cursor:pointer;line-height:1.5;"><?= htmlspecialchars($item[0]) ?></label>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- .env template -->
  <div class="section-block" style="margin-top:2rem;">
    <div class="section-title" style="background:#f8fafc;color:#374151;">📄 .env Template</div>
    <div style="padding:1.25rem;">
      <p style="font-size:.82rem;color:#6b7280;margin-bottom:.75rem;">Copy this to <code>.env</code> in the project root and fill in your production values:</p>
      <pre style="background:#1a1a1a;color:#d4edda;padding:1.25rem;border-radius:8px;font-size:.78rem;line-height:1.7;overflow-x:auto;"># ── GYC Naturals — Production Environment ──
# Copy to .env and fill in real values. DO NOT commit to git.

# Site
SITE_URL=https://gycnaturals.com
SITE_NAME=GYC Naturals
SITE_EMAIL=info@gycnaturals.com
ADMIN_EMAIL=admin@gycnaturals.com
SITE_PHONE=+234 xxx xxx xxxx
SITE_WHATSAPP=+2348xxxxxxxxx

# Database (cPanel / shared hosting)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_db_name
DB_USER=your_db_user
DB_PASS=your_strong_db_password

# Paystack (replace with your live keys from dashboard.paystack.com)
PAYSTACK_PUBLIC_KEY=<YOUR_PAYSTACK_PUBLIC_KEY>
PAYSTACK_SECRET_KEY=<YOUR_PAYSTACK_SECRET_KEY>

# Email — Resend (preferred)
RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Email — SMTP fallback
SMTP_FROM_EMAIL=info@gycnaturals.com
SMTP_FROM_NAME=GYC Naturals
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_ENCRYPTION=tls
SMTP_USERNAME=your@gmail.com
SMTP_PASSWORD=your_app_password</pre>
    </div>
  </div>

  <div class="page-footer">
    GYC Naturals · Deployment Checklist · Generated <?= date('Y-m-d H:i:s') ?><br>
    <strong style="color:#dc2626;">⚠ Delete this file before going live: deploy-checklist.php</strong>
  </div>

</div>
<script>
// Persist manual checklist state in localStorage
function saveChecked() {
  var state = {};
  document.querySelectorAll('input[type=checkbox]').forEach(function(cb) {
    state[cb.id] = cb.checked;
  });
  localStorage.setItem('gyc_deploy_checks', JSON.stringify(state));
}
(function() {
  var saved = JSON.parse(localStorage.getItem('gyc_deploy_checks') || '{}');
  Object.entries(saved).forEach(function([id, val]) {
    var el = document.getElementById(id);
    if (el) el.checked = val;
  });
})();
</script>
</body>
</html>
