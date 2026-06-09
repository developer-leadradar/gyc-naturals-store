<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
logout();
redirect(SITE_URL . '/admin/login.php');
