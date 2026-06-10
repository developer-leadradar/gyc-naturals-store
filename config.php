<?php
if (!defined('GYC_ACCESS')) { define('GYC_ACCESS', true); }

function _loadEnv($path) {
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($k, $_ENV) && !getenv($k)) {
            $_ENV[$k] = $v;
            putenv("$k=$v");
        }
    }
}
_loadEnv(__DIR__ . '/.env');

// Database
define('DB_HOST',   getenv('DB_HOST')   ?: 'localhost');
define('DB_PORT',   getenv('DB_PORT')   ?: '3306');
define('DB_NAME',   getenv('DB_NAME')   ?: 'gyc_store');
define('DB_USER',   getenv('DB_USER')   ?: 'root');
define('DB_PASS',   getenv('DB_PASS')   ?: '');
define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql');

// Site
define('SITE_NAME',     getenv('SITE_NAME')     ?: 'GYC Naturals');
define('SITE_URL',      rtrim(getenv('SITE_URL') ?: 'http://localhost/gyc-store', '/'));
define('SITE_EMAIL',    getenv('SITE_EMAIL')    ?: 'info@gycnaturals.com');
define('ADMIN_EMAIL',   getenv('ADMIN_EMAIL')   ?: 'admin@gycnaturals.com');
define('SITE_PHONE',    getenv('SITE_PHONE')    ?: '+234 800 000 0000');
define('SITE_WHATSAPP', getenv('SITE_WHATSAPP') ?: '+2348000000000');

// Paystack
define('PAYSTACK_PUBLIC_KEY',   getenv('PAYSTACK_PUBLIC_KEY')   ?: '');
define('PAYSTACK_SECRET_KEY',   getenv('PAYSTACK_SECRET_KEY')   ?: '');
define('PAYSTACK_CALLBACK_URL', getenv('PAYSTACK_CALLBACK_URL') ?: SITE_URL . '/api/paystack-verify.php');

// Email
define('RESEND_API_KEY',  getenv('RESEND_API_KEY')  ?: '');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'info@gycnaturals.com');
define('SMTP_FROM_NAME',  getenv('SMTP_FROM_NAME')  ?: 'GYC Naturals');
define('SMTP_HOST',       getenv('SMTP_HOST')       ?: 'smtp.gmail.com');
define('SMTP_PORT',       (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'tls');
define('SMTP_USERNAME',   getenv('SMTP_USERNAME')   ?: '');
define('SMTP_PASSWORD',   getenv('SMTP_PASSWORD')   ?: '');

// Supabase
define('SUPABASE_URL',         getenv('SUPABASE_URL')         ?: '');
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY') ?: '');
define('SUPABASE_BUCKET',      getenv('SUPABASE_BUCKET')      ?: 'gyc-gallery');

// Uploads
define('UPLOAD_PATH', getenv('UPLOAD_PATH') ?: __DIR__ . '/uploads/');
define('UPLOAD_URL',  getenv('UPLOAD_URL')  ?: SITE_URL . '/uploads/');

// DB proxy (Node.js ↔ PHP bridge on Vercel)
define('DB_PROXY_SECRET', getenv('DB_PROXY_SECRET') ?: '');

// Security
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12);

// Error reporting
$isProduction = !empty(getenv('VERCEL')) || !empty(getenv('VERCEL_ENV'));
error_reporting($isProduction ? 0 : E_ALL);
ini_set('display_errors', $isProduction ? '0' : '1');

date_default_timezone_set('Africa/Lagos');

// Session — DB-backed on Vercel (sessions persist across ephemeral containers);
// file-based locally where containers are not an issue.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path'     => '/',
        'secure'   => $isProduction,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    if ($isProduction && function_exists('curl_init')) {
        require_once __DIR__ . '/includes/db-session-handler.php';
        session_set_save_handler(new DbSessionHandler(), true);
    }
    session_start();
}
