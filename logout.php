<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Clear remember token
if (isLoggedIn()) {
    getDB()->update('users', ['remember_token' => null], 'id = ?', [$_SESSION['user_id']]);
}
setcookie('remember_token', '', time() - 3600, '/', '', false, true);
session_destroy();
redirect(SITE_URL . '/');
