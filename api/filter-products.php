<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$category   = sanitize($_GET['category']   ?? '');
$concern    = sanitize($_GET['concern']    ?? '');
$hair_type  = sanitize($_GET['hair_type']  ?? '');
$sort       = sanitize($_GET['sort']       ?? '');
$min_price  = (int)($_GET['min_price']     ?? 0);
$max_price  = (int)($_GET['max_price']     ?? 0);
$search     = sanitize($_GET['q']          ?? '');
$limit      = min(48, max(1, (int)($_GET['limit']  ?? 12)));
$offset     = max(0, (int)($_GET['offset'] ?? 0));

$filters = [];
if ($concern)   $filters['concern']      = $concern;
if ($hair_type) $filters['hair_type']    = $hair_type;
if ($min_price) $filters['min_price']    = $min_price;
if ($max_price) $filters['max_price']    = $max_price;
if ($search)    $filters['search']       = $search;
if ($sort)      $filters['sort']         = $sort;

// Resolve category slug to ID
if ($category) {
    $cats = getAllCategories();
    foreach ($cats as $cat) {
        if ($cat['slug'] === $category) {
            $filters['category_id'] = $cat['id'];
            break;
        }
    }
}

$total    = countProducts($filters);
$products = getAllProducts($filters, $limit, $offset);

// Format for JSON
$output = array_map(function ($p) {
    return [
        'id'           => $p['id'],
        'name'         => $p['name'],
        'slug'         => $p['slug'],
        'price'        => $p['price'],
        'price_fmt'    => formatPrice($p['price']),
        'image'        => $p['image'],
        'category'     => $p['category_name'],
        'concern'      => $p['concern'],
        'hair_type'    => $p['hair_type'],
        'is_featured'  => (bool)$p['is_featured'],
        'rating'       => (float)$p['rating'],
        'review_count' => (int)$p['review_count'],
        'stock'        => (int)$p['stock_quantity'],
        'url'          => SITE_URL . '/product.php?slug=' . urlencode($p['slug']),
    ];
}, $products);

echo json_encode([
    'success'  => true,
    'products' => $output,
    'total'    => $total,
    'limit'    => $limit,
    'offset'   => $offset,
    'has_more' => ($offset + $limit) < $total,
]);
