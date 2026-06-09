<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

$q = sanitize($_GET['q'] ?? '');
if (strlen($q) < 2) {
    jsonResponse([]);
}

$db      = getDB();
$search  = '%' . $q . '%';
$results = [];

// Search products
$products = $db->fetchAll(
    "SELECT id, name, slug, price, image, 'product' as type FROM products
     WHERE is_active=1 AND (name LIKE ? OR key_ingredient LIKE ?) LIMIT 5",
    [$search, $search]
);
foreach ($products as $p) {
    $results[] = [
        'type'  => 'product',
        'id'    => $p['id'],
        'title' => $p['name'],
        'price' => formatPrice($p['price']),
        'image' => $p['image'],
        'url'   => SITE_URL . '/product.php?slug=' . urlencode($p['slug']),
    ];
}

// Search gallery styles
$styles = $db->fetchAll(
    "SELECT id, title, slug, price_from, image_url, 'style' as type FROM gallery_images
     WHERE is_active=1 AND title LIKE ? LIMIT 4",
    [$search]
);
foreach ($styles as $s) {
    $results[] = [
        'type'  => 'style',
        'id'    => $s['id'],
        'title' => $s['title'],
        'price' => $s['price_from'] ? 'from ' . formatPrice($s['price_from']) : '',
        'image' => $s['image_url'],
        'url'   => SITE_URL . '/style-detail.php?slug=' . urlencode($s['slug']),
    ];
}

// Search blog posts
$posts = $db->fetchAll(
    "SELECT id, title, slug, 'blog' as type FROM blog_posts
     WHERE status='published' AND (title LIKE ? OR excerpt LIKE ?) LIMIT 3",
    [$search, $search]
);
foreach ($posts as $p) {
    $results[] = [
        'type'  => 'blog',
        'id'    => $p['id'],
        'title' => $p['title'],
        'price' => '',
        'image' => '',
        'url'   => SITE_URL . '/blog-post.php?slug=' . urlencode($p['slug']),
    ];
}

jsonResponse(array_slice($results, 0, 8));
