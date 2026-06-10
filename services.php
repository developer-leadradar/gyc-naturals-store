<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'Hair Salon Services — GYC Naturals';
$pageDescription = 'Explore GYC Naturals full service menu: knotless braids, locs, natural twists, faux locs, sew-ins and more at our Big Qua Mall, Calabar salon.';
require_once __DIR__ . '/includes/header.php';

$waPhone = getSetting('site_whatsapp') ?: SITE_WHATSAPP;

$services = [
  [
    'icon'     => 'scissors',
    'name'     => 'Knotless Box Braids',
    'price'    => '₦25,000 – ₦55,000',
    'duration' => '4–8 hrs',
    'desc'     => 'Lightweight, scalp-friendly knotless braids in any size — micro to jumbo. Natural hair and extensions to your specification.',
    'features' => ['Natural or coloured extensions','Waist-length or longer','Heart-shaped or triangle parts','Cornrow base option'],
    'category' => 'Braids',
    'popular'  => true,
  ],
  [
    'icon'     => 'wind',
    'name'     => 'Faux Locs',
    'price'    => '₦30,000 – ₦65,000',
    'duration' => '5–9 hrs',
    'desc'     => 'Distressed, soft, butterfly, or goddess faux locs. Achieve the loc look without the permanent commitment.',
    'features' => ['Soft & distressed styles','Butterfly & goddess options','Colour blending available','Removable — no long-term commitment'],
    'category' => 'Locs',
    'popular'  => false,
  ],
  [
    'icon'     => 'zap',
    'name'     => 'Cornrows & Feed-in Braids',
    'price'    => '₦8,000 – ₦20,000',
    'duration' => '1.5–3 hrs',
    'desc'     => 'Sleek, neat cornrows for any occasion — from simple straight backs to intricate geometric patterns.',
    'features' => ['Straight, curved, zig-zag patterns','Stitch braids & feed-ins','Kids\' braids welcome','Hairline design add-on'],
    'category' => 'Braids',
    'popular'  => false,
  ],
  [
    'icon'     => 'loop',
    'name'     => 'Senegalese Twists',
    'price'    => '₦20,000 – ₦42,000',
    'duration' => '3–6 hrs',
    'desc'     => 'Classic rope-twist style using smooth Kanekalon hair. Great for moisture retention and a polished look.',
    'features' => ['Fine or chunky sizing','Ombré & multi-colour','Great for natural hair growth','Low manipulation style'],
    'category' => 'Twists',
    'popular'  => false,
  ],
  [
    'icon'     => 'droplet',
    'name'     => 'Scalp Treatment & Deep Condition',
    'price'    => '₦8,000 – ₦18,000',
    'duration' => '45–90 min',
    'desc'     => 'Holistic scalp treatment targeting dandruff, dryness, breakage, and hair loss with our GYC Naturals product range.',
    'features' => ['Scalp analysis included','Steam deep condition','Natural product range','No sulphates or parabens'],
    'category' => 'Treatments',
    'popular'  => false,
  ],
  [
    'icon'     => 'sparkles',
    'name'     => 'Starter Locs',
    'price'    => '₦35,000 – ₦70,000',
    'duration' => '4–10 hrs',
    'desc'     => 'Begin your loc journey with our expert loc specialists. Comb coils, two-strand starts, or interlocking — all hair types welcome.',
    'features' => ['Consultation included','Comb coils or 2-strand','All natural hair textures','Maintenance schedule provided'],
    'category' => 'Locs',
    'popular'  => false,
  ],
  [
    'icon'     => 'sun',
    'name'     => 'Natural Styles & Wash-n-Go',
    'price'    => '₦7,000 – ₦15,000',
    'duration' => '1–2 hrs',
    'desc'     => 'Show off your natural curl pattern with a professional wash, deep condition, and styling session.',
    'features' => ['Curl definition styling','Twist-outs & braid-outs','Puff & Afro shapes','Silk press option'],
    'category' => 'Natural',
    'popular'  => false,
  ],
  [
    'icon'     => 'star',
    'name'     => 'Sew-In Weave',
    'price'    => '₦22,000 – ₦50,000',
    'duration' => '3–5 hrs',
    'desc'     => 'Full and partial sew-ins using premium human hair bundles. Secure cornrow base, leave-out or closure finish.',
    'features' => ['Closure & frontal options','Virgin & coloured hair','Natural leave-out option','Maintenance tips provided'],
    'category' => 'Weaves',
    'popular'  => false,
  ],
  [
    'icon'     => 'heart',
    'name'     => 'Kids\' Braiding',
    'price'    => '₦5,000 – ₦18,000',
    'duration' => '1–4 hrs',
    'desc'     => 'Gentle, child-friendly braiding in a calm, fun environment. We specialise in making kids feel comfortable and proud of their natural hair.',
    'features' => ['Zero tension promise','Protective styles','Fun colour options','Patience & gentleness guaranteed'],
    'category' => 'Kids',
    'popular'  => false,
  ],
];

$categories = array_unique(array_column($services, 'category'));
?>

<div style="min-height:72px;"></div>

<!-- HERO -->
<section style="background:linear-gradient(135deg,var(--gyc-green-900) 0%,var(--gyc-green-700) 60%,var(--gyc-gold) 100%);padding:6rem 0 5rem;color:#fff;">
  <div class="container" style="max-width:800px;text-align:center;">
    <p style="font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">Our Services</p>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5vw,3.5rem);line-height:1.2;margin-bottom:1.25rem;">Celebrate Your Crown</h1>
    <p style="font-size:1.05rem;opacity:.85;line-height:1.7;margin-bottom:2.5rem;">
      From intricate braids to transformative loc journeys — every service is delivered with expert hands, premium products, and deep respect for your natural beauty.
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold" style="padding:.9rem 2.25rem;font-size:1rem;">Book Appointment</a>
      <a href="<?= SITE_URL ?>/gallery.php" class="btn" style="padding:.9rem 2.25rem;font-size:1rem;background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.3);">View Gallery</a>
    </div>
  </div>
</section>

<!-- FILTER TABS -->
<section style="background:#fff;border-bottom:1px solid var(--gyc-green-100);padding:.75rem 0;overflow-x:auto;">
  <div class="container">
    <div style="display:flex;gap:.5rem;flex-wrap:nowrap;white-space:nowrap;">
      <button class="btn btn-sm btn-green service-filter-btn active" data-cat="all">All Services</button>
      <?php foreach ($categories as $cat): ?>
      <button class="btn btn-sm btn-outline-green service-filter-btn" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- SERVICES GRID -->
<section style="padding:4rem 0 5rem;background:#F8FAF9;">
  <div class="container">
    <div class="products-grid" style="grid-template-columns:repeat(auto-fill,minmax(300px,1fr));" id="services-grid">
      <?php foreach ($services as $svc): ?>
      <article class="service-card" data-cat="<?= htmlspecialchars($svc['category']) ?>"
               style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:2rem;position:relative;transition:box-shadow .2s;">
        <?php if ($svc['popular']): ?>
        <span style="position:absolute;top:1.25rem;right:1.25rem;background:var(--gyc-gold);color:#fff;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;padding:.25rem .6rem;border-radius:20px;">Popular</span>
        <?php endif; ?>

        <div style="width:52px;height:52px;background:var(--gyc-green-100);border-radius:var(--gyc-radius);display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem;">
          <i data-lucide="<?= $svc['icon'] ?>" style="width:24px;height:24px;color:var(--gyc-green-700);"></i>
        </div>

        <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-500);margin-bottom:.35rem;"><?= htmlspecialchars($svc['category']) ?></div>
        <h3 style="font-family:'Playfair Display',serif;font-size:1.2rem;margin-bottom:.6rem;line-height:1.3;"><?= htmlspecialchars($svc['name']) ?></h3>
        <p style="font-size:.87rem;color:#6B7280;line-height:1.7;margin-bottom:1.25rem;"><?= htmlspecialchars($svc['desc']) ?></p>

        <ul style="list-style:none;padding:0;margin:0 0 1.5rem;display:flex;flex-direction:column;gap:.3rem;">
          <?php foreach ($svc['features'] as $feat): ?>
          <li style="display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:#374151;">
            <i data-lucide="check" style="width:14px;height:14px;color:var(--gyc-green-600);flex-shrink:0;"></i>
            <?= htmlspecialchars($feat) ?>
          </li>
          <?php endforeach; ?>
        </ul>

        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:1.25rem;border-top:1px solid var(--gyc-green-100);">
          <div>
            <div style="font-size:.72rem;color:#9CA3AF;margin-bottom:.15rem;">Starting from</div>
            <div style="font-weight:700;font-size:1rem;color:var(--gyc-green-700);"><?= htmlspecialchars($svc['price']) ?></div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:.72rem;color:#9CA3AF;margin-bottom:.15rem;">Duration</div>
            <div style="font-weight:600;font-size:.88rem;color:var(--gyc-dark);"><?= htmlspecialchars($svc['duration']) ?></div>
          </div>
        </div>
        <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-green w-full" style="margin-top:1rem;font-size:.88rem;">Book This Service</a>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US -->
<section style="padding:5rem 0;background:#fff;">
  <div class="container">
    <div style="text-align:center;margin-bottom:3rem;">
      <p style="font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.5rem;">Why GYC Naturals</p>
      <h2 style="font-family:'Playfair Display',serif;font-size:2rem;">Expert Care You Can Trust</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:2rem;">
      <?php $whys = [
        ['award','Expert Stylists','All stylists hold professional certification with a minimum 5 years experience in natural African hair.'],
        ['leaf','100% Natural Products','We only use sulphate-free, paraben-free products — our own GYC Naturals range or carefully vetted alternatives.'],
        ['clock','Punctual Service','We respect your time. Appointments start on schedule, and we give realistic finish-time estimates.'],
        ['shield','Gentle Technique','Zero-tension principles protect your edges and scalp. We monitor for discomfort throughout every service.'],
      ];
      foreach ($whys as $w): ?>
      <div style="text-align:center;padding:1.5rem;">
        <div style="width:60px;height:60px;background:var(--gyc-green-100);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
          <i data-lucide="<?= $w[0] ?>" style="width:26px;height:26px;color:var(--gyc-green-700);"></i>
        </div>
        <h3 style="font-family:'Playfair Display',serif;font-size:1rem;margin-bottom:.5rem;"><?= $w[1] ?></h3>
        <p style="font-size:.83rem;color:#6B7280;line-height:1.65;"><?= $w[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section style="background:var(--gyc-green-900);color:#fff;padding:5rem 0;text-align:center;">
  <div class="container" style="max-width:600px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:1rem;">Ready for Your Transformation?</h2>
    <p style="opacity:.8;margin-bottom:2rem;line-height:1.7;">Book online in minutes. Have questions? Chat with us on WhatsApp.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold" style="padding:.9rem 2rem;">Book Appointment</a>
      <a href="<?= SITE_URL . '/api/whatsapp-redirect.php?page=services' ?>" target="_blank" rel="noopener"
         class="btn btn-whatsapp" style="padding:.9rem 2rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        Ask on WhatsApp
      </a>
    </div>
  </div>
</section>

<script>
document.querySelectorAll('.service-filter-btn').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.service-filter-btn').forEach(function(b) {
      b.classList.remove('btn-green'); b.classList.remove('active'); b.classList.add('btn-outline-green');
    });
    this.classList.add('btn-green'); this.classList.add('active'); this.classList.remove('btn-outline-green');

    const cat = this.dataset.cat;
    document.querySelectorAll('.service-card').forEach(function(card) {
      if (cat === 'all' || card.dataset.cat === cat) {
        card.style.display = ''; card.style.opacity = '1';
      } else {
        card.style.display = 'none';
      }
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
