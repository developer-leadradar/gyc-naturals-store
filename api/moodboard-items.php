<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

$slugsParam = trim($_GET['slugs'] ?? '');
if (!$slugsParam) {
    jsonResponse([]);
}

$slugs  = array_filter(array_map('trim', explode(',', $slugsParam)));
$slugs  = array_slice($slugs, 0, 20); // cap at 20

if (empty($slugs)) {
    jsonResponse([]);
}

$db          = getDB();
$placeholders = implode(',', array_fill(0, count($slugs), '?'));
$items = $db->fetchAll(
    "SELECT id, title, slug, image_url, price_from, price_to, style_type
     FROM gallery_images
     WHERE slug IN ($placeholders) AND is_active = 1
     ORDER BY display_order ASC",
    $slugs
);

// Preserve requested order and add formatted price
$ordered = [];
foreach ($slugs as $slug) {
    foreach ($items as $item) {
        if ($item['slug'] === $slug) {
            $item['price_from_fmt'] = $item['price_from'] ? formatPrice($item['price_from']) : null;
            $ordered[] = $item;
            break;
        }
    }
}

jsonResponse(['success' => true, 'items' => $ordered]);
