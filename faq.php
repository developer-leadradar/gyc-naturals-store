<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Frequently Asked Questions — GYC Naturals';
$pageDescription = 'Answers to common questions about GYC Naturals hair salon services, natural products, appointments, shipping, and returns — Calabar, Cross River State.';
require_once __DIR__ . '/includes/header.php';

$waPhone  = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
$waClean  = preg_replace('/[^0-9]/', '', $waPhone);

$faqs = [
  'Salon & Appointments' => [
    [
      'q' => 'How do I book an appointment?',
      'a' => 'You can book directly on our website via the <a href="' . SITE_URL . '/book-appointment.php" style="color:var(--gyc-green-600);">Book Appointment</a> page. Choose your preferred style, date, and time, then pay the deposit online. You\'ll receive a WhatsApp confirmation from our team.',
    ],
    [
      'q' => 'Is a deposit required to secure my slot?',
      'a' => 'Yes. A 30% deposit (minimum ₦2,000) is required to confirm your booking. This is deducted from your total service fee when you arrive. The deposit helps us plan our stylists\' schedule and reduces last-minute cancellations.',
    ],
    [
      'q' => 'What is your cancellation policy?',
      'a' => '<strong>48+ hours notice:</strong> Deposit transferred to a new booking (valid 90 days).<br><strong>24–48 hours notice:</strong> 50% of deposit transferred; 50% retained.<br><strong>Under 24 hours / No-show:</strong> Full deposit forfeited.<br>To reschedule, simply WhatsApp us with your appointment number.',
    ],
    [
      'q' => 'How long do hair braiding services take?',
      'a' => 'Duration depends on the style and your hair length/density. Cornrows: 1.5–3 hrs. Box braids: 4–8 hrs. Faux locs: 5–9 hrs. Starter locs: 4–10 hrs. We give you an estimate when booking. Bring snacks and a charged phone — we have Wi-Fi!',
    ],
    [
      'q' => 'Do you accept walk-ins?',
      'a' => 'We prioritise booked appointments, but walk-ins are welcome subject to stylist availability. During busy periods (Fridays, Saturdays, public holidays) we are usually fully booked. We strongly recommend booking at least 3 days in advance.',
    ],
    [
      'q' => 'Can I bring my own hair extensions?',
      'a' => 'Yes, absolutely! Please ensure your extensions are clean and detangled. We also stock a full range of high-quality extensions in-salon if you\'d prefer to purchase from us.',
    ],
    [
      'q' => 'Do you do children\'s hair?',
      'a' => 'Yes! We love working with kids. Our stylists are trained in child-friendly braiding techniques — gentle, no harsh pulling, and we keep the experience fun. Children under 12 must be accompanied by a parent or guardian.',
    ],
  ],
  'Products & Orders' => [
    [
      'q' => 'Are your products safe for sensitive scalps?',
      'a' => 'Yes. All GYC Naturals products are formulated without sulphates, parabens, silicones, or mineral oil. We use natural, plant-derived ingredients. However, if you have known allergies, please review the full ingredient list on each product page before purchasing.',
    ],
    [
      'q' => 'How long does delivery take?',
      'a' => 'Calabar & Cross River: 1–2 business days. Other major cities: 2–4 business days. Remote locations: 4–7 business days. Delivery is handled by trusted courier partners. You\'ll receive a tracking number once your order ships.',
    ],
    [
      'q' => 'What is the minimum order for free shipping?',
      'a' => 'Orders of ₦50,000 and above qualify for free standard delivery across Nigeria. Orders below this threshold incur a flat shipping fee.',
    ],
    [
      'q' => 'Can I return a product?',
      'a' => 'Unopened, unused products can be returned within 7 days of delivery. Opened products cannot be returned for hygiene reasons. See our full <a href="' . SITE_URL . '/refund.php" style="color:var(--gyc-green-600);">Refund Policy</a> for details.',
    ],
    [
      'q' => 'How do I track my order?',
      'a' => 'After placing an order, log in to your account and visit <a href="' . SITE_URL . '/my-orders.php" style="color:var(--gyc-green-600);">My Orders</a>. You\'ll see live status updates. You can also WhatsApp us with your order number for a quick update.',
    ],
    [
      'q' => 'Do you ship outside Nigeria?',
      'a' => 'Currently we only ship within Nigeria. We are exploring international shipping options and will announce when available. In the meantime, we can discuss special arrangements for the diaspora — please contact us.',
    ],
  ],
  'Payments' => [
    [
      'q' => 'What payment methods do you accept?',
      'a' => 'We accept all major debit/credit cards (Visa, Mastercard, Verve) and bank transfers via Paystack. Bank transfer payments must be confirmed via WhatsApp with proof of payment to process your order.',
    ],
    [
      'q' => 'Is it safe to enter my card details on your site?',
      'a' => 'Yes. All card payments are processed by <strong>Paystack</strong>, Nigeria\'s leading payment infrastructure provider. We never store your card details — they go directly to Paystack\'s PCI-DSS compliant servers.',
    ],
    [
      'q' => 'I was charged but haven\'t received confirmation. What do I do?',
      'a' => 'Don\'t panic! Sometimes there\'s a brief delay. Check your spam folder first. If you still don\'t see a confirmation email after 30 minutes, WhatsApp us with your name, email, and approximate transaction time and we\'ll resolve it immediately.',
    ],
  ],
  'Natural Hair Tips' => [
    [
      'q' => 'How often should I wash my natural hair?',
      'a' => 'For most natural African hair types, washing every 1–2 weeks is ideal. Over-washing strips natural oils; under-washing leads to product buildup. Our GYC Naturals Moisture Shampoo Bar is pH-balanced specifically for afro-textured hair.',
    ],
    [
      'q' => 'What products do you recommend for dry, brittle natural hair?',
      'a' => 'Our bestselling <strong>Baobab Hair Butter</strong> and <strong>Black Seed Scalp Oil</strong> are excellent for dry, brittle hair. For severely dry hair, we recommend the GYC Deep Moisture Bundle — hydrating shampoo, deep conditioner, and leave-in cream used as a weekly trio.',
    ],
    [
      'q' => 'Can I book a hair consultation before committing to a style?',
      'a' => 'Absolutely! We offer 30-minute consultations (in-salon or WhatsApp video call) to help you choose the best style and products for your hair type, lifestyle, and budget. Book a "Hair Consultation" slot on our booking page.',
    ],
  ],
];
?>


<!-- HERO -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-green-700));padding:5rem 0 4rem;color:#fff;text-align:center;">
  <div class="container" style="max-width:680px;">
    <p style="font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">Help Centre</p>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2rem,5vw,3rem);margin-bottom:1.25rem;">Frequently Asked Questions</h1>
    <p style="opacity:.85;font-size:1rem;line-height:1.7;margin-bottom:2rem;">Can't find what you're looking for? WhatsApp us — we reply fast.</p>
    <!-- Search -->
    <div style="display:flex;gap:.5rem;max-width:440px;margin:0 auto;">
      <input type="text" id="faq-search" placeholder="Search FAQs…" class="form-control"
             style="flex:1;background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.25);color:#fff;">
      <button type="button" class="btn btn-gold" onclick="searchFAQs()">Search</button>
    </div>
  </div>
</section>

<!-- CATEGORY TABS -->
<section style="background:#fff;border-bottom:1px solid var(--gyc-green-100);padding:.75rem 0;overflow-x:auto;">
  <div class="container">
    <div style="display:flex;gap:.5rem;flex-wrap:nowrap;white-space:nowrap;">
      <button class="btn btn-sm btn-green faq-cat-btn active" data-cat="all">All</button>
      <?php foreach (array_keys($faqs) as $cat): ?>
      <button class="btn btn-sm btn-outline-green faq-cat-btn" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ CONTENT -->
<section style="padding:4rem 0 6rem;background:#F8FAF9;">
  <div class="container" style="display:grid;grid-template-columns:1fr 300px;gap:3rem;align-items:start;max-width:1000px;">

    <!-- Accordion -->
    <div id="faq-list">
      <?php foreach ($faqs as $category => $items): ?>
      <div class="faq-category" data-cat="<?= htmlspecialchars($category) ?>">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.25rem;margin-bottom:1.25rem;padding-bottom:.5rem;border-bottom:2px solid var(--gyc-green-200);color:var(--gyc-dark);">
          <?= htmlspecialchars($category) ?>
        </h2>
        <?php foreach ($items as $idx => $item): ?>
        <div class="faq-item" style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);margin-bottom:.75rem;overflow:hidden;">
          <button class="faq-question" onclick="toggleFAQ(this)"
                  style="width:100%;text-align:left;padding:1.15rem 1.5rem;background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:1rem;font-size:.93rem;font-weight:600;color:var(--gyc-dark);line-height:1.4;">
            <span class="faq-q-text"><?= htmlspecialchars($item['q']) ?></span>
            <i data-lucide="chevron-down" style="width:18px;height:18px;flex-shrink:0;transition:transform .25s;color:var(--gyc-green-600);"></i>
          </button>
          <div class="faq-answer" style="display:none;padding:0 1.5rem 1.25rem;border-top:1px solid var(--gyc-green-100);">
            <p style="font-size:.88rem;line-height:1.8;color:#374151;margin-top:1rem;"><?= $item['a'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; ?>

      <!-- No results -->
      <div id="faq-no-results" style="display:none;text-align:center;padding:3rem;color:#888;">
        <i data-lucide="search-x" style="width:40px;height:40px;opacity:.3;margin-bottom:1rem;"></i>
        <p>No FAQs matched your search. Try different keywords or <a href="<?= SITE_URL ?>/contact.php" style="color:var(--gyc-green-600);">contact us</a>.</p>
      </div>
    </div>

    <!-- Sidebar -->
    <aside style="position:sticky;top:90px;display:flex;flex-direction:column;gap:1.25rem;">

      <!-- WhatsApp CTA -->
      <a href="https://wa.me/<?= $waClean ?>" target="_blank" rel="noopener"
         style="display:flex;flex-direction:column;align-items:center;text-align:center;gap:.75rem;background:#25D366;color:#fff;border-radius:var(--gyc-radius-lg);padding:1.75rem;text-decoration:none;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        <div>
          <div style="font-weight:700;font-size:.95rem;margin-bottom:.2rem;">Still need help?</div>
          <div style="font-size:.8rem;opacity:.9;">Chat with us on WhatsApp</div>
        </div>
      </a>

      <!-- Quick links -->
      <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem;">
        <h3 style="font-family:'Playfair Display',serif;font-size:.95rem;margin-bottom:1rem;">Quick Links</h3>
        <div style="display:flex;flex-direction:column;gap:.5rem;">
          <?php $ql = [
            ['book-appointment.php','Book Appointment','calendar'],
            ['shop.php',            'Shop Products',   'shopping-bag'],
            ['contact.php',         'Contact Us',      'mail'],
            ['refund.php',          'Return Policy',   'refresh-cw'],
          ]; foreach ($ql as $l): ?>
          <a href="<?= SITE_URL ?>/<?= $l[0] ?>" style="display:flex;align-items:center;gap:.6rem;font-size:.83rem;color:var(--gyc-green-700);text-decoration:none;padding:.3rem 0;">
            <i data-lucide="<?= $l[2] ?>" style="width:14px;height:14px;flex-shrink:0;"></i>
            <?= $l[1] ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

    </aside>
  </div>
</section>

<script>
function toggleFAQ(btn) {
  const answer = btn.nextElementSibling;
  const icon   = btn.querySelector('[data-lucide="chevron-down"]');
  const isOpen = answer.style.display !== 'none';

  // Close all others
  document.querySelectorAll('.faq-answer').forEach(function(a) { a.style.display = 'none'; });
  document.querySelectorAll('.faq-question [data-lucide="chevron-down"]').forEach(function(i) {
    i.style.transform = ''; i.closest('.faq-question').style.background = '';
  });

  if (!isOpen) {
    answer.style.display = 'block';
    if (icon) icon.style.transform = 'rotate(180deg)';
    btn.style.background = 'var(--gyc-green-100)';
  }
}

function searchFAQs() {
  const q = document.getElementById('faq-search').value.toLowerCase().trim();
  let found = 0;
  document.querySelectorAll('.faq-item').forEach(function(item) {
    const text = item.querySelector('.faq-q-text').textContent.toLowerCase()
               + item.querySelector('.faq-answer').textContent.toLowerCase();
    const match = !q || text.includes(q);
    item.style.display = match ? '' : 'none';
    if (match) found++;
  });
  // Show/hide category headers
  document.querySelectorAll('.faq-category').forEach(function(cat) {
    const visible = Array.from(cat.querySelectorAll('.faq-item')).some(function(i) { return i.style.display !== 'none'; });
    cat.querySelector('h2').style.display = visible ? '' : 'none';
  });
  document.getElementById('faq-no-results').style.display = found === 0 ? 'block' : 'none';
}

document.getElementById('faq-search').addEventListener('keyup', function(e) {
  if (e.key === 'Enter') searchFAQs();
});

document.querySelectorAll('.faq-cat-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.faq-cat-btn').forEach(function(b) {
      b.classList.remove('btn-green','active'); b.classList.add('btn-outline-green');
    });
    this.classList.add('btn-green','active'); this.classList.remove('btn-outline-green');

    const cat = this.dataset.cat;
    document.querySelectorAll('.faq-category').forEach(function(c) {
      c.style.display = (cat === 'all' || c.dataset.cat === cat) ? '' : 'none';
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
