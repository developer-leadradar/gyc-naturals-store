<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Clear remember token from DB
$p   = _getAuthPayload();
$uid = $p['uid'] ?? ($_SESSION['user_id'] ?? null);
if ($uid) getDB()->update('users', ['remember_token' => null], 'id = ?', [$uid]);

// logout() clears gyc_auth cookie, gyc_csrf, and destroys session, then redirects
logout();
