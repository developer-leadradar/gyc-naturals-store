<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Privacy Policy — GYC Naturals';
$pageDescription = 'Learn how GYC Naturals collects, uses, and protects your personal data in accordance with Nigerian data protection regulations.';
require_once __DIR__ . '/includes/header.php';

$lastUpdated = 'January 1, 2025';
$siteName    = getSetting('site_name') ?: 'GYC Naturals';
$siteEmail   = getSetting('contact_email') ?: 'hello@gycnaturals.com';
$siteAddress = getSetting('site_address') ?: 'Big Qua Mall, Ediba Road, Calabar, Cross River State, Nigeria';
?>


<!-- ── HERO ── -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:4rem 0 3rem;color:#fff;text-align:center;">
  <div class="container" style="max-width:680px;">
    <i data-lucide="shield-check" style="width:40px;height:40px;color:var(--gyc-gold);margin-bottom:1rem;"></i>
    <h1 style="font-family:'Playfair Display',serif;font-size:2.2rem;margin-bottom:.75rem;">Privacy Policy</h1>
    <p style="opacity:.8;font-size:.9rem;">Last updated: <?= $lastUpdated ?></p>
  </div>
</section>

<!-- ── CONTENT ── -->
<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container" style="display:grid;grid-template-columns:220px 1fr;gap:3rem;align-items:start;max-width:1000px;">

    <!-- Sticky TOC -->
    <aside style="position:sticky;top:90px;background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem;">
      <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-500);margin-bottom:.75rem;">Contents</p>
      <nav style="display:flex;flex-direction:column;gap:.4rem;">
        <?php $toc = [
          'info-we-collect'  => 'Information We Collect',
          'how-we-use'       => 'How We Use It',
          'sharing'          => 'Data Sharing',
          'cookies'          => 'Cookies',
          'security'         => 'Data Security',
          'your-rights'      => 'Your Rights',
          'children'         => 'Children\'s Privacy',
          'changes'          => 'Policy Changes',
          'contact-us'       => 'Contact Us',
        ];
        foreach ($toc as $anchor => $label): ?>
        <a href="#<?= $anchor ?>" style="font-size:.82rem;color:var(--gyc-green-700);text-decoration:none;padding:.25rem 0;"><?= htmlspecialchars($label) ?></a>
        <?php endforeach; ?>
      </nav>
    </aside>

    <!-- Body -->
    <article style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:2.5rem;">
      <style>
        .policy-section { margin-bottom: 2.5rem; }
        .policy-section h2 { font-family:'Playfair Display',serif; font-size:1.25rem; color:var(--gyc-dark); margin-bottom:1rem; padding-bottom:.5rem; border-bottom:2px solid var(--gyc-green-100); }
        .policy-section p, .policy-section li { font-size:.9rem; line-height:1.8; color:#374151; }
        .policy-section ul { padding-left:1.25rem; margin:.75rem 0; }
        .policy-section li { margin-bottom:.35rem; }
      </style>

      <p style="font-size:.9rem;line-height:1.8;color:#374151;margin-bottom:2rem;">
        <?= $siteName ?> ("we", "our", "us") operates <?= SITE_URL ?> and provides hair salon services and natural hair products. This Privacy Policy explains how we collect, use, and safeguard your personal information. By using our website or services, you agree to this policy.
      </p>

      <div class="policy-section" id="info-we-collect">
        <h2>1. Information We Collect</h2>
        <p>We collect information you provide directly and data generated automatically:</p>
        <ul>
          <li><strong>Personal identifiers:</strong> Name, email address, phone number</li>
          <li><strong>Appointment data:</strong> Preferred style, requested date/time, service notes</li>
          <li><strong>Order & payment data:</strong> Billing address, order history. Payment card details are processed securely by Paystack and are never stored on our servers.</li>
          <li><strong>Account data:</strong> Password (hashed and salted — never stored in plain text), profile photo (optional)</li>
          <li><strong>Technical data:</strong> IP address, browser type, operating system, pages visited, referring URLs</li>
          <li><strong>Cookies:</strong> Session identifiers, preference tokens (see Section 4)</li>
          <li><strong>Communications:</strong> Messages sent via our contact form or WhatsApp</li>
        </ul>
      </div>

      <div class="policy-section" id="how-we-use">
        <h2>2. How We Use Your Information</h2>
        <ul>
          <li>To process and fulfil product orders and send delivery updates</li>
          <li>To book, confirm, and manage salon appointments</li>
          <li>To process payments via Paystack and send receipts</li>
          <li>To respond to enquiries and provide customer support</li>
          <li>To send transactional emails (order confirmations, appointment reminders)</li>
          <li>To send marketing communications — only with your consent and with an easy opt-out in every email</li>
          <li>To improve our website, services, and product range through analytics</li>
          <li>To prevent fraud, detect misuse, and enforce our Terms of Service</li>
          <li>To comply with applicable Nigerian law and regulations</li>
        </ul>
      </div>

      <div class="policy-section" id="sharing">
        <h2>3. Data Sharing</h2>
        <p>We do <strong>not</strong> sell or rent your personal data. We share data only in these circumstances:</p>
        <ul>
          <li><strong>Paystack:</strong> Payment processing. See <a href="https://paystack.com/privacy" target="_blank" rel="noopener" style="color:var(--gyc-green-600);">Paystack's Privacy Policy</a>.</li>
          <li><strong>Delivery partners:</strong> Name and address shared with couriers for order fulfilment.</li>
          <li><strong>Email providers:</strong> We use Resend/PHPMailer to send transactional emails; your email address is transmitted to send each message.</li>
          <li><strong>Analytics:</strong> Anonymised, aggregated data with analytics services.</li>
          <li><strong>Legal obligations:</strong> If required by Nigerian law, a court order, or to protect our rights.</li>
          <li><strong>Business transfers:</strong> If GYC Naturals is acquired or merges, your data may transfer to the new entity.</li>
        </ul>
      </div>

      <div class="policy-section" id="cookies">
        <h2>4. Cookies &amp; Tracking</h2>
        <p>We use cookies and similar technologies:</p>
        <ul>
          <li><strong>Essential cookies:</strong> Session cookie for login state and shopping cart. Cannot be disabled.</li>
          <li><strong>Preference cookies:</strong> Remember your settings (e.g., currency display).</li>
          <li><strong>Analytics cookies:</strong> Anonymised traffic data. You can opt out via your browser settings.</li>
          <li><strong>localStorage:</strong> Moodboard data stored locally on your device — never transmitted to our servers unless you share a link.</li>
        </ul>
        <p>You can delete cookies via your browser settings. Disabling essential cookies will affect cart and login functionality.</p>
      </div>

      <div class="policy-section" id="security">
        <h2>5. Data Security</h2>
        <ul>
          <li>All data in transit is encrypted using TLS (HTTPS).</li>
          <li>Passwords are hashed using bcrypt with a cost factor of 12 — we cannot recover your password.</li>
          <li>Payment data is handled entirely by Paystack's PCI-DSS compliant infrastructure.</li>
          <li>We restrict employee access to personal data on a need-to-know basis.</li>
          <li>Despite these measures, no internet transmission is 100% secure. Please use a strong, unique password.</li>
        </ul>
      </div>

      <div class="policy-section" id="your-rights">
        <h2>6. Your Rights</h2>
        <p>Under the Nigeria Data Protection Regulation (NDPR) and applicable law, you have the right to:</p>
        <ul>
          <li><strong>Access:</strong> Request a copy of the personal data we hold about you.</li>
          <li><strong>Correction:</strong> Ask us to correct inaccurate or incomplete data.</li>
          <li><strong>Deletion:</strong> Request that we delete your personal data (subject to legal retention requirements).</li>
          <li><strong>Withdraw consent:</strong> Opt out of marketing emails at any time via the unsubscribe link or by emailing us.</li>
          <li><strong>Portability:</strong> Receive your data in a machine-readable format.</li>
          <li><strong>Restriction:</strong> Ask us to restrict processing of your data in certain circumstances.</li>
        </ul>
        <p>To exercise any right, email <a href="mailto:<?= $siteEmail ?>" style="color:var(--gyc-green-600);"><?= $siteEmail ?></a>. We will respond within 30 days.</p>
      </div>

      <div class="policy-section" id="children">
        <h2>7. Children's Privacy</h2>
        <p>Our website and services are not directed at children under 13. We do not knowingly collect personal data from children under 13. If we learn that we have inadvertently collected such data, we will promptly delete it. If you believe we hold data about a child, please contact us immediately.</p>
      </div>

      <div class="policy-section" id="changes">
        <h2>8. Changes to This Policy</h2>
        <p>We may update this Privacy Policy periodically. Material changes will be notified via email or a prominent notice on our website. The "Last updated" date at the top of this page reflects the most recent revision. Continued use of our services after a change constitutes acceptance.</p>
      </div>

      <div class="policy-section" id="contact-us">
        <h2>9. Contact Us</h2>
        <p>Questions about this Privacy Policy or how we handle your data?</p>
        <ul>
          <li><strong>Email:</strong> <a href="mailto:<?= $siteEmail ?>" style="color:var(--gyc-green-600);"><?= $siteEmail ?></a></li>
          <li><strong>Address:</strong> <?= htmlspecialchars($siteAddress) ?></li>
          <li><strong>WhatsApp:</strong> <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('site_whatsapp') ?: SITE_WHATSAPP) ?>" target="_blank" rel="noopener" style="color:var(--gyc-green-600);">Chat with us</a></li>
        </ul>
      </div>

    </article>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
