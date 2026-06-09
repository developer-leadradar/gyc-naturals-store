<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$aptNum = sanitize($_GET['apt'] ?? '');
$payRef = sanitize($_GET['ref'] ?? '');

if (empty($aptNum)) {
    redirect(SITE_URL . '/book-appointment.php');
}

$appointment = getAppointmentByNumber($aptNum);

if (!$appointment) {
    redirect(SITE_URL . '/book-appointment.php');
}

// If Paystack ref provided, verify payment
$depositVerified = false;
$depositAmount   = 0;

if ($payRef && PAYSTACK_SECRET_KEY) {
    $ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($payRef));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . PAYSTACK_SECRET_KEY],
        CURLOPT_TIMEOUT        => 15,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    if ($raw) {
        $pay = json_decode($raw, true);
        if (!empty($pay['status']) && $pay['data']['status'] === 'success') {
            $depositAmount   = (int)($pay['data']['amount'] ?? 0) / 100;
            $depositVerified = true;
            // Update appointment with deposit info
            getDB()->update('appointments', [
                'deposit_paid'   => 1,
                'deposit_amount' => $depositAmount,
                'paystack_ref'   => $payRef,
                'status'         => 'confirmed',
                'confirmed_at'   => date('Y-m-d H:i:s'),
            ], 'appointment_number = ?', [$aptNum]);
            // Refresh appointment data
            $appointment = getAppointmentByNumber($aptNum);
        }
    }
}

// Admin WhatsApp notification URL
$waPhone   = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
$waAdminMsg = "New Booking Confirmed!\n"
            . "Ref: {$aptNum}\n"
            . "Name: {$appointment['customer_name']}\n"
            . "Phone: {$appointment['customer_phone']}\n"
            . "Date: " . date('D jS M Y', strtotime($appointment['requested_date']))
            . ($appointment['requested_time'] ? " at " . date('g:i A', strtotime($appointment['requested_time'])) : '') . "\n"
            . "Style: " . ($appointment['style_name'] ?? 'To be decided') . "\n"
            . ($depositVerified ? "Deposit: ₦" . number_format($depositAmount) . " PAID ✓" : "Deposit: Pending");

$waAdminUrl    = whatsappMessage($waPhone, $waAdminMsg);

// Customer WhatsApp (self-send reminder)
$custWaMsg     = "Hi! I just booked an appointment at GYC Naturals.\n"
               . "Booking Ref: {$aptNum}\n"
               . "Date: " . date('D jS M Y', strtotime($appointment['requested_date'])) . "\n"
               . "Looking forward to it!";
$custWaUrl     = "https://wa.me/?text=" . rawurlencode($custWaMsg);

$pageTitle       = 'Booking Received — ' . $aptNum . ' | GYC Naturals';
$pageDescription = 'Your appointment request has been received at GYC Naturals Lagos. We will confirm within 24 hours.';

require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<!-- Confirmation Hero -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:4.5rem 0 3rem;text-align:center;color:#fff;">
  <div class="container">
    <?php if ($depositVerified): ?>
    <div style="width:72px;height:72px;border-radius:50%;background:var(--gyc-gold-500);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
      <i data-lucide="check" style="width:36px;height:36px;color:var(--gyc-dark);stroke-width:3;"></i>
    </div>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.8rem);color:#fff;margin:0 0 0.75rem;">
      Appointment Confirmed!
    </h1>
    <p style="color:rgba(255,255,255,0.82);max-width:480px;margin:0 auto;font-size:1rem;line-height:1.65;">
      Your deposit has been received. We will WhatsApp you with full confirmation details shortly.
    </p>
    <?php else: ?>
    <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.4);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
      <i data-lucide="calendar-check" style="width:36px;height:36px;color:#fff;"></i>
    </div>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,2.8rem);color:#fff;margin:0 0 0.75rem;">
      Booking Received!
    </h1>
    <p style="color:rgba(255,255,255,0.82);max-width:480px;margin:0 auto;font-size:1rem;line-height:1.65;">
      Your appointment request has been sent. We will reach out within 24 hours to confirm your date and time.
    </p>
    <?php endif; ?>
    <div style="display:inline-block;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.3);border-radius:var(--gyc-radius);padding:0.6rem 1.5rem;margin-top:1.5rem;">
      <span style="font-size:0.78rem;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.7);">Booking Reference</span>
      <div style="font-family:'Playfair Display',serif;font-size:1.4rem;color:#fff;font-weight:600;letter-spacing:0.04em;"><?= htmlspecialchars($aptNum) ?></div>
    </div>
  </div>
</section>

<!-- Details + Actions -->
<section style="padding:3.5rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div style="max-width:720px;margin:0 auto;">

      <!-- Summary card -->
      <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;margin-bottom:2rem;box-shadow:0 2px 12px rgba(0,0,0,0.04);">
        <?php if ($appointment['style_image']): ?>
        <div style="height:200px;overflow:hidden;background:var(--gyc-green-100);">
          <img src="<?= htmlspecialchars($appointment['style_image']) ?>"
               alt="<?= htmlspecialchars($appointment['style_name'] ?? '') ?>"
               style="width:100%;height:100%;object-fit:cover;">
        </div>
        <?php endif; ?>

        <div style="padding:1.75rem 2rem;">
          <h2 style="font-family:'Playfair Display',serif;font-size:1.25rem;margin-bottom:1.5rem;color:var(--gyc-dark);">Appointment Summary</h2>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
            <div>
              <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Reference</span>
              <div style="font-size:1rem;font-weight:700;color:var(--gyc-dark);margin-top:3px;"><?= htmlspecialchars($aptNum) ?></div>
            </div>
            <div>
              <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Status</span>
              <div style="margin-top:3px;">
                <?php
                $statusMap = [
                    'pending'   => ['Pending Confirmation', '#F59E0B', 'clock'],
                    'confirmed' => ['Confirmed', 'var(--gyc-green-600)', 'check-circle'],
                    'cancelled' => ['Cancelled', '#EF4444', 'x-circle'],
                    'completed' => ['Completed', '#6366F1', 'star'],
                ];
                $st = $statusMap[$appointment['status']] ?? ['Unknown', '#888', 'circle'];
                ?>
                <span style="display:inline-flex;align-items:center;gap:0.35rem;font-size:0.9rem;font-weight:600;color:<?= $st[1] ?>;">
                  <i data-lucide="<?= $st[2] ?>" style="width:16px;height:16px;"></i>
                  <?= $st[0] ?>
                </span>
              </div>
            </div>
            <div>
              <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Requested Date</span>
              <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:3px;">
                <?= date('l, jS F Y', strtotime($appointment['requested_date'])) ?>
              </div>
            </div>
            <div>
              <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Time</span>
              <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:3px;">
                <?= $appointment['requested_time']
                    ? date('g:i A', strtotime($appointment['requested_time']))
                    : 'To be confirmed' ?>
              </div>
            </div>
            <?php if ($appointment['style_name']): ?>
            <div>
              <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Style Requested</span>
              <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:3px;"><?= htmlspecialchars($appointment['style_name']) ?></div>
            </div>
            <?php endif; ?>
            <div>
              <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Name</span>
              <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:3px;"><?= htmlspecialchars($appointment['customer_name']) ?></div>
            </div>
            <?php if ($appointment['customer_phone']): ?>
            <div>
              <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Phone</span>
              <div style="font-size:1rem;font-weight:600;color:var(--gyc-dark);margin-top:3px;"><?= htmlspecialchars($appointment['customer_phone']) ?></div>
            </div>
            <?php endif; ?>
            <?php if ($depositVerified): ?>
            <div>
              <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Deposit Paid</span>
              <div style="font-size:1rem;font-weight:700;color:var(--gyc-green-600);margin-top:3px;">
                <?= formatPrice($depositAmount) ?> ✓
              </div>
            </div>
            <?php endif; ?>
          </div>

          <?php if ($appointment['customer_notes']): ?>
          <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--gyc-green-100);">
            <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Your Notes</span>
            <div style="font-size:0.9rem;color:#555;margin-top:4px;"><?= nl2br(htmlspecialchars($appointment['customer_notes'])) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Action buttons -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2.5rem;">
        <!-- Notify admin via WhatsApp -->
        <a href="<?= htmlspecialchars($waAdminUrl) ?>" target="_blank" rel="noopener"
           class="btn btn-whatsapp btn-lg" style="justify-content:center;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          Notify GYC Naturals
        </a>
        <a href="<?= htmlspecialchars($custWaUrl) ?>" target="_blank" rel="noopener"
           style="display:flex;align-items:center;justify-content:center;gap:0.5rem;background:#fff;border:1.5px solid var(--gyc-green-200);color:var(--gyc-dark);border-radius:var(--gyc-radius);padding:0.85rem 1.25rem;font-weight:600;font-size:0.9rem;text-decoration:none;">
          <i data-lucide="share-2" style="width:18px;height:18px;color:var(--gyc-green-600);"></i>
          Save Booking to WhatsApp
        </a>
      </div>

      <!-- What happens next -->
      <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem 2rem;margin-bottom:2rem;">
        <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;color:var(--gyc-dark);">What Happens Next</h3>
        <ol style="list-style:none;padding:0;display:flex;flex-direction:column;gap:1rem;counter-reset:steps;">
          <?php
          $nextSteps = [
            ['check-circle', 'var(--gyc-green-600)', 'Booking received', 'Your request is in our system with reference <strong>' . htmlspecialchars($aptNum) . '</strong>.'],
            ['phone', '#F59E0B', 'We contact you within 24 hours', 'Our team will call or WhatsApp you on <strong>' . htmlspecialchars($appointment['customer_phone']) . '</strong> to confirm the exact date and time.'],
            ['credit-card', 'var(--gyc-gold-600)', $depositVerified ? 'Deposit received — slot secured' : 'Deposit secures your slot', $depositVerified ? 'Your ₦' . number_format($depositAmount) . ' deposit has been received. Your appointment is confirmed.' : 'A 30% deposit will be requested to lock in your time slot.'],
            ['scissors', 'var(--gyc-green-700)', 'Arrive at the salon', 'Come in with clean, detangled hair. Bring any hair products you prefer us to use.'],
          ];
          foreach ($nextSteps as $i => $ns):
          ?>
          <li style="display:flex;gap:1rem;align-items:flex-start;">
            <span style="width:32px;height:32px;border-radius:50%;background:<?= $ns[1] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i data-lucide="<?= $ns[0] ?>" style="width:16px;height:16px;color:#fff;stroke-width:2.5;"></i>
            </span>
            <div>
              <strong style="font-size:0.92rem;color:var(--gyc-dark);display:block;"><?= $ns[2] ?></strong>
              <span style="font-size:0.83rem;color:#666;line-height:1.5;"><?= $ns[3] ?></span>
            </div>
          </li>
          <?php endforeach; ?>
        </ol>
      </div>

      <!-- Salon location -->
      <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem 2rem;margin-bottom:2rem;">
        <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;color:var(--gyc-dark);">Salon Location</h3>
        <div style="display:flex;align-items:flex-start;gap:1.25rem;flex-wrap:wrap;">
          <div style="flex:1;min-width:200px;">
            <div style="display:flex;align-items:flex-start;gap:0.75rem;margin-bottom:0.75rem;">
              <i data-lucide="map-pin" style="width:18px;height:18px;color:var(--gyc-green-600);flex-shrink:0;margin-top:2px;"></i>
              <div>
                <strong style="display:block;font-size:0.9rem;color:var(--gyc-dark);">GYC Naturals Salon</strong>
                <span style="font-size:0.85rem;color:#666;"><?= htmlspecialchars(getSetting('site_address') ?: 'Victoria Island, Lagos, Nigeria') ?></span>
              </div>
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
              <i data-lucide="clock" style="width:18px;height:18px;color:var(--gyc-green-600);flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:#666;">Monday–Saturday: 9:00 AM – 5:00 PM</span>
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;">
              <i data-lucide="phone" style="width:18px;height:18px;color:var(--gyc-green-600);flex-shrink:0;"></i>
              <span style="font-size:0.85rem;color:#666;"><?= htmlspecialchars(getSetting('site_phone') ?: SITE_PHONE) ?></span>
            </div>
          </div>
          <div>
            <a href="https://maps.google.com/?q=Victoria+Island+Lagos+Nigeria" target="_blank" rel="noopener"
               class="btn btn-outline-green btn-sm">
              <i data-lucide="navigation" style="width:14px;height:14px;"></i>
              Get Directions
            </a>
          </div>
        </div>
      </div>

      <!-- CTAs -->
      <div style="display:flex;gap:0.75rem;flex-wrap:wrap;justify-content:center;">
        <a href="<?= SITE_URL ?>/" class="btn btn-green">
          <i data-lucide="home" style="width:18px;height:18px;"></i>
          Back to Home
        </a>
        <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-outline-green">Explore More Styles</a>
        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline-green">
          <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
          Shop Products
        </a>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
