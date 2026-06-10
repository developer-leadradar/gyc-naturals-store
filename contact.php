<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/email-templates.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    // ── Rate limit: max 3 contact submissions per IP per hour ──
    $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ipHash   = hash('sha256', $ip);   // store hash, not raw IP (GDPR-friendly)
    $recentCount = (int)(getDB()->fetchOne(
        "SELECT COUNT(*) c FROM contact_messages WHERE ip_hash = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
        [$ipHash]
    )['c'] ?? 0);

    if ($recentCount >= 3) {
        $error = 'Too many messages. Please wait a while before sending another.';
    } else {
        $name    = trim(sanitize($_POST['name']    ?? ''));
        $email   = trim(sanitize($_POST['email']   ?? ''));
        $subject = trim(sanitize($_POST['subject'] ?? ''));
        $message = trim(sanitize($_POST['message'] ?? ''));

        if (!$name || strlen($name) < 2) {
            $error = 'Please enter your full name.';
        } elseif (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (!$subject) {
            $error = 'Please select a subject.';
        } elseif (strlen($message) < 20) {
            $error = 'Your message must be at least 20 characters.';
        } else {
            // Store in DB
            getDB()->insert('contact_messages', [
                'name'       => $name,
                'email'      => $email,
                'subject'    => $subject,
                'message'    => $message,
                'ip_hash'    => $ipHash,
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Admin notification (plain text — internal)
            $adminEmail = getSetting('contact_email') ?: ADMIN_EMAIL;
            $adminBody  = emailWrapper(
                "New contact form message from {$name}",
                "<h1>📬 New Contact Message</h1>"
                . "<table class=\"info-table\"><tr><td>From</td><td>" . htmlspecialchars($name) . "</td></tr>"
                . "<tr><td>Email</td><td><a href=\"mailto:" . htmlspecialchars($email) . "\">" . htmlspecialchars($email) . "</a></td></tr>"
                . "<tr><td>Subject</td><td>" . htmlspecialchars($subject) . "</td></tr></table>"
                . "<h2>Message</h2><p style=\"background:#F8FAF9;padding:1rem;border-radius:8px;border-left:4px solid #16a34a;\">"
                . nl2br(htmlspecialchars($message)) . "</p>"
                . "<p style=\"font-size:.8rem;color:#9CA3AF;\">Sent via GYC Naturals contact form.</p>"
            );
            sendEmail($adminEmail, "Contact Form: {$subject} | GYC Naturals", $adminBody);

            // Branded acknowledgement to user
            $ackHtml = emailWrapper(
                "We got your message — GYC Naturals will reply within 24 hours",
                "<h1>✅ Message Received!</h1>"
                . "<p>Hi <strong>" . htmlspecialchars($name) . "</strong>,</p>"
                . "<p>Thank you for reaching out to GYC Naturals! We've received your message about <strong>"
                . htmlspecialchars($subject) . "</strong> and will get back to you within <strong>24 hours</strong>.</p>"
                . "<div class=\"alert-box alert-gold\">For urgent inquiries, please WhatsApp us directly — it's the fastest way to reach us.</div>"
                . "<p style=\"text-align:center;margin:28px 0;\"><a href=\"https://wa.me/" . preg_replace('/\D/','',SITE_WHATSAPP) . "\" class=\"btn-email\">💬 WhatsApp Us</a></p>"
                . "<hr class=\"divider\"><p style=\"font-size:.82rem;color:#6B7280;\">GYC Naturals · Big Qua Mall, Calabar · Mon–Sat 9am–7pm</p>"
            );
            sendEmail($email, 'We received your message — GYC Naturals', $ackHtml);

            $success = 'Thank you! Your message has been sent. We\'ll reply within 24 hours.';
        }
    }
}

$pageTitle       = 'Contact Us — GYC Naturals';
$pageDescription = 'Get in touch with GYC Naturals — Calabar hair braiding salon at Big Qua Mall, Ediba Road. WhatsApp, email, or visit us in Cross River State.';
require_once __DIR__ . '/includes/header.php';

$waPhone   = getSetting('site_whatsapp')   ?: SITE_WHATSAPP;
$waClean   = preg_replace('/[^0-9]/', '', $waPhone);
$address   = getSetting('site_address')    ?: 'Big Qua Mall, Ediba Road, Off Big Qua Town by Marian Market, Calabar, Cross River State';
$phoneNum  = getSetting('site_phone')      ?: '+234 XXX XXX XXXX';
$emailAddr = getSetting('contact_email')   ?: 'hello@gycnaturals.com';
$openHours = getSetting('opening_hours')   ?: 'Mon–Sat: 8:00 AM – 7:00 PM | Sun: 10:00 AM – 5:00 PM';
?>

<div style="min-height:72px;"></div>

<!-- HERO -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:5rem 0 4rem;color:#fff;text-align:center;">
  <div class="container" style="max-width:640px;">
    <p style="font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">Reach Out</p>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3rem);margin-bottom:1.25rem;">We'd Love to Hear From You</h1>
    <p style="opacity:.85;font-size:1rem;line-height:1.7;">Whether you want to book an appointment, ask about a product, or just say hello — we're here.</p>
  </div>
</section>

<!-- QUICK CONTACT CARDS -->
<section style="background:#fff;padding:2.5rem 0;border-bottom:1px solid var(--gyc-green-100);">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;">
      <?php $contacts = [
        ['message-circle','WhatsApp','Fastest response','btn-whatsapp','https://wa.me/'.$waClean],
        ['phone','Call Us',$phoneNum,'btn-outline-green','tel:'.$phoneNum],
        ['mail','Email Us',$emailAddr,'btn-outline-green','mailto:'.$emailAddr],
        ['map-pin','Visit Us','Big Qua Mall, Calabar','btn-outline-green','https://maps.google.com/?q=Big+Qua+Mall+Calabar+Cross+River+State+Nigeria'],
      ];
      foreach ($contacts as $c): ?>
      <a href="<?= htmlspecialchars($c[4]) ?>" target="_blank" rel="noopener"
         style="display:flex;flex-direction:column;align-items:center;padding:1.5rem 1rem;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);text-align:center;text-decoration:none;transition:box-shadow .2s;gap:.5rem;"
         onmouseover="this.style.boxShadow='var(--gyc-shadow-lg)'" onmouseout="this.style.boxShadow=''">
        <i data-lucide="<?= $c[0] ?>" style="width:24px;height:24px;color:var(--gyc-green-600);"></i>
        <div style="font-weight:700;font-size:.88rem;color:var(--gyc-dark);"><?= htmlspecialchars($c[1]) ?></div>
        <div style="font-size:.75rem;color:#6B7280;"><?= htmlspecialchars($c[2]) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- MAIN CONTENT -->
<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 420px;gap:3rem;align-items:start;">

      <!-- CONTACT FORM -->
      <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-xl);padding:2.5rem;">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.5rem;">Send Us a Message</h2>
        <p style="font-size:.87rem;color:#6B7280;margin-bottom:2rem;">We reply within 24 hours on business days.</p>

        <?php if ($success): ?>
        <div class="alert alert-success" style="margin-bottom:1.5rem;">
          <i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <?= htmlspecialchars($success) ?>
        </div>
        <?php elseif ($error): ?>
        <div class="alert alert-danger" style="margin-bottom:1.5rem;">
          <i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i>
          <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
          <?= csrfInput() ?>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
              <label class="form-label">Your Name <span style="color:var(--gyc-terra);">*</span></label>
              <input type="text" name="name" class="form-control" required placeholder="Full name"
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Email Address <span style="color:var(--gyc-terra);">*</span></label>
              <input type="email" name="email" class="form-control" required placeholder="you@example.com"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Subject <span style="color:var(--gyc-terra);">*</span></label>
            <select name="subject" class="form-control" required>
              <option value="">— Select a subject —</option>
              <option value="Appointment Enquiry" <?= ($_POST['subject'] ?? '') === 'Appointment Enquiry' ? 'selected' : '' ?>>Appointment Enquiry</option>
              <option value="Product Question"    <?= ($_POST['subject'] ?? '') === 'Product Question'    ? 'selected' : '' ?>>Product Question</option>
              <option value="Order Support"       <?= ($_POST['subject'] ?? '') === 'Order Support'       ? 'selected' : '' ?>>Order Support</option>
              <option value="Refund / Return"     <?= ($_POST['subject'] ?? '') === 'Refund / Return'     ? 'selected' : '' ?>>Refund / Return</option>
              <option value="Partnership"         <?= ($_POST['subject'] ?? '') === 'Partnership'         ? 'selected' : '' ?>>Partnership / Wholesale</option>
              <option value="Press / Media"       <?= ($_POST['subject'] ?? '') === 'Press / Media'       ? 'selected' : '' ?>>Press / Media</option>
              <option value="General"             <?= ($_POST['subject'] ?? '') === 'General'             ? 'selected' : '' ?>>General Enquiry</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Your Message <span style="color:var(--gyc-terra);">*</span></label>
            <textarea name="message" class="form-control" rows="6" required minlength="20"
                      placeholder="Tell us how we can help…"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-green w-full" style="font-size:1rem;">
            <i data-lucide="send" style="width:16px;height:16px;"></i>
            Send Message
          </button>
        </form>
      </div>

      <!-- INFO SIDEBAR -->
      <div style="display:flex;flex-direction:column;gap:1.5rem;">

        <!-- Location card -->
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;">
          <!-- Google Maps embed -->
          <div style="position:relative;overflow:hidden;height:220px;">
            <iframe
              src="https://maps.google.com/maps?q=Big+Qua+Mall,Ediba+Road,Calabar,Cross+River+State,Nigeria&t=&z=15&ie=UTF8&iwloc=&output=embed"
              width="100%" height="220" frameborder="0" scrolling="no"
              style="border:0;display:block;"
              title="GYC Naturals location — Big Qua Mall, Calabar"
              allowfullscreen loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
            <a href="https://maps.google.com/?q=Big+Qua+Mall,Ediba+Road,Calabar,Cross+River+State,Nigeria"
               target="_blank" rel="noopener"
               style="position:absolute;bottom:.6rem;right:.6rem;z-index:2;"
               class="btn btn-green btn-sm" style="font-size:.75rem;">Open in Maps</a>
          </div>
          <div style="padding:1.5rem;">
            <h3 style="font-family:'Playfair Display',serif;font-size:1rem;margin-bottom:1rem;">Our Salon</h3>
            <div style="display:flex;flex-direction:column;gap:.75rem;">
              <div style="display:flex;gap:.75rem;align-items:flex-start;">
                <i data-lucide="map-pin" style="width:16px;height:16px;color:var(--gyc-green-600);flex-shrink:0;margin-top:.1rem;"></i>
                <span style="font-size:.85rem;color:#374151;"><?= htmlspecialchars($address) ?></span>
              </div>
              <div style="display:flex;gap:.75rem;align-items:flex-start;">
                <i data-lucide="phone" style="width:16px;height:16px;color:var(--gyc-green-600);flex-shrink:0;"></i>
                <a href="tel:<?= htmlspecialchars($phoneNum) ?>" style="font-size:.85rem;color:#374151;text-decoration:none;"><?= htmlspecialchars($phoneNum) ?></a>
              </div>
              <div style="display:flex;gap:.75rem;align-items:flex-start;">
                <i data-lucide="mail" style="width:16px;height:16px;color:var(--gyc-green-600);flex-shrink:0;"></i>
                <a href="mailto:<?= htmlspecialchars($emailAddr) ?>" style="font-size:.85rem;color:#374151;text-decoration:none;"><?= htmlspecialchars($emailAddr) ?></a>
              </div>
              <div style="display:flex;gap:.75rem;align-items:flex-start;">
                <i data-lucide="clock" style="width:16px;height:16px;color:var(--gyc-green-600);flex-shrink:0;margin-top:.1rem;"></i>
                <span style="font-size:.85rem;color:#374151;"><?= htmlspecialchars($openHours) ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- WhatsApp CTA -->
        <a href="<?= htmlspecialchars(whatsappMessage($waPhone, 'Hi GYC Naturals! I have a question and would like to speak with someone.')) ?>"
           target="_blank" rel="noopener"
           style="display:flex;align-items:center;gap:1.25rem;background:#25D366;color:#fff;border-radius:var(--gyc-radius-lg);padding:1.5rem;text-decoration:none;">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          <div>
            <div style="font-weight:700;font-size:.95rem;margin-bottom:.15rem;">Chat on WhatsApp</div>
            <div style="font-size:.78rem;opacity:.85;">Fastest way to reach us — usually respond in minutes</div>
          </div>
        </a>

        <!-- FAQ hint -->
        <div style="background:var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.25rem;">
          <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;">
            <i data-lucide="help-circle" style="width:18px;height:18px;color:var(--gyc-green-600);"></i>
            <span style="font-weight:700;font-size:.9rem;color:var(--gyc-dark);">Got a common question?</span>
          </div>
          <p style="font-size:.82rem;color:#374151;margin-bottom:.75rem;">Check our FAQ page — you might get an instant answer.</p>
          <a href="<?= SITE_URL ?>/faq.php" class="btn btn-outline-green btn-sm">View FAQs</a>
        </div>

      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
