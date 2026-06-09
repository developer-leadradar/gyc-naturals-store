<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'About Us — GYC Naturals';
$pageDescription = 'The story of GYC Naturals — a Nigerian natural hair brand celebrating African beauty through braiding artistry, natural hair products, and authentic cultural fashion.';
require_once __DIR__ . '/includes/header.php';

$waPhone    = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
$waClean    = preg_replace('/[^0-9]/', '', $waPhone);
$team = [
  [
    'name'  => 'Grace Yakubu',
    'role'  => 'Founder & Creative Director',
    'bio'   => 'Grace founded GYC Naturals with a vision to make African women feel seen, celebrated, and elevated through their natural hair. With 10+ years of braiding experience, she leads our style direction.',
    'photo' => SITE_URL . '/assets/images/team/grace.jpg',
  ],
  [
    'name'  => 'Chinwe Okafor',
    'role'  => 'Head Stylist & Loc Specialist',
    'bio'   => 'Chinwe specialises in loc journeys, from starter locs to advanced loc art. She is a certified trichologist and brings deep scientific knowledge to every scalp treatment.',
    'photo' => SITE_URL . '/assets/images/team/chinwe.jpg',
  ],
  [
    'name'  => 'Adaeze Nwachukwu',
    'role'  => 'Product Development Lead',
    'bio'   => 'Adaeze formulates our natural hair product range using traditional Nigerian botanicals — including black seed, baobab, and African shea — with modern cosmetic science.',
    'photo' => SITE_URL . '/assets/images/team/adaeze.jpg',
  ],
];
?>

<div style="min-height:72px;"></div>

<!-- HERO -->
<section style="position:relative;min-height:500px;background:linear-gradient(135deg,var(--gyc-green-900) 0%,var(--gyc-green-700) 60%,#1a3622 100%);color:#fff;display:flex;align-items:center;padding:5rem 0;">
  <!-- Decorative geometric shapes -->
  <div style="position:absolute;right:8%;top:15%;width:280px;height:280px;border-radius:50%;background:rgba(200,161,82,.12);pointer-events:none;"></div>
  <div style="position:absolute;right:18%;bottom:10%;width:140px;height:140px;border-radius:50%;background:rgba(200,161,82,.08);pointer-events:none;"></div>

  <div class="container" style="max-width:820px;position:relative;z-index:1;">
    <p style="font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">Our Story</p>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5vw,3.5rem);line-height:1.2;margin-bottom:1.5rem;">
      Where African<br>Beauty Lives
    </h1>
    <p style="font-size:1.05rem;opacity:.85;line-height:1.8;max-width:580px;margin-bottom:2rem;">
      GYC Naturals was born in Victoria Island, Lagos — out of a love for natural African hair, ancestral braiding traditions, and the belief that every Nigerian woman deserves to feel crowned.
    </p>
    <div style="display:flex;gap:2.5rem;flex-wrap:wrap;">
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--gyc-gold);">3+</div>
        <div style="font-size:.78rem;opacity:.75;text-transform:uppercase;letter-spacing:.1em;">Years in Business</div>
      </div>
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--gyc-gold);">500+</div>
        <div style="font-size:.78rem;opacity:.75;text-transform:uppercase;letter-spacing:.1em;">Happy Clients</div>
      </div>
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--gyc-gold);">40+</div>
        <div style="font-size:.78rem;opacity:.75;text-transform:uppercase;letter-spacing:.1em;">Style Offerings</div>
      </div>
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--gyc-gold);">100%</div>
        <div style="font-size:.78rem;opacity:.75;text-transform:uppercase;letter-spacing:.1em;">Natural Products</div>
      </div>
    </div>
  </div>
</section>

<!-- ORIGIN STORY -->
<section style="padding:6rem 0;background:#fff;">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;">
      <div>
        <p style="font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.75rem;">The Beginning</p>
        <h2 style="font-family:'Playfair Display',serif;font-size:2rem;line-height:1.25;margin-bottom:1.5rem;">Braids, Botanicals &amp; Bold African Fashion</h2>
        <p style="font-size:.95rem;color:#374151;line-height:1.85;margin-bottom:1.25rem;">
          GYC Naturals started as a passion project in 2021 when our founder, Grace, noticed a gap in the Lagos market: women who wanted high-quality natural hair care but were forced to choose between expensive imported brands and products that didn't suit African hair textures.
        </p>
        <p style="font-size:.95rem;color:#374151;line-height:1.85;margin-bottom:1.25rem;">
          She began formulating small batches of scalp oil and hair butter — drawing on her grandmother's herbal remedies from Plateau State — and offering braiding services from her home studio. Word spread quickly.
        </p>
        <p style="font-size:.95rem;color:#374151;line-height:1.85;">
          Today, GYC Naturals operates a full salon in Victoria Island, ships products nationwide, and has launched an African-inspired clothing line that celebrates the richness of Nigerian and pan-African heritage.
        </p>
      </div>
      <div style="position:relative;">
        <div style="aspect-ratio:4/5;background:linear-gradient(135deg,var(--gyc-green-100),var(--gyc-green-200));border-radius:var(--gyc-radius-xl);overflow:hidden;">
          <img src="<?= SITE_URL ?>/assets/images/about-founder.jpg" alt="Grace — GYC Naturals Founder"
               style="width:100%;height:100%;object-fit:cover;"
               onerror="this.style.display='none'">
          <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:.5rem;color:var(--gyc-green-400);">
            <i data-lucide="user" style="width:64px;height:64px;opacity:.25;"></i>
            <span style="font-size:.8rem;opacity:.4;">Grace Yakubu</span>
          </div>
        </div>
        <!-- Accent card -->
        <div style="position:absolute;bottom:-1.5rem;left:-1.5rem;background:var(--gyc-gold);color:#fff;border-radius:var(--gyc-radius-lg);padding:1.25rem 1.5rem;box-shadow:var(--gyc-shadow-lg);">
          <div style="font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:700;">4.9★</div>
          <div style="font-size:.72rem;font-weight:600;opacity:.9;">Google Reviews</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- VALUES -->
<section style="padding:5rem 0;background:#F8FAF9;">
  <div class="container">
    <div style="text-align:center;margin-bottom:3rem;">
      <p style="font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.5rem;">What Drives Us</p>
      <h2 style="font-family:'Playfair Display',serif;font-size:2rem;">Our Core Values</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:2rem;">
      <?php $values = [
        ['leaf',      'Natural First',        'var(--gyc-green-700)', 'var(--gyc-green-100)',
         'We formulate every product with transparency. No sulphates, no parabens, no mineral oil. Just plants, science, and love.'],
        ['crown',     'Crown Celebration',    '#B45309',              '#FEF9EC',
         'African hair textures are not a problem to be solved — they are a crown to be celebrated. Everything we do affirms this.'],
        ['users',     'Community First',       '#1D4ED8',             '#EFF6FF',
         'We are Lagosians, for Lagosians. We host free natural hair workshops, support local artisans, and give back through the GYC Foundation.'],
        ['shield',    'Gentle & Safe',         'var(--gyc-green-700)', 'var(--gyc-green-100)',
         'Zero-tension braiding, certified stylists, and honest product labels. We never cut corners on your safety or hair health.'],
        ['globe',     'African Heritage',      '#7C3AED',             '#F5F3FF',
         'From Adire prints to Kente patterns — our fashion line celebrates the diversity and richness of pan-African culture.'],
        ['heart',     'Honest Excellence',     '#DC2626',             '#FFF5F5',
         'We don\'t overpromise. We deliver. Every client leaves feeling better than when they arrived — that is the GYC guarantee.'],
      ];
      foreach ($values as $v): ?>
      <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.75rem;">
        <div style="width:48px;height:48px;background:<?= $v[3] ?>;border-radius:var(--gyc-radius);display:flex;align-items:center;justify-content:center;margin-bottom:1rem;">
          <i data-lucide="<?= $v[0] ?>" style="width:22px;height:22px;color:<?= $v[2] ?>;"></i>
        </div>
        <h3 style="font-family:'Playfair Display',serif;font-size:1.05rem;margin-bottom:.6rem;"><?= $v[1] ?></h3>
        <p style="font-size:.85rem;color:#6B7280;line-height:1.7;"><?= $v[4] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- TEAM -->
<section style="padding:5rem 0;background:#fff;">
  <div class="container">
    <div style="text-align:center;margin-bottom:3rem;">
      <p style="font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.5rem;">The People</p>
      <h2 style="font-family:'Playfair Display',serif;font-size:2rem;">Meet Our Team</h2>
      <p style="color:#6B7280;max-width:500px;margin:.75rem auto 0;font-size:.9rem;">Experts in African hair care, passionate about your journey from the first consultation to the final style.</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:2.5rem;">
      <?php foreach ($team as $member): ?>
      <div style="text-align:center;">
        <div style="width:140px;height:140px;border-radius:50%;overflow:hidden;margin:0 auto 1.25rem;border:4px solid var(--gyc-green-100);background:var(--gyc-green-100);">
          <img src="<?= htmlspecialchars($member['photo']) ?>" alt="<?= htmlspecialchars($member['name']) ?>"
               style="width:100%;height:100%;object-fit:cover;"
               onerror="this.style.display='none'">
        </div>
        <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;margin-bottom:.25rem;"><?= htmlspecialchars($member['name']) ?></h3>
        <p style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--gyc-green-600);margin-bottom:.75rem;"><?= htmlspecialchars($member['role']) ?></p>
        <p style="font-size:.84rem;color:#6B7280;line-height:1.7;max-width:280px;margin:0 auto;"><?= htmlspecialchars($member['bio']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- MILESTONES -->
<section style="padding:5rem 0;background:var(--gyc-green-900);color:#fff;">
  <div class="container" style="max-width:820px;">
    <div style="text-align:center;margin-bottom:3rem;">
      <p style="font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.5rem;">Our Journey</p>
      <h2 style="font-family:'Playfair Display',serif;font-size:2rem;">Milestones</h2>
    </div>
    <?php $milestones = [
      ['2021', 'Founded',        'Grace launches GYC Naturals from a home studio in Victoria Island — offering braiding services and 3 handmade products.'],
      ['2022', 'Salon Opens',    'We move into a dedicated salon space. Launch our first full product range including scalp oil, hair butter, and shampoo bars.'],
      ['2023', 'Nationwide',     'Online store goes live. We begin shipping to all 36 states. Launch our GYC Naturals clothing line inspired by Yoruba Adire print.'],
      ['2024', 'GYC Foundation', 'Launch the GYC Foundation — providing free natural hair care workshops to young women in Lagos public schools.'],
      ['2025', 'Growing Strong', '500+ loyal clients, 4.9★ rating, and expanding our product range with 20 new SKUs. Our best chapter yet.'],
    ];
    foreach ($milestones as $i => $ms): ?>
    <div style="display:flex;gap:2rem;align-items:flex-start;margin-bottom:<?= $i < count($milestones)-1 ? '2rem' : '0' ?>;position:relative;">
      <div style="flex-shrink:0;width:70px;text-align:right;">
        <span style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:var(--gyc-gold);"><?= $ms[0] ?></span>
      </div>
      <div style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;">
        <div style="width:14px;height:14px;border-radius:50%;background:var(--gyc-gold);margin-top:.15rem;"></div>
        <?php if ($i < count($milestones)-1): ?>
        <div style="width:2px;flex:1;background:rgba(200,161,82,.3);margin-top:.3rem;min-height:40px;"></div>
        <?php endif; ?>
      </div>
      <div style="padding-bottom:<?= $i < count($milestones)-1 ? '1.5rem' : '0' ?>;">
        <div style="font-weight:700;margin-bottom:.3rem;"><?= htmlspecialchars($ms[1]) ?></div>
        <div style="font-size:.88rem;opacity:.78;line-height:1.7;"><?= htmlspecialchars($ms[2]) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- CTA -->
<section style="padding:5rem 0;background:#F8FAF9;text-align:center;">
  <div class="container" style="max-width:600px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:2rem;margin-bottom:1rem;">Come Experience the GYC Difference</h2>
    <p style="color:#6B7280;margin-bottom:2rem;line-height:1.7;">Visit us at our Victoria Island salon, book online, or order our natural hair products — delivered nationwide.</p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-green" style="padding:.9rem 2rem;">Book Appointment</a>
      <a href="https://wa.me/<?= $waClean ?>" target="_blank" rel="noopener" class="btn btn-whatsapp" style="padding:.9rem 2rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        WhatsApp Us
      </a>
      <a href="<?= SITE_URL ?>/contact.php" class="btn btn-outline-green" style="padding:.9rem 2rem;">Contact Us</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
