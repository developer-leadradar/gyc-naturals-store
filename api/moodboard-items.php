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

// Fallback images by style_type when image_url is empty
$fallbacks = [
    'braiding'  => 'https://images.pexels.com/photos/8514938/pexels-photo-8514938.jpeg?auto=compress&cs=tinysrgb&w=400&h=500&fit=crop',
    'natural'   => 'https://images.pexels.com/photos/14792192/pexels-photo-14792192.jpeg?auto=compress&cs=tinysrgb&w=400&h=500&fit=crop',
    'kids'      => 'https://images.pexels.com/photos/34191088/pexels-photo-34191088.jpeg?auto=compress&cs=tinysrgb&w=400&h=500&fit=crop',
    'treatment' => 'https://images.pexels.com/photos/5722771/pexels-photo-5722771.jpeg?auto=compress&cs=tinysrgb&w=400&h=500&fit=crop',
];
$defaultFallback = 'https://images.pexels.com/photos/8514938/pexels-photo-8514938.jpeg?auto=compress&cs=tinysrgb&w=400&h=500&fit=crop';

// Preserve requested order and add formatted price
$ordered = [];
foreach ($slugs as $slug) {
    foreach ($items as $item) {
        if ($item['slug'] === $slug) {
            $item['price_from_fmt'] = $item['price_from'] ? formatPrice($item['price_from']) : null;
            if (empty($item['image_url'])) {
                $item['image_url'] = $fallbacks[$item['style_type'] ?? ''] ?? $defaultFallback;
            }
            $ordered[] = $item;
            break;
        }
    }
}

jsonResponse(['success' => true, 'items' => $ordered]);
