<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Refund & Return Policy — GYC Naturals';
$pageDescription = 'GYC Naturals refund and return policy for natural hair products and salon services. Know your rights and how to request a refund.';
require_once __DIR__ . '/includes/header.php';

$lastUpdated = 'January 1, 2025';
$siteEmail   = getSetting('contact_email') ?: 'hello@gycnaturals.com';
$waPhone     = preg_replace('/[^0-9]/', '', getSetting('site_whatsapp') ?: SITE_WHATSAPP);
?>

<div style="min-height:72px;"></div>

<!-- HERO -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:4rem 0 3rem;color:#fff;text-align:center;">
  <div class="container" style="max-width:680px;">
    <i data-lucide="refresh-cw" style="width:40px;height:40px;color:var(--gyc-gold);margin-bottom:1rem;"></i>
    <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;margin-bottom:.75rem;">Refund &amp; Return Policy</h1>
    <p style="opacity:.8;font-size:.9rem;">Last updated: <?= $lastUpdated ?>. We want you to love every GYC product.</p>
  </div>
</section>

<!-- QUICK BOXES -->
<section style="background:#fff;padding:2.5rem 0;border-bottom:1px solid var(--gyc-green-100);">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;">
      <div style="text-align:center;padding:1.5rem;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);">
        <i data-lucide="package" style="width:32px;height:32px;color:var(--gyc-green-600);margin-bottom:.75rem;"></i>
        <div style="font-weight:700;font-size:.9rem;color:var(--gyc-dark);margin-bottom:.3rem;">Unopened Products</div>
        <div style="font-size:.82rem;color:#6B7280;">7-day returns accepted</div>
      </div>
      <div style="text-align:center;padding:1.5rem;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);">
        <i data-lucide="alert-triangle" style="width:32px;height:32px;color:var(--gyc-gold);margin-bottom:.75rem;"></i>
        <div style="font-weight:700;font-size:.9rem;color:var(--gyc-dark);margin-bottom:.3rem;">Damaged / Wrong Item</div>
        <div style="font-size:.82rem;color:#6B7280;">Full replacement or refund</div>
      </div>
      <div style="text-align:center;padding:1.5rem;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);">
        <i data-lucide="clock" style="width:32px;height:32px;color:var(--gyc-terra);margin-bottom:.75rem;"></i>
        <div style="font-weight:700;font-size:.9rem;color:var(--gyc-dark);margin-bottom:.3rem;">Refund Processing</div>
        <div style="font-size:.82rem;color:#6B7280;">5–10 business days</div>
      </div>
    </div>
  </div>
</section>

<!-- CONTENT -->
<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container" style="display:grid;grid-template-columns:220px 1fr;gap:3rem;align-items:start;max-width:1000px;">

    <aside style="position:sticky;top:90px;background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem;">
      <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-500);margin-bottom:.75rem;">Contents</p>
      <nav style="display:flex;flex-direction:column;gap:.4rem;">
        <?php $toc = [
          'product-returns'   => 'Product Returns',
          'non-returnable'    => 'Non-Returnable Items',
          'damaged'           => 'Damaged / Wrong Items',
          'how-to-return'     => 'How to Return',
          'refund-process'    => 'Refund Process',
          'salon-deposits'    => 'Salon Deposits',
          'exchange'          => 'Exchanges',
          'contact-refund'    => 'Contact Us',
        ];
        foreach ($toc as $anchor => $label): ?>
        <a href="#<?= $anchor ?>" style="font-size:.82rem;color:var(--gyc-green-700);text-decoration:none;padding:.25rem 0;"><?= htmlspecialchars($label) ?></a>
        <?php endforeach; ?>
      </nav>
    </aside>

    <article style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:2.5rem;">
      <style>
        .refund-section { margin-bottom: 2.5rem; }
        .refund-section h2 { font-family:'Playfair Display',serif; font-size:1.25rem; color:var(--gyc-dark); margin-bottom:1rem; padding-bottom:.5rem; border-bottom:2px solid var(--gyc-green-100); }
        .refund-section p, .refund-section li { font-size:.9rem; line-height:1.8; color:#374151; }
        .refund-section ul, .refund-section ol { padding-left:1.25rem; margin:.75rem 0; }
        .refund-section li { margin-bottom:.35rem; }
        .highlight-box { background:var(--gyc-green-100);border-left:4px solid var(--gyc-green-600);padding:1rem 1.25rem;border-radius:0 var(--gyc-radius) var(--gyc-radius) 0;margin:.75rem 0; }
      </style>

      <div class="refund-section" id="product-returns">
        <h2>Product Returns</h2>
        <div class="highlight-box">
          <strong>7-day return window</strong> — returns must be initiated within 7 days of confirmed delivery.
        </div>
        <p>We accept returns for <strong>unopened, unused products</strong> in their original packaging. To qualify:</p>
        <ul>
          <li>The product must be in its original, sealed condition with all labels intact.</li>
          <li>You must have proof of purchase (order number or email confirmation).</li>
          <li>The return must be initiated within 7 calendar days of delivery confirmation.</li>
          <li>Return shipping cost is the customer's responsibility unless the item is defective or incorrect.</li>
        </ul>
        <p>Products that have been opened, tested, or used — even partially — cannot be returned for hygiene and safety reasons.</p>
      </div>

      <div class="refund-section" id="non-returnable">
        <h2>Non-Returnable Items</h2>
        <p>The following items are <strong>final sale</strong> and cannot be returned or exchanged:</p>
        <ul>
          <li>Opened or used hair products (oils, serums, conditioners, shampoos)</li>
          <li>Sale or promotional items marked "Final Sale"</li>
          <li>Downloadable content or digital products</li>
          <li>Custom or personalised orders</li>
          <li>Hair accessories that have been used or removed from packaging</li>
          <li>Clothing items that have been worn, washed, or tags removed</li>
          <li>Gift cards</li>
        </ul>
      </div>

      <div class="refund-section" id="damaged">
        <h2>Damaged or Incorrect Items</h2>
        <p>If you receive a damaged, defective, or incorrect item, please:</p>
        <ol>
          <li>Take clear photos of the product and packaging immediately upon receipt.</li>
          <li>Contact us within <strong>48 hours</strong> of delivery via WhatsApp or email.</li>
          <li>We will arrange a free replacement or issue a full refund — your choice.</li>
        </ol>
        <p>Claims made after 48 hours may not be accepted unless the defect is latent (not visible on delivery).</p>
      </div>

      <div class="refund-section" id="how-to-return">
        <h2>How to Initiate a Return</h2>
        <ol>
          <li>Email <a href="mailto:<?= $siteEmail ?>" style="color:var(--gyc-green-600);"><?= $siteEmail ?></a> with subject line <strong>"Return – [Your Order Number]"</strong>, or message us on WhatsApp.</li>
          <li>Include: your order number, item(s) to return, reason for return, and photos (for damaged items).</li>
          <li>We will confirm eligibility within 1–2 business days and provide return instructions.</li>
          <li>Pack the item securely and ship to the address provided.</li>
          <li>Once received and inspected, we will process your refund or exchange within 5 business days.</li>
        </ol>
      </div>

      <div class="refund-section" id="refund-process">
        <h2>Refund Process &amp; Timeline</h2>
        <ul>
          <li>Approved refunds are processed to your <strong>original payment method</strong> (Paystack card/bank transfer).</li>
          <li>Processing time: <strong>5–10 business days</strong> after we receive and inspect the returned item.</li>
          <li>Bank processing time may add an additional 3–5 business days depending on your bank.</li>
          <li>You will receive an email confirmation once the refund has been processed.</li>
          <li>We do not issue cash refunds. Store credit may be offered as an alternative.</li>
        </ul>
      </div>

      <div class="refund-section" id="salon-deposits">
        <h2>Salon Appointment Deposits</h2>
        <p>Appointment deposits are subject to our cancellation policy:</p>
        <ul>
          <li><strong>48+ hours notice:</strong> Deposit transferred to a new appointment date (valid 90 days).</li>
          <li><strong>24–48 hours notice:</strong> 50% of deposit applied to rescheduled appointment; 50% forfeited.</li>
          <li><strong>Less than 24 hours / No-show:</strong> Full deposit forfeited.</li>
          <li><strong>GYC Naturals cancels:</strong> Full deposit refunded or transferred — your choice.</li>
        </ul>
        <p>Deposits are <strong>non-refundable to the original payment method</strong> except when GYC Naturals cancels the appointment.</p>
      </div>

      <div class="refund-section" id="exchange">
        <h2>Exchanges</h2>
        <p>We offer size or variant exchanges (e.g., clothing size) for <strong>unopened items within 7 days</strong>. Subject to stock availability. If the desired variant is out of stock, we will issue a refund or store credit. Contact us before returning to confirm availability.</p>
      </div>

      <div class="refund-section" id="contact-refund">
        <h2>Contact Us</h2>
        <p>Our team is happy to help resolve any issue:</p>
        <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:.75rem;">
          <a href="mailto:<?= $siteEmail ?>" class="btn btn-outline-green btn-sm" style="align-self:flex-start;">
            <i data-lucide="mail" style="width:16px;height:16px;"></i>
            <?= $siteEmail ?>
          </a>
          <a href="https://wa.me/<?= $waPhone ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-sm" style="align-self:flex-start;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            WhatsApp Us
          </a>
        </div>
      </div>

    </article>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
