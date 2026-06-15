<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'About Us — GYC Naturals';
$pageDescription = 'The story of GYC Naturals — a Calabar-based natural hair salon celebrating African beauty through braiding artistry, natural hair products, and authentic cultural fashion. Founded by Juliet Arah in 2024.';
require_once __DIR__ . '/includes/header.php';

$waPhone    = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
$waClean    = preg_replace('/[^0-9]/', '', $waPhone);
?>

<div style="min-height:72px;"></div>

<!-- HERO -->
<section style="position:relative;overflow:hidden;min-height:500px;background:linear-gradient(135deg,var(--gyc-green-900) 0%,var(--gyc-green-700) 60%,#1a3622 100%);color:#fff;display:flex;align-items:center;padding:5rem 0;">
  <!-- Decorative geometric shapes -->
  <div style="position:absolute;right:8%;top:15%;width:280px;height:280px;border-radius:50%;background:rgba(200,161,82,.12);pointer-events:none;"></div>
  <div style="position:absolute;right:18%;bottom:10%;width:140px;height:140px;border-radius:50%;background:rgba(200,161,82,.08);pointer-events:none;"></div>

  <div class="container" style="max-width:820px;position:relative;z-index:1;">
    <p style="font-size:.8rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">Our Story</p>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5vw,3.5rem);line-height:1.2;margin-bottom:1.5rem;color:var(--gyc-gold);">
      Where African<br>Beauty Lives
    </h1>
    <p style="font-size:1.05rem;opacity:.85;line-height:1.8;max-width:580px;margin-bottom:2rem;">
      GYC Naturals was born in the heart of Calabar — out of a love for natural African hair, ancestral braiding traditions, and the belief that every woman deserves to feel crowned.
    </p>
    <div style="display:flex;gap:2.5rem;flex-wrap:wrap;">
      <div>
        <div style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:var(--gyc-gold);">2024</div>
        <div style="font-size:.78rem;opacity:.75;text-transform:uppercase;letter-spacing:.1em;">Est. in Calabar</div>
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
    <div class="about-origin-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;">
      <div class="about-origin-text">
        <p style="font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.75rem;">The Beginning</p>
        <h2 style="font-family:'Playfair Display',serif;font-size:2rem;line-height:1.25;margin-bottom:1.5rem;">Braids, Botanicals &amp; Bold African Fashion</h2>
        <p style="font-size:.95rem;color:#374151;line-height:1.85;margin-bottom:1.25rem;">
          GYC Naturals was founded in 2024 by Juliet Arah, who saw a clear need for a dedicated natural hair salon right here in Calabar. Women in Cross River State deserved high-quality braiding and natural hair care without travelling far or settling for less.
        </p>
        <p style="font-size:.95rem;color:#374151;line-height:1.85;margin-bottom:1.25rem;">
          Juliet set up her salon at Big Qua Mall, Ediba Road — a convenient, welcoming space where every client is treated like royalty. Drawing on deep expertise in African braiding techniques and a passion for healthy natural hair, she built a loyal client base through word of mouth alone.
        </p>
        <p style="font-size:.95rem;color:#374151;line-height:1.85;">
          Today, GYC Naturals offers professional braiding services, curated natural hair products, and an African-inspired clothing line — all from the vibrant city of Calabar, Cross River State.
        </p>
      </div>
      <div style="position:relative;">
        <div style="aspect-ratio:4/5;background:linear-gradient(135deg,var(--gyc-green-100),var(--gyc-green-200));border-radius:var(--gyc-radius-xl);overflow:hidden;">
          <img src="https://images.pexels.com/photos/25752048/pexels-photo-25752048.jpeg?auto=compress&cs=tinysrgb&w=800"
               alt="Juliet Arah — GYC Naturals Founder"
               style="width:100%;height:100%;object-fit:cover;" loading="lazy">
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
         'We are from Calabar, and we are proud of it. We support local artisans, celebrate Cross River State culture, and believe in giving back to the community that gave us our home.'],
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

<!-- FOUNDER -->
<section style="padding:5rem 0;background:#fff;">
  <div class="container">
    <div style="text-align:center;margin-bottom:3rem;">
      <p style="font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.5rem;">The Person Behind It All</p>
      <h2 style="font-family:'Playfair Display',serif;font-size:2rem;">Meet the Founder</h2>
    </div>
    <div style="max-width:700px;margin:0 auto;display:flex;flex-direction:column;align-items:center;gap:1.5rem;text-align:center;">
      <div style="width:180px;height:180px;border-radius:50%;overflow:hidden;border:5px solid var(--gyc-green-100);">
        <img src="https://images.pexels.com/photos/29731065/pexels-photo-29731065.jpeg?auto=compress&cs=tinysrgb&w=400"
             alt="Juliet Arah — Founder, GYC Naturals"
             style="width:100%;height:100%;object-fit:cover;" loading="lazy">
      </div>
      <div>
        <h3 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.25rem;">Juliet Arah</h3>
        <p style="font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--gyc-green-600);margin-bottom:1.25rem;">Founder &amp; Creative Director</p>
        <p style="font-size:1rem;color:#374151;line-height:1.85;max-width:580px;">
          Juliet founded GYC Naturals in 2024 with a single mission: to bring world-class African hair braiding and natural hair care to Calabar. An experienced braiding specialist with a deep passion for African beauty culture, she personally attends to every client to ensure the highest standard of service. At GYC Naturals, you're in expert hands.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- MILESTONES -->
<section style="padding:5rem 0;background:var(--gyc-green-900);color:#fff;">
  <div class="container" style="max-width:820px;">
    <div style="text-align:center;margin-bottom:3rem;">
      <p style="font-size:.8rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.5rem;">Our Journey</p>
      <h2 style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--gyc-gold);">Milestones</h2>
    </div>
    <?php $milestones = [
      ['2024', 'Founded',         'Juliet Arah opens GYC Naturals at Big Qua Mall, Ediba Road, Calabar — bringing professional African braiding to Cross River State.'],
      ['2024', 'First 100 Clients', 'Word spreads fast. Within months of opening, GYC Naturals builds a loyal clientele through exceptional service and real results.'],
      ['2025', 'Product Line',    'Launches a curated range of natural hair products — scalp oils, hair butter, and growth serums formulated for African hair textures.'],
      ['2025', 'Online Booking',  'Website and online booking go live, making it even easier for clients to reserve their favourite styles from anywhere.'],
      ['2026', 'Growing Strong',  'Growing steadily with 100+ loyal clients, 5★ reviews, and an expanding service menu. The best is yet to come.'],
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
    <p style="color:#6B7280;margin-bottom:2rem;line-height:1.7;">Visit us at Big Qua Mall, Ediba Road, Calabar, book online, or order our natural hair products — delivered nationwide.</p>
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
