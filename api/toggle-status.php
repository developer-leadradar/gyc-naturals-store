<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false], 405);
}

$id    = (int)($_POST['id']    ?? 0);
$table = preg_replace('/[^a-z_]/', '', strtolower($_POST['table'] ?? ''));
$field = preg_replace('/[^a-z_]/', '', strtolower($_POST['field'] ?? 'is_active'));
$value = (int)($_POST['value'] ?? 0);

// Allowlist of tables/fields that can be toggled
$allowedTables = ['products','gallery_images','blog_posts','testimonials','bundles','gallery_categories','categories'];
$allowedFields = ['is_active','is_featured'];

if (!$id || !in_array($table, $allowedTables) || !in_array($field, $allowedFields)) {
    jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
}

$db     = getDB();
$result = $db->query("UPDATE `$table` SET `$field` = ? WHERE id = ?", [$value, $id]);

if ($result) {
    jsonResponse(['success' => true, 'id' => $id, 'table' => $table, 'field' => $field, 'value' => $value]);
} else {
    jsonResponse(['success' => false, 'message' => 'Update failed'], 500);
}
