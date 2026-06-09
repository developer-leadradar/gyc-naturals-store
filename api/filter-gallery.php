<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');

$filters = [];
if (!empty($_GET['category']))   $filters['category_slug'] = sanitize($_GET['category']);
if (!empty($_GET['style_type'])) $filters['style_type']    = sanitize($_GET['style_type']);
if (!empty($_GET['featured']))   $filters['featured']      = true;

$limit  = min(24, max(6, (int)($_GET['limit'] ?? 12)));
$offset = max(0, (int)($_GET['offset'] ?? 0));

$images = getGalleryImages($filters, $limit, $offset);
$total  = countGalleryImages($filters);

$output = array_map(function($img) {
    return [
        'id'         => $img['id'],
        'title'      => $img['title'],
        'slug'       => $img['slug'],
        'image_url'  => $img['image_url'],
        'price_from' => $img['price_from'],
        'price_label'=> $img['price_from'] ? 'from ' . formatPrice($img['price_from']) : '',
        'category'   => $img['category_name'] ?? '',
        'cat_slug'   => $img['category_slug'] ?? '',
        'style_type' => $img['style_type'],
        'is_featured'=> (bool)$img['is_featured'],
        'detail_url' => SITE_URL . '/style-detail.php?slug=' . urlencode($img['slug']),
        'book_url'   => SITE_URL . '/book-appointment.php?style_id=' . $img['id'],
    ];
}, $images);

jsonResponse([
    'success' => true,
    'images'  => $output,
    'total'   => (int)$total,
    'limit'   => $limit,
    'offset'  => $offset,
    'has_more'=> ($offset + $limit) < $total,
]);
