<?php
/**
 * GYC Naturals — Branded HTML Email Templates
 * All functions return a complete HTML string ready for sendEmail()
 */

// ═══════════════════════════════════════════════════════
// SHARED LAYOUT WRAPPER
// ═══════════════════════════════════════════════════════

function emailWrapper(string $preheader, string $bodyHtml): string {
    $siteUrl    = defined('SITE_URL') ? SITE_URL : 'http://localhost/gyc-store';
    $logoUrl    = $siteUrl . '/assets/images/logo.png';
    $year       = date('Y');
    $address    = 'GYC Naturals, Victoria Island, Lagos, Nigeria';
    $whatsapp   = function_exists('getSetting') ? (getSetting('site_whatsapp') ?: '+2348100000000') : '+2348100000000';
    $instagram  = function_exists('getSetting') ? (getSetting('social_instagram') ?: 'gycnaturals') : 'gycnaturals';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>GYC Naturals</title>
<!--[if mso]>
<noscript>
  <xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml>
</noscript>
<![endif]-->
<style>
  /* Reset */
  body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;}
  table,td{mso-table-lspace:0;mso-table-rspace:0;}
  img{-ms-interpolation-mode:bicubic;border:0;height:auto;line-height:100%;outline:none;text-decoration:none;}
  /* Base */
  body{margin:0!important;padding:0!important;background:#F5F7F5;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;}
  /* Preheader */
  .preheader{display:none!important;max-height:0;overflow:hidden;mso-hide:all;}
  /* Container */
  .email-container{max-width:600px;margin:0 auto;}
  /* Header */
  .email-header{background:#14532d;padding:28px 32px;text-align:center;}
  .email-header img{max-height:48px;width:auto;}
  .email-tagline{color:rgba(255,255,255,.7);font-size:12px;margin-top:6px;letter-spacing:.08em;}
  /* Body card */
  .email-body{background:#ffffff;padding:36px 40px;}
  /* Footer */
  .email-footer{background:#1a1a1a;padding:24px 32px;text-align:center;}
  /* Typography */
  h1{margin:0 0 8px;font-size:26px;line-height:1.3;color:#14532d;font-weight:700;}
  h2{margin:24px 0 8px;font-size:18px;color:#14532d;}
  p{margin:0 0 16px;font-size:15px;line-height:1.65;color:#374151;}
  a{color:#16a34a;}
  /* Divider */
  .divider{border:none;border-top:1.5px solid #E5E7EB;margin:24px 0;}
  /* Info table */
  .info-table{width:100%;border-collapse:collapse;font-size:14px;margin:16px 0;}
  .info-table td{padding:9px 12px;border-bottom:1px solid #F0F0F0;color:#374151;}
  .info-table td:first-child{font-weight:600;color:#111;width:38%;background:#F8FAF9;}
  .info-table tr:last-child td{border-bottom:none;}
  /* Items table */
  .items-table{width:100%;border-collapse:collapse;font-size:14px;margin:16px 0;}
  .items-table th{background:#14532d;color:#fff;padding:10px 12px;text-align:left;font-weight:600;}
  .items-table td{padding:10px 12px;border-bottom:1px solid #F0F0F0;vertical-align:top;}
  .items-table tr:last-child td{border-bottom:none;}
  .items-table .price{text-align:right;font-weight:700;white-space:nowrap;}
  /* Total row */
  .total-row td{background:#F8FAF9;font-weight:700;font-size:15px;color:#14532d;}
  /* CTA button */
  .btn-email{display:inline-block;background:#16a34a;color:#ffffff!important;text-decoration:none;padding:13px 28px;border-radius:6px;font-weight:700;font-size:15px;letter-spacing:.02em;margin:8px 0;}
  .btn-email-outline{display:inline-block;border:2px solid #16a34a;color:#16a34a!important;text-decoration:none;padding:11px 26px;border-radius:6px;font-weight:700;font-size:15px;letter-spacing:.02em;margin:8px 4px;}
  /* Alert boxes */
  .alert-box{padding:14px 18px;border-radius:6px;font-size:14px;margin:16px 0;}
  .alert-green{background:#DCFCE7;border-left:4px solid #16a34a;color:#14532d;}
  .alert-gold{background:#FEF9C3;border-left:4px solid #CA8A04;color:#713F12;}
  .alert-terra{background:#FEE2E2;border-left:4px solid #DC2626;color:#7F1D1D;}
  /* Badge */
  .badge-green{display:inline-block;background:#16a34a;color:#fff;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:700;letter-spacing:.05em;}
  .badge-gold{display:inline-block;background:#CA8A04;color:#fff;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:700;letter-spacing:.05em;}
  /* Social */
  .social-links a{color:#9CA3AF!important;font-size:12px;margin:0 8px;text-decoration:none;}
  /* Responsive */
  @media only screen and (max-width:620px){
    .email-body{padding:24px 20px!important;}
    .email-header{padding:20px 20px!important;}
    h1{font-size:22px!important;}
  }
</style>
</head>
<body>
<span class="preheader">{$preheader} &nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;‌&nbsp;</span>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#F5F7F5">
<tr><td>
  <!-- Top spacer -->
  <table role="presentation" class="email-container" width="600" cellpadding="0" cellspacing="0" border="0" align="center">

    <!-- HEADER -->
    <tr>
      <td class="email-header">
        <img src="{$logoUrl}" alt="GYC Naturals" height="48">
        <div class="email-tagline">Natural Hair · Braiding · Clothing — Victoria Island, Lagos</div>
      </td>
    </tr>

    <!-- BODY -->
    <tr>
      <td class="email-body">
        {$bodyHtml}
      </td>
    </tr>

    <!-- FOOTER -->
    <tr>
      <td class="email-footer">
        <div class="social-links" style="margin-bottom:12px;">
          <a href="https://instagram.com/{$instagram}">Instagram</a>
          <a href="https://wa.me/{$whatsapp}">WhatsApp</a>
          <a href="{$siteUrl}">Website</a>
        </div>
        <p style="margin:0;font-size:12px;color:#6B7280;line-height:1.6;">
          {$address}<br>
          © {$year} GYC Naturals. All rights reserved.
        </p>
        <p style="margin:8px 0 0;font-size:11px;color:#4B5563;">
          You're receiving this because you interacted with GYC Naturals.
          <a href="{$siteUrl}" style="color:#6B7280;">Unsubscribe</a>
        </p>
      </td>
    </tr>

  </table>
</td></tr>
</table>
</body>
</html>
HTML;
}

// ═══════════════════════════════════════════════════════
// ORDER CONFIRMATION
// ═══════════════════════════════════════════════════════

/**
 * @param array $order  Keys: order_number, shipping_first_name, shipping_last_name,
 *                            shipping_address, shipping_city, shipping_state,
 *                            total, subtotal, shipping_cost, customer_email, created_at
 * @param array $items  Each: ['name'=>..., 'quantity'=>..., 'price'=>..., 'image_url'=>...]
 */
function emailOrderConfirmation(array $order, array $items): string {
    $siteUrl    = defined('SITE_URL') ? SITE_URL : 'http://localhost/gyc-store';
    $orderNum   = htmlspecialchars($order['order_number'] ?? '');
    $firstName  = htmlspecialchars($order['shipping_first_name'] ?? 'Valued Customer');
    $lastName   = htmlspecialchars($order['shipping_last_name'] ?? '');
    $addrParts = array_filter([
        $order['shipping_address'] ?? null,
        $order['shipping_city']    ?? null,
        $order['shipping_state']   ?? null,
        $order['shipping_country'] ?? null,
    ]);
    $address = htmlspecialchars(implode(', ', $addrParts));
    $total      = formatPrice($order['total'] ?? 0);
    $subtotal   = formatPrice($order['subtotal'] ?? 0);
    $shipping   = formatPrice($order['shipping_cost'] ?? $order['shipping'] ?? 0);
    $orderUrl   = $siteUrl . '/order-details.php?order=' . urlencode($order['order_number'] ?? '');
    $date       = !empty($order['created_at']) ? date('D, jS F Y', strtotime($order['created_at'])) : date('D, jS F Y');

    // Build items rows
    $itemRows = '';
    foreach ($items as $item) {
        $name  = htmlspecialchars($item['name'] ?? 'Product');
        $qty   = (int)($item['quantity'] ?? 1);
        $price = formatPrice(($item['price'] ?? 0) * $qty);
        $itemRows .= "<tr><td>{$name}</td><td style=\"text-align:center;\">{$qty}</td><td class=\"price\">{$price}</td></tr>";
    }

    $body = <<<HTML
<h1>🎉 Order Confirmed!</h1>
<p>Hi <strong>{$firstName}</strong>, thank you for shopping with GYC Naturals. We've received your order and payment — we're already getting it ready for you!</p>

<div class="alert-box alert-green" style="margin-bottom:20px;">
  <strong>Order Reference: {$orderNum}</strong> &nbsp;<span class="badge-green">Paid ✓</span><br>
  <span style="font-size:13px;opacity:.8;">Placed on {$date}</span>
</div>

<h2>🛍️ Your Items</h2>
<table class="items-table" role="presentation">
  <thead>
    <tr>
      <th>Product</th>
      <th style="text-align:center;width:50px;">Qty</th>
      <th style="text-align:right;width:90px;">Amount</th>
    </tr>
  </thead>
  <tbody>
    {$itemRows}
    <tr class="total-row">
      <td colspan="2" style="text-align:right;padding-right:12px;">Subtotal</td>
      <td class="price">{$subtotal}</td>
    </tr>
    <tr class="total-row">
      <td colspan="2" style="text-align:right;padding-right:12px;">Shipping</td>
      <td class="price">{$shipping}</td>
    </tr>
    <tr style="background:#14532d;">
      <td colspan="2" style="text-align:right;padding-right:12px;color:#fff;font-weight:700;font-size:15px;">Total Paid</td>
      <td class="price" style="color:#fff;background:#14532d;">{$total}</td>
    </tr>
  </tbody>
</table>

<h2>📦 Delivery Details</h2>
<table class="info-table" role="presentation">
  <tr><td>Name</td><td>{$firstName} {$lastName}</td></tr>
  <tr><td>Deliver To</td><td>{$address}</td></tr>
  <tr><td>Est. Delivery</td><td>3–5 business days (Lagos) · 5–7 days (other states)</td></tr>
</table>

<div class="alert-box alert-gold">
  <strong>📱 Track your order</strong><br>
  You can follow your order status anytime from your account dashboard.
</div>

<p style="text-align:center;margin:28px 0;">
  <a href="{$orderUrl}" class="btn-email">View Order Details</a>
</p>

<hr class="divider">
<p style="font-size:13px;color:#6B7280;">Questions? Reply to this email or reach us on WhatsApp — we're always happy to help.</p>
HTML;

    return emailWrapper("Your GYC Naturals order #{$orderNum} is confirmed — thank you!", $body);
}

// ═══════════════════════════════════════════════════════
// BOOKING CONFIRMATION
// ═══════════════════════════════════════════════════════

/**
 * @param array $apt     Keys: appointment_number, customer_name, requested_date, requested_time,
 *                             style_name, deposit_amount, customer_notes
 * @param bool  $depositPaid  Whether the deposit was already paid via Paystack
 */
function emailBookingConfirmation(array $apt, bool $depositPaid = false): string {
    $siteUrl    = defined('SITE_URL') ? SITE_URL : 'http://localhost/gyc-store';
    $aptNum     = htmlspecialchars($apt['appointment_number'] ?? '');
    $name       = htmlspecialchars($apt['customer_name'] ?? 'Valued Client');
    $rawDate    = $apt['requested_date'] ?? '';
    $date       = $rawDate ? date('l, jS F Y', strtotime($rawDate)) : '—';
    $time       = !empty($apt['requested_time']) ? htmlspecialchars($apt['requested_time']) : 'To be confirmed';
    $style      = htmlspecialchars($apt['style_name'] ?? 'Appointment');
    $notes      = !empty($apt['customer_notes']) ? htmlspecialchars($apt['customer_notes']) : '—';
    $deposit    = !empty($apt['deposit_amount']) ? formatPrice($apt['deposit_amount']) : '—';
    $bookUrl    = $siteUrl . '/booking-confirmation.php?apt=' . urlencode($apt['appointment_number'] ?? '');

    $depositNote = $depositPaid
        ? '<div class="alert-box alert-green"><strong>✅ Deposit Received</strong> — Your booking is fully secured. See you soon!</div>'
        : '<div class="alert-box alert-gold"><strong>⏳ Deposit Required</strong> — Please pay your deposit of ' . $deposit . ' to confirm your slot. We\'ll hold it for 24 hours.</div>';

    $body = <<<HTML
<h1>✂️ Booking Received!</h1>
<p>Hi <strong>{$name}</strong>, your appointment request with GYC Naturals has been received. We'll confirm your slot within 2 hours during business hours (9am–7pm, Mon–Sat).</p>

<div class="alert-box alert-green">
  <strong>Booking Reference: {$aptNum}</strong><br>
  <span style="font-size:13px;opacity:.8;">Please save this reference for your records.</span>
</div>

<h2>📋 Booking Details</h2>
<table class="info-table" role="presentation">
  <tr><td>Style / Service</td><td><strong>{$style}</strong></td></tr>
  <tr><td>Date</td><td>{$date}</td></tr>
  <tr><td>Time</td><td>{$time}</td></tr>
  <tr><td>Your Notes</td><td>{$notes}</td></tr>
  <tr><td>Deposit</td><td>{$deposit}</td></tr>
</table>

{$depositNote}

<h2>📍 Location & What to Bring</h2>
<table class="info-table" role="presentation">
  <tr><td>Salon</td><td>GYC Naturals, Victoria Island, Lagos</td></tr>
  <tr><td>What to bring</td><td>Clean, detangled hair. Arrive on time — late arrivals may need to reschedule.</td></tr>
  <tr><td>Cancellation</td><td>Cancel at least 24 hrs in advance to avoid deposit forfeiture.</td></tr>
</table>

<p style="text-align:center;margin:28px 0;">
  <a href="{$bookUrl}" class="btn-email">View Booking Details</a>
  <a href="{$siteUrl}/book-appointment.php" class="btn-email-outline">Book Another Style</a>
</p>

<hr class="divider">
<p style="font-size:13px;color:#6B7280;">Need to change or cancel? Reply to this email or WhatsApp us. We're here to help you look your best!</p>
HTML;

    return emailWrapper("Booking confirmed — GYC Naturals · Ref #{$aptNum}", $body);
}

// ═══════════════════════════════════════════════════════
// WELCOME / REGISTRATION
// ═══════════════════════════════════════════════════════

function emailWelcome(string $firstName, string $email = ''): string {
    $siteUrl  = defined('SITE_URL') ? SITE_URL : 'http://localhost/gyc-store';
    $name     = htmlspecialchars($firstName);

    $body = <<<HTML
<h1>Welcome to GYC Naturals, {$name}! 🌿</h1>
<p>We're so glad you're here. Your account is now active and you have full access to everything GYC Naturals offers — from booking appointments to managing your orders and building your personal moodboard.</p>

<div class="alert-box alert-green">
  <strong>Your account is ready.</strong> You can sign in anytime at <a href="{$siteUrl}/login.php">{$siteUrl}/login.php</a>
</div>

<h2>✨ What You Can Do</h2>
<table class="info-table" role="presentation">
  <tr><td>📅 Book</td><td>Browse styles in the gallery and book an appointment in minutes.</td></tr>
  <tr><td>🛍️ Shop</td><td>Order our curated natural hair products, delivered across Nigeria.</td></tr>
  <tr><td>📋 Track</td><td>View all your orders, bookings, and wishlist from your dashboard.</td></tr>
  <tr><td>🎨 Moodboard</td><td>Save styles you love and share them with your braider.</td></tr>
  <tr><td>⭐ Review</td><td>Leave reviews after each service or purchase.</td></tr>
</table>

<p style="text-align:center;margin:28px 0;">
  <a href="{$siteUrl}/book-appointment.php" class="btn-email">Book Your First Appointment</a>
  <a href="{$siteUrl}/shop.php" class="btn-email-outline">Shop Products</a>
</p>

<hr class="divider">
<p style="font-size:13px;color:#6B7280;">If you didn't create this account, please <a href="{$siteUrl}">contact us</a> immediately.</p>
HTML;

    return emailWrapper("Welcome to GYC Naturals, {$firstName}! Your account is ready.", $body);
}

// ═══════════════════════════════════════════════════════
// PASSWORD RESET
// ═══════════════════════════════════════════════════════

function emailPasswordReset(string $firstName, string $resetUrl): string {
    $siteUrl  = defined('SITE_URL') ? SITE_URL : 'http://localhost/gyc-store';
    $name     = htmlspecialchars($firstName);
    $url      = htmlspecialchars($resetUrl);

    $body = <<<HTML
<h1>Reset Your Password</h1>
<p>Hi <strong>{$name}</strong>, we received a request to reset the password for your GYC Naturals account.</p>

<p style="text-align:center;margin:28px 0;">
  <a href="{$url}" class="btn-email">Reset My Password</a>
</p>

<div class="alert-box alert-gold">
  <strong>⏱ This link expires in 1 hour.</strong> If you don't reset your password within that time, you'll need to request a new link.
</div>

<p>If the button doesn't work, copy and paste this link into your browser:</p>
<p style="word-break:break-all;background:#F3F4F6;padding:10px 14px;border-radius:4px;font-size:13px;color:#374151;">{$url}</p>

<hr class="divider">
<p style="font-size:13px;color:#6B7280;">If you didn't request a password reset, you can safely ignore this email — your account is still secure.</p>
HTML;

    return emailWrapper("Reset your GYC Naturals password — link expires in 1 hour", $body);
}

// ═══════════════════════════════════════════════════════
// ORDER STATUS UPDATE
// ═══════════════════════════════════════════════════════

/**
 * @param array  $order   Keys: order_number, shipping_first_name
 * @param string $status  new status: 'processing'|'shipped'|'delivered'|'cancelled'
 * @param string $note    Optional custom message from admin
 */
function emailOrderStatusUpdate(array $order, string $status, string $note = ''): string {
    $siteUrl  = defined('SITE_URL') ? SITE_URL : 'http://localhost/gyc-store';
    $orderNum = htmlspecialchars($order['order_number'] ?? '');
    $name     = htmlspecialchars($order['shipping_first_name'] ?? 'Customer');
    $orderUrl = $siteUrl . '/order-details.php?order=' . urlencode($order['order_number'] ?? '');

    $statusInfo = [
        'processing' => ['emoji' => '⚙️', 'label' => 'Being Prepared',  'color' => 'alert-gold',  'msg' => 'Great news! Your order is currently being prepared and packed with care.'],
        'shipped'    => ['emoji' => '🚚', 'label' => 'On Its Way',       'color' => 'alert-gold',  'msg' => 'Your order is on its way to you! Delivery typically takes 1–3 business days within Lagos.'],
        'delivered'  => ['emoji' => '✅', 'label' => 'Delivered',        'color' => 'alert-green', 'msg' => 'Your order has been delivered! We hope you love your GYC Naturals products.'],
        'cancelled'  => ['emoji' => '❌', 'label' => 'Cancelled',        'color' => 'alert-terra', 'msg' => 'Your order has been cancelled. If you paid, a refund will be processed within 3–5 business days.'],
    ];

    $info    = $statusInfo[$status] ?? $statusInfo['processing'];
    $emoji   = $info['emoji'];
    $label   = $info['label'];
    $colorCls = $info['color'];
    $msg     = $info['msg'];
    $noteHtml = $note ? "<p><strong>Message from GYC Naturals:</strong> " . htmlspecialchars($note) . "</p>" : '';

    $body = <<<HTML
<h1>{$emoji} Order Update: {$label}</h1>
<p>Hi <strong>{$name}</strong>,</p>
<div class="alert-box {$colorCls}">
  <strong>Order #{$orderNum}</strong> — Status: <strong>{$label}</strong>
</div>
<p>{$msg}</p>
{$noteHtml}
<p style="text-align:center;margin:28px 0;">
  <a href="{$orderUrl}" class="btn-email">View Order Details</a>
</p>
<hr class="divider">
<p style="font-size:13px;color:#6B7280;">Questions? Reply to this email or WhatsApp us directly.</p>
HTML;

    return emailWrapper("Order #{$orderNum} Update: {$label} — GYC Naturals", $body);
}

// ═══════════════════════════════════════════════════════
// APPOINTMENT STATUS UPDATE
// ═══════════════════════════════════════════════════════

/**
 * @param array  $apt    Keys: appointment_number, customer_name, requested_date, requested_time, style_name
 * @param string $status 'confirmed'|'rescheduled'|'cancelled'
 * @param string $note   Optional admin note
 */
function emailAppointmentUpdate(array $apt, string $status, string $note = ''): string {
    $siteUrl = defined('SITE_URL') ? SITE_URL : 'http://localhost/gyc-store';
    $aptNum  = htmlspecialchars($apt['appointment_number'] ?? '');
    $name    = htmlspecialchars($apt['customer_name'] ?? 'Valued Client');
    $rawDate = $apt['requested_date'] ?? '';
    $date    = $rawDate ? date('l, jS F Y', strtotime($rawDate)) : '—';
    $time    = !empty($apt['requested_time']) ? htmlspecialchars($apt['requested_time']) : 'To be confirmed';
    $style   = htmlspecialchars($apt['style_name'] ?? 'Appointment');

    $statusInfo = [
        'confirmed'   => ['emoji' => '✅', 'label' => 'Confirmed',    'color' => 'alert-green', 'msg' => "Your appointment is confirmed! Please arrive 5 minutes early."],
        'rescheduled' => ['emoji' => '🗓️', 'label' => 'Rescheduled', 'color' => 'alert-gold',  'msg' => "Your appointment has been rescheduled. Please review the new date/time below."],
        'cancelled'   => ['emoji' => '❌', 'label' => 'Cancelled',    'color' => 'alert-terra', 'msg' => "We're sorry, your appointment has been cancelled. Please book again at your convenience."],
    ];

    $info     = $statusInfo[$status] ?? $statusInfo['confirmed'];
    $emoji    = $info['emoji'];
    $label    = $info['label'];
    $colorCls = $info['color'];
    $msg      = $info['msg'];
    $noteHtml = $note ? "<p><strong>Note from GYC Naturals:</strong> " . htmlspecialchars($note) . "</p>" : '';
    $bookUrl  = $siteUrl . '/book-appointment.php';

    $body = <<<HTML
<h1>{$emoji} Appointment {$label}</h1>
<p>Hi <strong>{$name}</strong>,</p>
<div class="alert-box {$colorCls}"><strong>{$msg}</strong></div>
<h2>📋 Appointment Details</h2>
<table class="info-table" role="presentation">
  <tr><td>Reference</td><td><strong>{$aptNum}</strong></td></tr>
  <tr><td>Style</td><td>{$style}</td></tr>
  <tr><td>Date</td><td>{$date}</td></tr>
  <tr><td>Time</td><td>{$time}</td></tr>
</table>
{$noteHtml}
<p style="text-align:center;margin:28px 0;">
  <a href="{$bookUrl}" class="btn-email">Book New Appointment</a>
</p>
<hr class="divider">
<p style="font-size:13px;color:#6B7280;">Questions? Reply to this email or WhatsApp us. We look forward to serving you!</p>
HTML;

    return emailWrapper("Appointment #{$aptNum}: {$label} — GYC Naturals", $body);
}
