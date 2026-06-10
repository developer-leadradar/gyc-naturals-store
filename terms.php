<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Terms & Conditions — GYC Naturals';
$pageDescription = 'Read the Terms and Conditions governing your use of GYC Naturals website, salon services, and product orders.';
require_once __DIR__ . '/includes/header.php';

$lastUpdated = 'January 1, 2025';
$siteEmail   = getSetting('contact_email') ?: 'hello@gycnaturals.com';
?>

<div style="min-height:72px;"></div>

<!-- HERO -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:4rem 0 3rem;color:#fff;text-align:center;">
  <div class="container" style="max-width:680px;">
    <i data-lucide="file-text" style="width:40px;height:40px;color:var(--gyc-gold);margin-bottom:1rem;"></i>
    <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;margin-bottom:.75rem;">Terms &amp; Conditions</h1>
    <p style="opacity:.8;font-size:.9rem;">Last updated: <?= $lastUpdated ?>. By using our website or services, you agree to these terms.</p>
  </div>
</section>

<!-- CONTENT -->
<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container" style="display:grid;grid-template-columns:220px 1fr;gap:3rem;align-items:start;max-width:1000px;">

    <aside style="position:sticky;top:90px;background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem;">
      <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-500);margin-bottom:.75rem;">Contents</p>
      <nav style="display:flex;flex-direction:column;gap:.4rem;">
        <?php $toc = [
          'acceptance'    => '1. Acceptance',
          'services'      => '2. Salon Services',
          'products'      => '3. Product Orders',
          'payments'      => '4. Payments',
          'cancellation'  => '5. Cancellations',
          'ip'            => '6. Intellectual Property',
          'liability'     => '7. Limitation of Liability',
          'governing'     => '8. Governing Law',
          'contact'       => '9. Contact',
        ];
        foreach ($toc as $anchor => $label): ?>
        <a href="#<?= $anchor ?>" style="font-size:.82rem;color:var(--gyc-green-700);text-decoration:none;padding:.25rem 0;"><?= htmlspecialchars($label) ?></a>
        <?php endforeach; ?>
      </nav>
    </aside>

    <article style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:2.5rem;">
      <style>
        .terms-section { margin-bottom: 2.5rem; }
        .terms-section h2 { font-family:'Playfair Display',serif; font-size:1.25rem; color:var(--gyc-dark); margin-bottom:1rem; padding-bottom:.5rem; border-bottom:2px solid var(--gyc-green-100); }
        .terms-section p, .terms-section li { font-size:.9rem; line-height:1.8; color:#374151; }
        .terms-section ul, .terms-section ol { padding-left:1.25rem; margin:.75rem 0; }
        .terms-section li { margin-bottom:.35rem; }
      </style>

      <div class="terms-section" id="acceptance">
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing <strong><?= SITE_URL ?></strong> or booking our salon services, you confirm that you are at least 18 years old (or have parental consent) and agree to be bound by these Terms and Conditions. GYC Naturals reserves the right to update these terms at any time. Continued use after changes constitutes acceptance.</p>
      </div>

      <div class="terms-section" id="services">
        <h2>2. Salon Services</h2>
        <ul>
          <li>All appointment slots are subject to availability and are not confirmed until you receive a confirmation message from us.</li>
          <li>A non-refundable deposit (30% of the service price, minimum ₦2,000) is required to secure your appointment. The deposit is credited toward your total service fee.</li>
          <li>Please arrive on time. We allow a 15-minute grace period. Appointments may be forfeited and deposits retained for late arrivals beyond this window without prior notice.</li>
          <li>GYC Naturals reserves the right to decline services at our discretion, including cases of aggressive behaviour toward staff.</li>
          <li>Patch tests are recommended for new clients receiving chemical or colour treatments. We are not liable for reactions where the client declined a patch test.</li>
          <li>Service prices displayed on the website are indicative. Exact pricing is confirmed at consultation and may vary based on hair length, density, or complexity.</li>
        </ul>
      </div>

      <div class="terms-section" id="products">
        <h2>3. Product Orders</h2>
        <ul>
          <li>All prices are in Nigerian Naira (₦) and are inclusive of VAT where applicable.</li>
          <li>Product images are for illustrative purposes. Minor colour variations may occur due to screen settings.</li>
          <li>We ship to all states in Nigeria. Delivery timelines are estimates and not guaranteed. GYC Naturals is not responsible for delays caused by third-party couriers or force majeure.</li>
          <li>Risk of loss and title for products pass to you upon delivery to the courier.</li>
          <li>Certain products (e.g., opened skincare, intimate care) cannot be returned for hygiene reasons. See our Refund Policy for full details.</li>
        </ul>
      </div>

      <div class="terms-section" id="payments">
        <h2>4. Payments</h2>
        <ul>
          <li>Online payments are processed by <strong>Paystack</strong>. We do not store card details on our servers.</li>
          <li>By completing a payment, you authorise GYC Naturals to charge the stated amount.</li>
          <li>Failed payments do not constitute order confirmation. Please contact us if funds are debited but no confirmation is received.</li>
          <li>Refunds, where applicable, are processed within 5–10 business days via the original payment method.</li>
          <li>Bank transfer payments must be confirmed by sending proof of payment via WhatsApp to complete your order.</li>
        </ul>
      </div>

      <div class="terms-section" id="cancellation">
        <h2>5. Cancellations &amp; Rescheduling</h2>
        <ul>
          <li><strong>48+ hours notice:</strong> Full deposit transferred to a rescheduled appointment date.</li>
          <li><strong>24–48 hours notice:</strong> 50% of deposit transferred; remainder retained by GYC Naturals.</li>
          <li><strong>Less than 24 hours / No-show:</strong> Full deposit is forfeited.</li>
          <li>GYC Naturals may cancel or reschedule appointments due to staff illness, facility issues, or unforeseen circumstances. In such cases, we will offer a full rescheduled slot or refund of deposit.</li>
          <li>Product orders may be cancelled before dispatch. Once dispatched, cancellations follow our Refund Policy.</li>
        </ul>
      </div>

      <div class="terms-section" id="ip">
        <h2>6. Intellectual Property</h2>
        <p>All content on this website — including text, images, logos, videos, and the GYC Naturals brand — is owned by or licensed to GYC Naturals and protected by Nigerian copyright law. You may not reproduce, distribute, or create derivative works without written permission. You may share links to our content with attribution.</p>
      </div>

      <div class="terms-section" id="liability">
        <h2>7. Limitation of Liability</h2>
        <p>To the fullest extent permitted by Nigerian law, GYC Naturals shall not be liable for:</p>
        <ul>
          <li>Indirect, incidental, or consequential damages arising from use of our website or services</li>
          <li>Loss of data, revenue, or goodwill</li>
          <li>Third-party actions, including delivery delays or payment processor outages</li>
          <li>Adverse reactions to products where instructions and patch test recommendations were not followed</li>
        </ul>
        <p>Our total liability for any claim shall not exceed the amount you paid for the relevant service or product.</p>
      </div>

      <div class="terms-section" id="governing">
        <h2>8. Governing Law</h2>
        <p>These Terms are governed by and construed in accordance with the laws of the Federal Republic of Nigeria. Any disputes shall be subject to the exclusive jurisdiction of the courts of Cross River State, Nigeria. We encourage you to contact us first to resolve any dispute informally.</p>
      </div>

      <div class="terms-section" id="contact">
        <h2>9. Contact Us</h2>
        <p>Questions about these Terms? Email us at <a href="mailto:<?= $siteEmail ?>" style="color:var(--gyc-green-600);"><?= $siteEmail ?></a> or visit our <a href="<?= SITE_URL ?>/contact.php" style="color:var(--gyc-green-600);">Contact page</a>.</p>
      </div>

    </article>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
