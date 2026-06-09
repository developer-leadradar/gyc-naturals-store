<?php
define('GYC_ACCESS', true);
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to leave a review.']);
    exit;
}

if (!verifyCsrfSilent()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$rating    = (int)($_POST['rating']     ?? 0);
$title     = trim(sanitize($_POST['title'] ?? ''));
$body      = trim(sanitize($_POST['body']  ?? ''));

if (!$productId || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Please select a star rating.']);
    exit;
}
if (empty($body) || strlen($body) < 10) {
    echo json_encode(['success' => false, 'message' => 'Review text must be at least 10 characters.']);
    exit;
}

$db = getDB();

// Prevent duplicate review from same user for same product
$existing = $db->fetchOne(
    "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?",
    [$productId, $_SESSION['user_id']]
);
if ($existing) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product.']);
    exit;
}

// Check product exists
$product = getProductById($productId);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit;
}

$id = $db->insert('reviews', [
    'product_id'  => $productId,
    'user_id'     => $_SESSION['user_id'],
    'rating'      => $rating,
    'title'       => $title ?: null,
    'body'        => $body,
    'is_approved' => 0, // Requires admin approval
]);

if ($id) {
    echo json_encode(['success' => true, 'message' => 'Review submitted! It will appear after approval.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not save review. Please try again.']);
}
