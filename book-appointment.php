<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Pre-selected style from URL
$preStyleId    = (int)($_GET['style_id'] ?? 0);
$preStyle      = $preStyleId ? getDB()->fetchOne("SELECT * FROM gallery_images WHERE id=? AND is_active=1", [$preStyleId]) : null;
$preService    = sanitize($_GET['service'] ?? '');
$validServices = ['braiding', 'kids', 'natural', 'treatment'];
$defaultSvc    = in_array($preService, $validServices) ? $preService : 'braiding';

// Gallery data grouped by service type for the booking style chooser
$validBookSvcs = ['braiding','kids','natural','treatment'];
$categoriesBySvc = [];
$stylesBySvc     = [];
foreach ($validBookSvcs as $sv) {
    $categoriesBySvc[$sv] = getAllGalleryCategories(true, $sv);
    $stylesBySvc[$sv]     = getGalleryImages(['service_type' => $sv], 80);
}
$categories = $categoriesBySvc[$defaultSvc] ?? [];
$allStyles  = $stylesBySvc[$defaultSvc] ?? [];

// Min date = tomorrow
$minDate = date('Y-m-d', strtotime('+1 day'));
$maxDate = date('Y-m-d', strtotime('+60 days'));

$pageTitle       = 'Book an Appointment — GYC Naturals Calabar';
$pageDescription = 'Book your professional hair braiding appointment online at GYC Naturals, Big Qua Mall, Calabar. Choose your style, pick a date, and confirm with a 30% deposit.';

require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<!-- Hero -->
<div style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:3.5rem 0 2rem;text-align:center;color:#fff;">
  <div class="container">
    <span class="section-eyebrow" style="color:var(--gyc-gold-300);">Online Booking</span>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,3rem);color:#fff;margin:0.5rem 0 0.75rem;">Book Your Appointment</h1>
    <p style="color:rgba(255,255,255,0.75);max-width:480px;margin:0 auto;font-size:0.95rem;">
      Choose your style, pick a date, and confirm with a 30% deposit. Easy, online, and takes under 3 minutes.
    </p>
  </div>
</div>

<!-- Booking form -->
<section style="padding:2.5rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:2.5rem;align-items:start;max-width:1000px;margin:0 auto;">

      <!-- 3-step form -->
      <div>
        <!-- Step indicators -->
        <div class="booking-stepper" style="display:flex;align-items:center;margin-bottom:2.5rem;">
          <?php
          $stepLabels = ['Choose Style', 'Pick Date & Time', 'Your Details'];
          foreach ($stepLabels as $si => $label):
          ?>
          <div class="booking-step-item <?= $si === 0 ? 'active' : '' ?>" data-step="<?= $si ?>">
            <div style="display:flex;align-items:center;gap:0.5rem;">
              <span style="width:28px;height:28px;border-radius:50%;background:<?= $si === 0 ? 'var(--gyc-green-700)' : 'var(--gyc-green-100)' ?>;color:<?= $si === 0 ? '#fff' : 'var(--gyc-green-700)' ?>;font-size:0.8rem;font-weight:700;display:flex;align-items:center;justify-content:center;"><?= $si+1 ?></span>
              <span style="font-size:0.82rem;font-weight:600;color:var(--gyc-dark);"><?= htmlspecialchars($label) ?></span>
            </div>
          </div>
          <?php if ($si < 2): ?>
          <div class="booking-step-line" style="flex:1;height:1px;background:var(--gyc-green-100);margin:0 0.75rem;"></div>
          <?php endif; ?>
          <?php endforeach; ?>
        </div>

        <form method="POST" action="<?= SITE_URL ?>/api/create-booking.php" id="booking-form" novalidate>
          <?= csrfInput() ?>
          <input type="hidden" name="gallery_image_id" id="booking-style-id" value="<?= $preStyleId ?: '' ?>">
          <input type="hidden" name="slot_id"   id="booking-slot-id" value="">
          <input type="hidden" name="date"       id="booking-date-hidden" value="">
          <input type="hidden" name="time"       id="booking-time" value="">

          <!-- PANEL 0: Choose Style -->
          <div class="booking-panel active" id="booking-panel-0">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.35rem;margin-bottom:0.5rem;">What Are You Booking?</h2>
            <p style="font-size:0.85rem;color:#666;margin-bottom:1.25rem;">Choose a service type, then pick your style below</p>

            <!-- Service type selector -->
            <input type="hidden" name="service_type" id="booking-service-type" value="<?= $defaultSvc ?>">
            <?php
            $serviceTypes = [
              ['id'=>'braiding',   'icon'=>'layers',     'label'=>'Braiding & Protective',  'desc'=>'Knotless, box braids, cornrows, twists & more'],
              ['id'=>'kids',       'icon'=>'users',      'label'=>'Kids\' Hair',             'desc'=>'Gentle styles for children'],
              ['id'=>'natural',    'icon'=>'flower-2',   'label'=>'Natural Styles',          'desc'=>'Bantu knots, afro puffs, flat twists, wash & go'],
              ['id'=>'treatment',  'icon'=>'droplets',   'label'=>'Scalp & Treatments',      'desc'=>'Deep conditioning, scalp detox & hair spa'],
            ];
            ?>
            <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.75rem;margin-bottom:1.5rem;" id="service-type-grid">
              <?php foreach ($serviceTypes as $svc): ?>
              <button type="button" class="svc-type-btn <?= $svc['id'] === $defaultSvc ? 'svc-type-btn--active' : '' ?>"
                      data-svc="<?= $svc['id'] ?>"
                      onclick="selectServiceType('<?= $svc['id'] ?>', this)"
                      style="display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;background:#fff;border:1.5px solid <?= $svc['id']===$defaultSvc ? 'var(--gyc-green-600)' : 'var(--gyc-green-100)' ?>;border-radius:var(--gyc-radius-lg);cursor:pointer;text-align:left;width:100%;transition:border-color .15s,box-shadow .15s;">
                <span style="width:38px;height:38px;border-radius:10px;background:<?= $svc['id']===$defaultSvc ? 'var(--gyc-green-100)' : '#F3F4F6' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                  <i data-lucide="<?= $svc['icon'] ?>" style="width:18px;height:18px;color:<?= $svc['id']===$defaultSvc ? 'var(--gyc-green-700)' : '#6B7280' ?>;"></i>
                </span>
                <span>
                  <span style="display:block;font-size:.85rem;font-weight:700;color:var(--gyc-dark);"><?= $svc['label'] ?></span>
                  <span style="display:block;font-size:.73rem;color:#888;line-height:1.3;"><?= $svc['desc'] ?></span>
                </span>
              </button>
              <?php endforeach; ?>
            </div>
            <script>
            function selectServiceType(svc, btn) {
              document.querySelectorAll('.svc-type-btn').forEach(function(b) {
                b.style.borderColor = 'var(--gyc-green-100)';
                b.style.boxShadow = '';
                var iconBg = b.querySelector('span:first-child');
                if (iconBg) iconBg.style.background = '#F3F4F6';
                var iconEl = b.querySelector('i') || b.querySelector('svg');
                if (iconEl) iconEl.style.color = '#6B7280';
              });
              btn.style.borderColor = 'var(--gyc-green-600)';
              btn.style.boxShadow = '0 0 0 3px rgba(22,101,52,0.1)';
              var activeIconBg = btn.querySelector('span:first-child');
              if (activeIconBg) activeIconBg.style.background = 'var(--gyc-green-100)';
              var activeIcon = btn.querySelector('i') || btn.querySelector('svg');
              if (activeIcon) activeIcon.style.color = 'var(--gyc-green-700)';
              document.getElementById('booking-service-type').value = svc;
              // Swap the per-service gallery block (chips + style grid)
              document.querySelectorAll('.svc-gallery-block').forEach(function(blk) {
                blk.style.display = (blk.getAttribute('data-svc') === svc) ? 'block' : 'none';
              });
              // Clear current style selection — user must pick again per service
              document.querySelectorAll('input[name="gallery_image_id"]').forEach(function(r) { r.checked = false; });
              var firstRadio = document.querySelector('.svc-gallery-block[data-svc="' + svc + '"] input[name="gallery_image_id"][value="0"]');
              if (firstRadio) firstRadio.checked = true;
              var styleHidden = document.getElementById('booking-style-id');
              if (styleHidden) styleHidden.value = '0';
            }

            function filterStylesIn(svc, slug, chipBtn) {
              var block = document.getElementById('svc-gallery-' + svc);
              if (!block) return;
              block.querySelectorAll('.svc-filter-row .chip').forEach(function(c) { c.classList.remove('chip--active'); });
              chipBtn.classList.add('chip--active');
              block.querySelectorAll('.style-selector-item').forEach(function(item) {
                if (item.classList.contains('style-selector-item--decide')) return;
                var match = (slug === 'all') || (item.getAttribute('data-cat-slug') === slug);
                item.style.display = match ? '' : 'none';
              });
            }
            </script>
            <!-- Per-service gallery (filter chips + style grid) — one block per service type, JS toggles visibility -->
            <?php foreach ($validBookSvcs as $svKey):
              $svCats   = $categoriesBySvc[$svKey] ?? [];
              $svStyles = $stylesBySvc[$svKey]     ?? [];
              $isActive = ($svKey === $defaultSvc);
            ?>
            <div class="svc-gallery-block" data-svc="<?= $svKey ?>" id="svc-gallery-<?= $svKey ?>" style="display:<?= $isActive ? 'block' : 'none' ?>;">

              <?php if (!empty($svCats)): ?>
              <div class="svc-filter-row" style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.25rem;overflow-x:auto;">
                <button type="button" class="chip chip--active" onclick="filterStylesIn('<?= $svKey ?>','all',this)">All</button>
                <?php foreach ($svCats as $cat): ?>
                <button type="button" class="chip" onclick="filterStylesIn('<?= $svKey ?>','<?= htmlspecialchars($cat['slug']) ?>',this)">
                  <?= htmlspecialchars($cat['name']) ?>
                </button>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>

              <div class="style-selector-grid">
                <label class="style-selector-item style-selector-item--decide">
                  <input type="radio" name="gallery_image_id" value="0" <?= ($isActive && !$preStyleId) ? 'checked' : '' ?>>
                  <div class="style-selector-card" style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:0.5rem;background:var(--gyc-green-100);min-height:120px;">
                    <i data-lucide="help-circle" style="width:28px;height:28px;color:var(--gyc-green-700);"></i>
                    <span style="font-size:0.85rem;font-weight:600;color:var(--gyc-green-700);text-align:center;">I'll decide in person</span>
                  </div>
                </label>
                <?php foreach ($svStyles as $style): ?>
                <label class="style-selector-item" data-cat-slug="<?= htmlspecialchars($style['category_slug'] ?? '') ?>">
                  <input type="radio" name="gallery_image_id" value="<?= $style['id'] ?>" <?= $preStyleId == $style['id'] ? 'checked' : '' ?>>
                  <div class="style-selector-card">
                    <img src="<?= htmlspecialchars($style['image_url']) ?>" alt="<?= htmlspecialchars($style['title']) ?>" loading="lazy" style="width:100%;height:110px;object-fit:cover;border-radius:8px 8px 0 0;">
                    <div style="padding:0.5rem;font-size:0.75rem;font-weight:600;text-align:center;line-height:1.3;">
                      <?= htmlspecialchars($style['title']) ?>
                      <?php if ($style['price_from']): ?><br><span style="color:var(--gyc-green-600);font-size:0.7rem;">from <?= formatPrice($style['price_from']) ?></span><?php endif; ?>
                    </div>
                  </div>
                </label>
                <?php endforeach; ?>
                <?php if (empty($svStyles)): ?>
                <p style="grid-column:1/-1;text-align:center;color:#888;font-size:.85rem;padding:1rem;">No styles yet for this service. Pick "I'll decide in person".</p>
                <?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>

            <div style="margin-top:1.5rem;">
              <button type="button" class="btn btn-green btn-lg" data-booking-next style="width:100%;justify-content:center;">
                Continue to Date &amp; Time
                <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
              </button>
            </div>
          </div>

          <!-- PANEL 1: Date & Time -->
          <div class="booking-panel" id="booking-panel-1">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.35rem;margin-bottom:0.5rem;">Choose Date &amp; Time</h2>
            <p style="font-size:0.85rem;color:#666;margin-bottom:1.5rem;">Select an available date and time slot</p>

            <div class="form-group">
              <label class="form-label">Preferred Date <span class="required">*</span></label>
              <input type="date" id="booking-date" class="form-control" name="date_display"
                     min="<?= $minDate ?>" max="<?= $maxDate ?>"
                     style="max-width:280px;">
              <p class="form-hint">Appointments available Monday–Saturday, 9am–5pm</p>
            </div>

            <div id="time-slots-box" style="margin-top:1.25rem;min-height:80px;">
              <p style="font-size:0.85rem;color:#aaa;">Select a date to see available times</p>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:1.75rem;">
              <button type="button" class="btn btn-outline-green btn-lg" data-booking-back>
                <i data-lucide="arrow-left" style="width:18px;height:18px;"></i> Back
              </button>
              <button type="button" class="btn btn-green btn-lg" data-booking-next style="flex:1;justify-content:center;">
                Continue to Your Details
                <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
              </button>
            </div>
          </div>

          <!-- PANEL 2: Contact Details -->
          <div class="booking-panel" id="booking-panel-2">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.35rem;margin-bottom:0.5rem;">Your Details</h2>
            <p style="font-size:0.85rem;color:#666;margin-bottom:1.5rem;">We need your contact information to confirm the booking</p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
              <div class="form-group">
                <label class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" name="customer_name" class="form-control" placeholder="Your full name" required
                       value="<?= isLoggedIn() ? htmlspecialchars(getCurrentUser()['name'] ?? '') : '' ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Phone / WhatsApp <span class="required">*</span></label>
                <input type="tel" name="customer_phone" class="form-control" placeholder="+234 xxx xxx xxxx" required>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input type="email" name="customer_email" class="form-control" placeholder="For booking confirmation"
                     value="<?= isLoggedIn() ? htmlspecialchars(getCurrentUser()['email'] ?? '') : '' ?>">
            </div>

            <div class="form-group">
              <label class="form-label">Hair Notes (optional)</label>
              <textarea name="notes" class="form-control" rows="3"
                        placeholder="Any special instructions, allergies, or requests for your stylist..."></textarea>
            </div>

            <!-- Deposit notice -->
            <div class="alert alert-info" style="margin:0.5rem 0 1.25rem;">
              <i data-lucide="info" style="width:18px;height:18px;flex-shrink:0;"></i>
              <div>
                <strong>30% Deposit Required</strong><br>
                <span style="font-size:0.83rem;"><?= htmlspecialchars(getSetting('booking_deposit_note') ?: 'A 30% deposit is required to confirm your appointment.') ?></span>
              </div>
            </div>

            <div style="display:flex;gap:0.75rem;">
              <button type="button" class="btn btn-outline-green btn-lg" data-booking-back>
                <i data-lucide="arrow-left" style="width:18px;height:18px;"></i> Back
              </button>
              <button type="submit" class="btn btn-gold btn-lg" style="flex:1;justify-content:center;">
                <i data-lucide="calendar-check" style="width:18px;height:18px;"></i>
                Confirm Appointment
              </button>
            </div>
          </div>

        </form>
      </div>

      <!-- Sidebar summary -->
      <div>
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem;position:sticky;top:calc(var(--gyc-nav-height) + 1rem);">
          <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;color:var(--gyc-dark);">Booking Summary</h3>
          <div style="display:flex;flex-direction:column;gap:0.75rem;font-size:0.88rem;">
            <div style="display:flex;justify-content:space-between;gap:0.5rem;">
              <span style="color:#888;">Style:</span>
              <strong id="summary-style" style="text-align:right;"><?= $preStyle ? htmlspecialchars($preStyle['title']) : 'Not selected' ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;">
              <span style="color:#888;">Date:</span>
              <strong id="summary-date">—</strong>
            </div>
            <div style="display:flex;justify-content:space-between;">
              <span style="color:#888;">Time:</span>
              <strong id="summary-time">—</strong>
            </div>
            <?php if ($preStyle && $preStyle['price_from']): ?>
            <hr style="border:none;border-top:1px solid var(--gyc-green-100);margin:0.25rem 0;">
            <div style="display:flex;justify-content:space-between;">
              <span style="color:#888;">Starting from:</span>
              <strong style="color:var(--gyc-green-700);"><?= formatPrice($preStyle['price_from']) ?></strong>
            </div>
            <div style="display:flex;justify-content:space-between;">
              <span style="color:#888;">Deposit (30%):</span>
              <strong style="color:var(--gyc-gold-700);"><?= formatPrice($preStyle['price_from'] * 0.30) ?></strong>
            </div>
            <?php endif; ?>
          </div>

          <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gyc-green-100);">
            <h4 style="font-size:0.82rem;font-weight:600;color:var(--gyc-dark);margin-bottom:0.75rem;">What to expect:</h4>
            <ul style="list-style:none;padding:0;display:flex;flex-direction:column;gap:0.5rem;">
              <?php
              $steps2 = [
                ['calendar','Book online in 3 minutes'],
                ['credit-card','Pay 30% deposit to confirm'],
                ['message-circle','WhatsApp reminder sent 24hrs before'],
                ['scissors','Arrive with clean, detangled hair'],
              ];
              foreach ($steps2 as $s):
              ?>
              <li style="display:flex;align-items:flex-start;gap:0.5rem;font-size:0.8rem;color:#555;">
                <i data-lucide="<?= $s[0] ?>" style="width:15px;height:15px;color:var(--gyc-green-500);flex-shrink:0;margin-top:1px;"></i>
                <span><?= $s[1] ?></span>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<script src="<?= SITE_URL ?>/assets/js/booking.js"></script>
<script>
// Sync radio → hidden + summary
document.querySelectorAll('input[name="gallery_image_id"]').forEach(function (radio) {
  radio.addEventListener('change', function () {
    document.getElementById('booking-style-id').value = radio.value;
    const lbl   = radio.closest('label');
    const span  = lbl ? lbl.querySelector('span') : null;
    const sumEl = document.getElementById('summary-style');
    if (sumEl) sumEl.textContent = (span ? span.textContent.trim() : '') || (radio.value == 0 ? 'Decide in person' : 'Style selected');
  });
});

// Filter booking style grid
function filterBookingStyles(catSlug, btn) {
  document.querySelectorAll('[onclick*="filterBookingStyles"]').forEach(function(b) { b.classList.remove('chip--active'); });
  btn.classList.add('chip--active');
  document.querySelectorAll('.style-selector-item[data-cat-slug]').forEach(function (item) {
    item.style.display = (catSlug === 'all' || item.dataset.catSlug === catSlug) ? '' : 'none';
  });
}

// Sync date → hidden field
const dateInput2 = document.getElementById('booking-date');
if (dateInput2) {
  dateInput2.addEventListener('change', function () {
    document.getElementById('booking-date-hidden').value = dateInput2.value;
    const d = new Date(dateInput2.value + 'T00:00:00');
    const sumDate = document.getElementById('summary-date');
    if (sumDate) sumDate.textContent = d.toLocaleDateString('en-NG', {weekday:'short', day:'numeric', month:'short', year:'numeric'});
  });
}

// AJAX form submission with optional Paystack inline payment
document.getElementById('booking-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const form   = this;
  const btn    = form.querySelector('button[type="submit"]');
  const origTxt = btn.innerHTML;
  btn.disabled  = true;
  btn.innerHTML = '<span style="opacity:.7">Processing…</span>';

  const data = new FormData(form);

  fetch(form.action, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: data
  })
  .then(function(r) { return r.json(); })
  .then(function(res) {
    if (!res.success) {
      btn.disabled  = false;
      btn.innerHTML = origTxt;
      if (typeof lucide !== 'undefined') lucide.createIcons();
      showFormError(res.message || 'Something went wrong. Please try again.');
      return;
    }

    // If Paystack deposit available, open inline payment
    if (res.paystack && res.paystack.public_key) {
      const pk = res.paystack;
      const handler = PaystackPop.setup({
        key:       pk.public_key,
        email:     pk.email,
        amount:    pk.amount,
        currency:  pk.currency || 'NGN',
        ref:       pk.reference,
        metadata:  pk.metadata || {},
        callback: function(response) {
          // Verify + redirect
          window.location.href = res.redirect + '&ref=' + encodeURIComponent(response.reference);
        },
        onClose: function() {
          // Allow skipping deposit — redirect anyway
          if (confirm('Pay deposit now to secure your slot, or continue to request only?')) {
            window.location.href = res.redirect;
          } else {
            btn.disabled  = false;
            btn.innerHTML = origTxt;
            if (typeof lucide !== 'undefined') lucide.createIcons();
          }
        }
      });
      handler.openIframe();
    } else {
      window.location.href = res.redirect;
    }
  })
  .catch(function() {
    btn.disabled  = false;
    btn.innerHTML = origTxt;
    if (typeof lucide !== 'undefined') lucide.createIcons();
    showFormError('Network error. Please check your connection and try again.');
  });
});

function showFormError(msg) {
  let existing = document.getElementById('booking-form-error');
  if (!existing) {
    existing = document.createElement('div');
    existing.id = 'booking-form-error';
    existing.className = 'alert alert-danger';
    existing.style.cssText = 'margin-bottom:1rem;';
    const panel = document.querySelector('.booking-panel.active');
    if (panel) panel.prepend(existing);
  }
  existing.textContent = msg;
  existing.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
