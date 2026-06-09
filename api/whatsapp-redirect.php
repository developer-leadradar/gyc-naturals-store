<?php
/**
 * GYC Naturals — WhatsApp Redirect Helper
 *
 * Usage: /api/whatsapp-redirect.php?page=services
 * Builds a wa.me pre-filled link with context-specific message
 * and redirects the user. Admin MUST click "Send" in WhatsApp.
 *
 * Query params:
 *   page     — context identifier (services|gallery|contact|booking|shop|general)
 *   style    — style name for gallery context
 *   product  — product name for shop context
 *   ref      — booking/order ref
 */
define('GYC_ACCESS', true);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$page    = sanitize($_GET['page']    ?? 'general');
$style   = sanitize($_GET['style']   ?? '');
$product = sanitize($_GET['product'] ?? '');
$ref     = sanitize($_GET['ref']     ?? '');

$phone   = getSetting('site_whatsapp') ?: SITE_WHATSAPP;

$messages = [
    'services' => "Hi GYC Naturals! 🌿 I'd like to find out more about your hair braiding services. Could you help me choose the right style and get a booking?",
    'gallery'  => $style
        ? "Hi GYC Naturals! 🌿 I love the *{$style}* I saw in your gallery. I'd like to book this style — what's the availability and pricing?"
        : "Hi GYC Naturals! 🌿 I was browsing your style gallery and I'm interested in booking. Can you help me choose and schedule?",
    'shop'     => $product
        ? "Hi GYC Naturals! 🌿 I have a question about *{$product}* in your shop. Could you help me with more details?"
        : "Hi GYC Naturals! 🌿 I was browsing your natural hair products shop and I have a question. Can you help?",
    'booking'  => $ref
        ? "Hi GYC Naturals! 🌿 I'd like to follow up on my booking *#{$ref}*. Can you help me with more details?"
        : "Hi GYC Naturals! 🌿 I'd like to book a hair appointment. What are your available times this week?",
    'contact'  => "Hi GYC Naturals! 🌿 I sent a message through your contact form and I'd like to follow up. Can you help?",
    'quiz'     => "Hi GYC Naturals! 🌿 I just completed your hair quiz and I have some follow-up questions about my results. Can you help?",
    'general'  => "Hi GYC Naturals! 🌿 I found you online and I'd like to get more information about your salon and products.",
];

$message = $messages[$page] ?? $messages['general'];
$waUrl   = whatsappMessage($phone, $message);

// If this is an AJAX/fetch request, return JSON
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || ($_GET['json'] ?? false)) {
    header('Content-Type: application/json');
    echo json_encode(['url' => $waUrl]);
    exit;
}

// Otherwise redirect
header('Location: ' . $waUrl);
exit;
