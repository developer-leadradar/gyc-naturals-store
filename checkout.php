<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$summary = getCartSummary();
if ($summary['itemCount'] === 0) {
    redirect(SITE_URL . '/cart.php');
}

$subtotal = $summary['subtotal'];
$shipping = $summary['shipping'];
$total    = $summary['total'];
$items    = $summary['items'];

$user = isLoggedIn() ? getCurrentUser() : null;

$pageTitle = 'Checkout — GYC Naturals Calabar';
require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<section style="padding:2.5rem 0 5rem;">
  <div class="container">
    <h1 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:2rem;">Checkout</h1>

    <div class="checkout-grid">

      <!-- Checkout form -->
      <div>
        <form id="checkout-form" novalidate>
          <?= csrfInput() ?>

          <!-- Contact info -->
          <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem;margin-bottom:1.5rem;">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;">Contact Information</h2>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
              <div class="form-group">
                <label class="form-label">First Name <span class="required">*</span></label>
                <input type="text" name="shipping_first_name" class="form-control" required
                       value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Last Name <span class="required">*</span></label>
                <input type="text" name="shipping_last_name" class="form-control" required
                       value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Email Address <span class="required">*</span></label>
              <input type="email" name="customer_email" class="form-control" required
                     value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                     placeholder="For order confirmation">
            </div>
            <div class="form-group">
              <label class="form-label">Phone / WhatsApp <span class="required">*</span></label>
              <input type="tel" name="shipping_phone" class="form-control" required
                     placeholder="+234 xxx xxx xxxx">
            </div>
          </div>

          <!-- Shipping address -->
          <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem;margin-bottom:1.5rem;">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;">Shipping Address</h2>
            <div class="form-group">
              <label class="form-label">Street Address <span class="required">*</span></label>
              <input type="text" name="shipping_address" class="form-control" required
                     placeholder="House number, street name">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
              <div class="form-group">
                <label class="form-label">City <span class="required">*</span></label>
                <input type="text" name="shipping_city" class="form-control" required
                       placeholder="Calabar">
              </div>
              <div class="form-group">
                <label class="form-label">State <span class="required">*</span></label>
                <select name="shipping_state" class="form-control" required>
                  <option value="">Select state</option>
                  <?php
                  $states = ['Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT – Abuja','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara'];
                  foreach ($states as $st):
                  ?>
                  <option value="<?= $st ?>" <?= $st === 'Cross River' ? 'selected' : '' ?>><?= $st ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Country</label>
              <input type="text" name="shipping_country" class="form-control" value="Nigeria" readonly>
            </div>
          </div>

          <!-- Order notes -->
          <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem;margin-bottom:1.5rem;">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;">Additional Notes</h2>
            <div class="form-group" style="margin:0;">
              <textarea name="notes" class="form-control" rows="3" placeholder="Any special delivery instructions?"></textarea>
            </div>
          </div>

          <!-- Payment method -->
          <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem;margin-bottom:1.5rem;">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;">Payment</h2>
            <div style="display:flex;align-items:center;gap:.75rem;padding:.9rem 1.1rem;background:var(--gyc-green-100);border-radius:var(--gyc-radius);border:1.5px solid var(--gyc-green-300);">
              <i data-lucide="credit-card" style="width:20px;height:20px;color:var(--gyc-green-600);"></i>
              <div>
                <strong style="font-size:.9rem;color:var(--gyc-dark);">Paystack — Card, Bank Transfer, USSD</strong>
                <div style="font-size:.78rem;color:#666;">Secure payment powered by Paystack</div>
              </div>
              <img src="https://paystack.com/favicon.ico" alt="Paystack" style="width:20px;height:20px;margin-left:auto;">
            </div>
            <p style="font-size:.78rem;color:#888;margin-top:.6rem;">
              You will be redirected to Paystack to complete your payment securely.
              GYC Naturals never stores your card details.
            </p>
          </div>

          <div id="checkout-error" style="display:none;" class="alert alert-danger"></div>

          <button type="submit" class="btn btn-gold btn-lg" style="width:100%;justify-content:center;" id="pay-btn">
            <i data-lucide="lock" style="width:18px;height:18px;"></i>
            Pay <?= formatPrice($total) ?> Securely
          </button>
          <p style="font-size:.75rem;color:#888;text-align:center;margin-top:.5rem;">
            By placing your order you agree to our <a href="<?= SITE_URL ?>/terms.php">Terms &amp; Conditions</a>
          </p>
        </form>
      </div>

      <!-- Order summary -->
      <div>
        <div class="order-summary-card">
          <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:1.25rem;">Your Order</h3>

          <!-- Items -->
          <div style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:1.25rem;max-height:320px;overflow-y:auto;">
            <?php foreach ($items as $item): ?>
            <div style="display:flex;gap:.75rem;align-items:center;">
              <div style="position:relative;flex-shrink:0;">
                <img src="<?= htmlspecialchars($item['image']) ?>"
                     alt="<?= htmlspecialchars($item['name']) ?>"
                     style="width:48px;height:48px;object-fit:cover;border-radius:var(--gyc-radius);">
                <span style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:var(--gyc-dark);color:#fff;font-size:.68rem;font-weight:700;display:flex;align-items:center;justify-content:center;"><?= $item['quantity'] ?></span>
              </div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:.83rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($item['name']) ?></div>
              </div>
              <div style="font-size:.85rem;font-weight:700;color:var(--gyc-green-700);flex-shrink:0;"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
            </div>
            <?php endforeach; ?>
          </div>

          <div style="border-top:1px solid var(--gyc-green-200);padding-top:1rem;display:flex;flex-direction:column;gap:.5rem;font-size:.88rem;">
            <div style="display:flex;justify-content:space-between;"><span style="color:#666;">Subtotal</span><span><?= formatPrice($subtotal) ?></span></div>
            <div style="display:flex;justify-content:space-between;">
              <span style="color:#666;">Shipping</span>
              <span style="color:var(--gyc-green-600);font-weight:600;"><?= $shipping === 0 ? 'FREE' : formatPrice($shipping) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;border-top:2px solid var(--gyc-green-200);padding-top:.75rem;margin-top:.25rem;">
              <strong style="font-size:1rem;">Total</strong>
              <strong style="font-family:'Playfair Display',serif;font-size:1.25rem;color:var(--gyc-green-700);"><?= formatPrice($total) ?></strong>
            </div>
          </div>

          <a href="<?= SITE_URL ?>/cart.php" style="display:block;text-align:center;font-size:.8rem;color:#888;margin-top:1rem;text-decoration:underline;">
            Edit cart
          </a>
        </div>
      </div>
    </div><!-- /checkout-grid -->
  </div>
</section>

<script>
document.getElementById('checkout-form').addEventListener('submit', function(e) {
  e.preventDefault();

  const form   = this;
  const btn    = document.getElementById('pay-btn');
  const errEl  = document.getElementById('checkout-error');
  const origTxt = btn.innerHTML;

  // Client-side validation
  const required = form.querySelectorAll('[required]');
  let firstInvalid = null;
  required.forEach(function(field) {
    field.style.borderColor = '';
    if (!field.value.trim()) {
      field.style.borderColor = 'var(--gyc-terra)';
      if (!firstInvalid) firstInvalid = field;
    }
  });
  if (firstInvalid) {
    firstInvalid.focus();
    errEl.textContent = 'Please fill in all required fields.';
    errEl.style.display = 'flex';
    return;
  }

  const email = form.querySelector('[name="customer_email"]').value.trim();
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    form.querySelector('[name="customer_email"]').style.borderColor = 'var(--gyc-terra)';
    errEl.textContent = 'Please enter a valid email address.';
    errEl.style.display = 'flex';
    return;
  }

  errEl.style.display = 'none';
  btn.disabled = true;
  btn.innerHTML = '<span style="opacity:.7">Processing…</span>';

  // Store checkout data in session via API call
  const formData = new FormData(form);
  fetch('<?= SITE_URL ?>/api/paystack-verify.php', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(r => r.json())
  .then(function(data) {
    if (!data.success) {
      btn.disabled = false;
      btn.innerHTML = origTxt;
      if (typeof lucide !== 'undefined') lucide.createIcons();
      errEl.textContent = data.message || 'Something went wrong.';
      errEl.style.display = 'flex';
      return;
    }

    // Open Paystack
    if (data.paystack && data.paystack.public_key) {
      const pk = data.paystack;
      const handler = PaystackPop.setup({
        key:      pk.public_key,
        email:    pk.email,
        amount:   pk.amount,
        currency: 'NGN',
        ref:      pk.reference,
        metadata: pk.metadata || {},
        callback: function(response) {
          window.location.href = '<?= SITE_URL ?>/api/paystack-verify.php?reference=' + encodeURIComponent(response.reference) + '&type=order';
        },
        onClose: function() {
          btn.disabled = false;
          btn.innerHTML = origTxt;
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }
      });
      handler.openIframe();
    }
  })
  .catch(function() {
    btn.disabled = false;
    btn.innerHTML = origTxt;
    if (typeof lucide !== 'undefined') lucide.createIcons();
    errEl.textContent = 'Network error. Please try again.';
    errEl.style.display = 'flex';
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
