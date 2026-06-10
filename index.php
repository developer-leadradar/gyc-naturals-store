<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle       = 'GYC Naturals — Grow Your Crown | Professional Hair Braiding & Natural Products, Calabar';
$pageDescription = 'Professional African hair braiding salon in Calabar, Cross River State. Knotless braids, box braids, cornrows, Senegalese twists & natural hair products. Book your appointment online.';
$pageKeywords    = 'hair braiding Calabar, knotless braids Calabar, box braids Calabar Nigeria, natural hair products Calabar, GYC Naturals, African hair salon Big Qua Mall';

// Fetch data for all sections
$featuredGallery = getFeaturedGalleryImages(6);
$allGalCats      = getAllGalleryCategories(true);
$featuredProds   = getFeaturedProducts(4);
$bundles         = getAllBundles();
$testimonials    = getAllTestimonials(true);
$blogPosts       = getBlogPosts(3);

// Before/After pair from gallery (images that have before_image_url)
$beforeAfterImages = getDB()->fetchAll(
    "SELECT * FROM gallery_images WHERE before_image_url IS NOT NULL AND is_active=1 LIMIT 3"
);

// Clothing products
$clothingProducts = getDB()->fetchAll(
    "SELECT * FROM products WHERE category_id=4 AND is_active=1 ORDER BY display_order LIMIT 4"
);

// Proverb of the day
$proverb = getProverbOfTheDay();

// JSON-LD LocalBusiness schema
$phone    = getSetting('site_phone')    ?: SITE_PHONE;
$wa       = getSetting('site_whatsapp') ?: SITE_WHATSAPP;
$address  = getSetting('site_address')  ?: 'Big Qua Mall, Ediba Road, Off Big Qua Town by Marian Market, Calabar, Cross River State, Nigeria';
$email    = getSetting('site_email')    ?: SITE_EMAIL;
$hours    = getSetting('opening_hours') ?: 'Mo-Sa 09:00-19:00';
$jsonLd = json_encode([
    '@context'    => 'https://schema.org',
    '@type'       => 'BeautySalon',
    'name'        => 'GYC Naturals',
    'description' => 'Professional African hair braiding salon and natural hair product store at Big Qua Mall, Calabar, Cross River State, Nigeria. Specialising in knotless braids, faux locs, cornrows, Senegalese twists and scalp treatments.',
    'url'         => SITE_URL . '/',
    'logo'        => SITE_URL . '/assets/images/gyc-logo.png',
    'image'       => SITE_URL . '/assets/images/salon-exterior.jpg',
    'telephone'   => '+234' . ltrim(str_replace([' ','-','+234'], '', $phone), '0'),
    'email'       => $email,
    'address'     => [
        '@type'           => 'PostalAddress',
        'streetAddress'   => 'Big Qua Mall, Ediba Road, Off Big Qua Town by Marian Market',
        'addressLocality' => 'Calabar',
        'addressRegion'   => 'Cross River State',
        'addressCountry'  => 'NG',
    ],
    'geo' => [
        '@type'     => 'GeoCoordinates',
        'latitude'  => 4.9517,
        'longitude' => 8.3601,
    ],
    'openingHours'       => $hours,
    'priceRange'         => '₦₦',
    'currenciesAccepted' => 'NGN',
    'paymentAccepted'    => 'Cash, Bank Transfer, Card, Paystack',
    'hasMap'             => 'https://maps.google.com/?q=Big+Qua+Mall,Calabar,Cross+River+State,Nigeria',
    'sameAs'             => array_filter([
        getSetting('social_instagram') ?: '',
        getSetting('social_facebook')  ?: '',
        getSetting('social_tiktok')    ?: '',
    ]),
    'aggregateRating' => [
        '@type'       => 'AggregateRating',
        'ratingValue' => '4.9',
        'reviewCount' => '500',
        'bestRating'  => '5',
        'worstRating' => '1',
    ],
    'servesCuisine' => null,  // not applicable
    'makesOffer'    => [
        ['@type'=>'Offer','name'=>'Knotless Box Braids','priceCurrency'=>'NGN','price'=>'35000'],
        ['@type'=>'Offer','name'=>'Faux Locs','priceCurrency'=>'NGN','price'=>'45000'],
        ['@type'=>'Offer','name'=>'Cornrows','priceCurrency'=>'NGN','price'=>'15000'],
        ['@type'=>'Offer','name'=>'Senegalese Twists','priceCurrency'=>'NGN','price'=>'40000'],
        ['@type'=>'Offer','name'=>'Scalp Treatment','priceCurrency'=>'NGN','price'=>'12000'],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

require_once __DIR__ . '/includes/header.php';
?>
<script type="application/ld+json"><?= $jsonLd ?></script>

<!-- ═══════════════════════════════════════════════════════
     SECTION 1: HERO
════════════════════════════════════════════════════════ -->
<section class="hero" aria-label="Hero section">
  <div class="hero-inner">

    <!-- Hero text -->
    <div class="hero-content" data-aos="fade-up">
      <span class="hero-eyebrow">
        <img src="<?= SITE_URL ?>/assets/images/adinkra-sankofa.svg" alt="Sankofa" width="20" height="20" style="vertical-align:middle;margin-right:6px;">
        Big Qua Mall, Calabar &nbsp;·&nbsp; Est. 2024
      </span>
      <h1 class="hero-headline">
        <span class="hero-headline-line1">Grow Your</span>
        <span class="hero-headline-line2 text-gold">Crown</span>
      </h1>
      <p class="hero-sub">Professional African hair braiding &amp; hand-selected natural products rooted in botanical tradition.</p>
      <div class="hero-ctas">
        <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold btn-lg">
          <i data-lucide="calendar-check" style="width:18px;height:18px;"></i>
          Book Appointment
        </a>
        <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-outline-white btn-lg">
          <i data-lucide="image" style="width:18px;height:18px;"></i>
          Browse Styles
        </a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <span class="hero-stat-num">2,000+</span>
          <span class="hero-stat-label">Happy Clients</span>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <span class="hero-stat-num">20+</span>
          <span class="hero-stat-label">Braid Styles</span>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <span class="hero-stat-num">5★</span>
          <span class="hero-stat-label">Avg Rating</span>
        </div>
      </div>
    </div>

    <!-- Hero masonry gallery -->
    <div class="hero-masonry" aria-hidden="true">
      <?php
      $heroImages = array_slice($featuredGallery ?: [], 0, 6);
      $positions  = ['tall','wide','sq','tall','sq','wide'];
      foreach ($heroImages as $i => $img):
          $pos = $positions[$i % count($positions)] ?? 'sq';
      ?>
      <div class="hero-tile hero-tile--<?= $pos ?>">
        <img src="<?= htmlspecialchars($img['image_url']) ?>"
             alt="<?= htmlspecialchars($img['title']) ?>"
             loading="<?= $i < 2 ? 'eager' : 'lazy' ?>"
             width="400" height="500">
        <div class="hero-tile-caption"><?= htmlspecialchars($img['title']) ?></div>
      </div>
      <?php endforeach; ?>
      <?php
      $heroFallback = [31473242,14883868,37115258,17463802,5722771,11268995];
      if (empty($heroImages)):
          foreach ($heroFallback as $idx => $pid): $pos = $positions[$idx % count($positions)]; ?>
          <div class="hero-tile hero-tile--<?= $pos ?>">
            <img src="https://images.pexels.com/photos/<?= $pid ?>/pexels-photo-<?= $pid ?>.jpeg?auto=compress&cs=tinysrgb&w=500"
                 alt="African braided hair style" loading="lazy" width="400" height="500">
          </div>
          <?php endforeach; endif; ?>
    </div>

  </div><!-- .hero-inner -->

  <!-- Scroll indicator -->
  <a href="#proverb-strip" class="hero-scroll-hint" aria-label="Scroll down">
    <span class="hero-scroll-line"></span>
    <i data-lucide="chevron-down" style="width:20px;height:20px;color:#fff;"></i>
  </a>
</section>

<!-- ═══════════════════════════════════════════════════════
     SECTION 2: AFRICAN PROVERB STRIP
════════════════════════════════════════════════════════ -->
<div class="proverb-strip" id="proverb-strip">
  <div class="proverb-ankara-bg" aria-hidden="true"></div>
  <div class="proverb-inner container">
    <img src="<?= SITE_URL ?>/assets/images/adinkra-gye-nyame.svg" alt="Gye Nyame" class="proverb-adinkra" width="48" height="48">
    <blockquote class="proverb-text" id="proverb-quote">
      <?php if ($proverb): ?>
      <span class="proverb-main">&ldquo;<?= htmlspecialchars($proverb['text']) ?>&rdquo;</span>
      <span class="proverb-translation">— <?= htmlspecialchars($proverb['translation']) ?></span>
      <span class="proverb-lang"><?= htmlspecialchars($proverb['language']) ?></span>
      <?php else: ?>
      <span class="proverb-main">&ldquo;Irun jẹ ẹwa&rdquo;</span>
      <span class="proverb-translation">— Hair is beauty</span>
      <span class="proverb-lang">Yoruba</span>
      <?php endif; ?>
    </blockquote>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SECTION 3: LATEST STYLES
════════════════════════════════════════════════════════ -->
<section class="section-latest-styles" id="latest-styles" aria-labelledby="styles-heading">
  <div class="container">
    <div class="section-header">
      <div>
        <p class="section-eyebrow">Fresh from the Chair</p>
        <h2 class="section-title" id="styles-heading">Latest Styles</h2>
      </div>
      <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-outline-green">
        View All Styles
        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
      </a>
    </div>

    <!-- Filter chips -->
    <div class="filter-chips" role="tablist" aria-label="Filter styles by category">
      <button class="chip chip--active" data-filter="all" role="tab" aria-selected="true">All Styles</button>
      <?php foreach ($allGalCats as $cat): ?>
      <button class="chip" data-filter="<?= htmlspecialchars($cat['slug']) ?>" role="tab" aria-selected="false">
        <?= htmlspecialchars($cat['name']) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Gallery grid -->
    <div class="gallery-masonry" id="home-gallery-grid" role="tabpanel">
      <?php foreach ($featuredGallery as $style): ?>
      <article class="gallery-card" data-category="<?= htmlspecialchars($style['style_type'] ?? '') ?>">
        <a href="<?= SITE_URL ?>/style-detail.php?slug=<?= urlencode($style['slug']) ?>" class="gallery-card-img-wrap">
          <img src="<?= htmlspecialchars($style['image_url']) ?>"
               alt="<?= htmlspecialchars($style['title']) ?>"
               loading="lazy" width="400" height="500"
               class="gallery-card-img">
          <div class="gallery-card-overlay">
            <button class="gallery-bookmark" data-slug="<?= htmlspecialchars($style['slug']) ?>"
                    aria-label="Save to moodboard" title="Save to moodboard"
                    onclick="toggleMoodboard('<?= htmlspecialchars($style['slug']) ?>', this)">
              <i data-lucide="bookmark" style="width:18px;height:18px;"></i>
            </button>
            <div class="gallery-card-info">
              <h3 class="gallery-card-title"><?= htmlspecialchars($style['title']) ?></h3>
              <?php if ($style['price_from']): ?>
              <span class="gallery-card-price">from <?= formatPrice($style['price_from']) ?></span>
              <?php endif; ?>
              <a href="<?= SITE_URL ?>/book-appointment.php?style_id=<?= $style['id'] ?>"
                 class="btn btn-gold btn-sm gallery-card-book">Book This Style</a>
            </div>
          </div>
        </a>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="section-footer-cta">
      <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-green btn-lg">
        Explore All <?= count($featuredGallery) > 0 ? '20+' : '' ?> Styles
        <i data-lucide="sparkles" style="width:18px;height:18px;"></i>
      </a>
      <a href="<?= SITE_URL ?>/moodboard.php" class="btn btn-outline-green btn-lg">
        <i data-lucide="layout-grid" style="width:18px;height:18px;"></i>
        Build Moodboard
      </a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     SECTION 4: SERVICES
════════════════════════════════════════════════════════ -->
<section class="section-services" aria-labelledby="services-heading">
  <div class="container">
    <div class="section-header">
      <div>
        <p class="section-eyebrow">What We Do</p>
        <h2 class="section-title" id="services-heading">Our Services</h2>
      </div>
      <a href="<?= SITE_URL ?>/services.php" class="btn btn-outline-green">All Services</a>
    </div>

    <div class="services-grid">

      <article class="service-card">
        <div class="service-card-icon" style="background:linear-gradient(135deg,#52B788,#2D6A4F);">
          <i data-lucide="layers" style="width:28px;height:28px;color:#fff;"></i>
        </div>
        <h3 class="service-card-title">Knotless Braids</h3>
        <p class="service-card-desc">The gentlest braid install available. No knot at the base, no tension on your scalp. Natural-looking roots that last 8–10 weeks.</p>
        <ul class="service-card-features">
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Zero tension on edges</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Small, medium, or jumbo sizes</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> With or without curled ends</li>
        </ul>
        <div class="service-card-footer">
          <span class="service-card-price">from <strong>₦20,000</strong></span>
          <a href="<?= SITE_URL ?>/book-appointment.php?service=knotless" class="btn btn-gold btn-sm">Book Now</a>
        </div>
      </article>

      <article class="service-card">
        <div class="service-card-icon" style="background:linear-gradient(135deg,#C9A84C,#8B6914);">
          <i data-lucide="git-branch" style="width:28px;height:28px;color:#fff;"></i>
        </div>
        <h3 class="service-card-title">Box Braids</h3>
        <p class="service-card-desc">The timeless classic. Clean, even box sections from micro to jumbo. Worn straight, in a bun, or half-up — endlessly versatile.</p>
        <ul class="service-card-features">
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Micro, small, medium, jumbo</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Lasts 6–8 weeks</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Any length available</li>
        </ul>
        <div class="service-card-footer">
          <span class="service-card-price">from <strong>₦12,000</strong></span>
          <a href="<?= SITE_URL ?>/book-appointment.php?service=box-braids" class="btn btn-gold btn-sm">Book Now</a>
        </div>
      </article>

      <article class="service-card">
        <div class="service-card-icon" style="background:linear-gradient(135deg,#40916C,#1B4332);">
          <i data-lucide="activity" style="width:28px;height:28px;color:#fff;"></i>
        </div>
        <h3 class="service-card-title">Cornrows</h3>
        <p class="service-card-desc">From simple feed-in lines to intricate geometric designs. Neat edges, cultural pride, and maximum protective power in one style.</p>
        <ul class="service-card-features">
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Feed-in technique</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Tribal & geometric patterns</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Child-safe options available</li>
        </ul>
        <div class="service-card-footer">
          <span class="service-card-price">from <strong>₦8,000</strong></span>
          <a href="<?= SITE_URL ?>/book-appointment.php?service=cornrows" class="btn btn-gold btn-sm">Book Now</a>
        </div>
      </article>

      <article class="service-card">
        <div class="service-card-icon" style="background:linear-gradient(135deg,#74C69D,#2D6A4F);">
          <i data-lucide="wind" style="width:28px;height:28px;color:#fff;"></i>
        </div>
        <h3 class="service-card-title">Senegalese Twists</h3>
        <p class="service-card-desc">Smooth, sleek rope-like twists using premium Kanekalon hair. Lightweight, long-lasting, and absolutely stunning at every length.</p>
        <ul class="service-card-features">
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Premium Kanekalon hair</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Lasts 4–6 weeks</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Waist length available</li>
        </ul>
        <div class="service-card-footer">
          <span class="service-card-price">from <strong>₦15,000</strong></span>
          <a href="<?= SITE_URL ?>/book-appointment.php?service=twists" class="btn btn-gold btn-sm">Book Now</a>
        </div>
      </article>

      <article class="service-card">
        <div class="service-card-icon" style="background:linear-gradient(135deg,#D4A843,#9A6B00);">
          <i data-lucide="flower-2" style="width:28px;height:28px;color:#fff;"></i>
        </div>
        <h3 class="service-card-title">Natural Styles</h3>
        <p class="service-card-desc">Bantu knots, afro puffs, flat twists, and wash-and-go styles that celebrate your natural texture exactly as it grows.</p>
        <ul class="service-card-features">
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Bantu knots & knot-outs</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> Deep conditioning included</li>
          <li><i data-lucide="check" style="width:14px;height:14px;color:#52B788;"></i> All natural hair textures</li>
        </ul>
        <div class="service-card-footer">
          <span class="service-card-price">from <strong>₦5,000</strong></span>
          <a href="<?= SITE_URL ?>/book-appointment.php?service=natural" class="btn btn-gold btn-sm">Book Now</a>
        </div>
      </article>

      <article class="service-card service-card--dark">
        <div class="service-card-icon" style="background:rgba(255,255,255,0.15);">
          <i data-lucide="scissors" style="width:28px;height:28px;color:#C9A84C;"></i>
        </div>
        <h3 class="service-card-title" style="color:#fff;">Not sure what to book?</h3>
        <p class="service-card-desc" style="color:#B7E4C7;">Take our 4-step hair consultation quiz and we will recommend the perfect style for your hair type, lifestyle, and goals.</p>
        <a href="<?= SITE_URL ?>/quiz.php" class="btn btn-gold btn-lg" style="margin-top:1rem;width:100%;justify-content:center;">
          <i data-lucide="sparkles" style="width:18px;height:18px;"></i>
          Take the Hair Quiz
        </a>
      </article>

    </div><!-- .services-grid -->
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     SECTION 5: QUIZ CTA STRIP
════════════════════════════════════════════════════════ -->
<div class="quiz-strip">
  <div class="quiz-strip-ankara" aria-hidden="true"></div>
  <div class="container quiz-strip-inner">
    <div class="quiz-strip-text">
      <span class="quiz-strip-emoji">✨</span>
      <div>
        <strong>Discover Your Perfect Style</strong>
        <span>Answer 4 quick questions and get a personalised hair care plan — completely free.</span>
      </div>
    </div>
    <a href="<?= SITE_URL ?>/quiz.php" class="btn btn-gold">
      Start Quiz
      <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
    </a>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SECTION 6: SHOP PREVIEW
════════════════════════════════════════════════════════ -->
<section class="section-shop" aria-labelledby="shop-heading">
  <div class="container">
    <div class="section-header">
      <div>
        <p class="section-eyebrow">The GYC Collection</p>
        <h2 class="section-title" id="shop-heading">Natural Products</h2>
      </div>
      <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline-green">
        Shop All Products
        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
      </a>
    </div>

    <div class="products-grid">
      <?php foreach ($featuredProds as $prod): ?>
      <article class="product-card">
        <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>" class="product-card-img-wrap">
          <img src="<?= htmlspecialchars($prod['image']) ?>"
               alt="<?= htmlspecialchars($prod['name']) ?>"
               loading="lazy" width="300" height="300"
               class="product-card-img">
          <?php if ($prod['stock_quantity'] < 10 && $prod['stock_quantity'] > 0): ?>
          <span class="product-badge product-badge--low">Only <?= $prod['stock_quantity'] ?> left</span>
          <?php elseif ($prod['stock_quantity'] == 0): ?>
          <span class="product-badge product-badge--out">Sold Out</span>
          <?php endif; ?>
          <button class="product-wishlist"
                  data-product-id="<?= $prod['id'] ?>"
                  aria-label="Add to wishlist"
                  onclick="toggleWishlist(<?= $prod['id'] ?>, this)">
            <i data-lucide="heart" style="width:18px;height:18px;"></i>
          </button>
        </a>
        <div class="product-card-body">
          <?php if ($prod['key_ingredient']): ?>
          <span class="product-tag"><?= htmlspecialchars($prod['key_ingredient']) ?></span>
          <?php endif; ?>
          <h3 class="product-card-name">
            <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>">
              <?= htmlspecialchars($prod['name']) ?>
            </a>
          </h3>
          <?php if ($prod['volume_ml']): ?>
          <span class="product-card-size"><?= $prod['volume_ml'] ?>ml</span>
          <?php endif; ?>
          <?php if ($prod['concern']): ?>
          <div class="product-card-concerns">
            <?php foreach (explode(',', $prod['concern']) as $c): ?>
            <span class="concern-chip"><?= htmlspecialchars(trim($c)) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="product-card-footer">
            <span class="product-price"><?= formatPrice($prod['price']) ?></span>
            <button class="btn btn-gold btn-sm add-to-cart-btn"
                    data-product-id="<?= $prod['id'] ?>"
                    <?= $prod['stock_quantity'] == 0 ? 'disabled' : '' ?>>
              <i data-lucide="shopping-bag" style="width:14px;height:14px;"></i>
              <?= $prod['stock_quantity'] == 0 ? 'Sold Out' : 'Add to Bag' ?>
            </button>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="section-footer-cta">
      <a href="<?= SITE_URL ?>/shop.php" class="btn btn-green btn-lg">
        Browse Full Collection
        <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
      </a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     SECTION 7: BUNDLES STRIP
════════════════════════════════════════════════════════ -->
<?php if (!empty($bundles)): ?>
<section class="section-bundles" aria-labelledby="bundles-heading">
  <div class="container">
    <div class="section-header">
      <div>
        <p class="section-eyebrow">Save More, Grow More</p>
        <h2 class="section-title" id="bundles-heading">Product Bundles</h2>
      </div>
      <a href="<?= SITE_URL ?>/shop.php?tab=bundles" class="btn btn-outline-green">View All Bundles</a>
    </div>
    <div class="bundles-scroll" role="list">
      <?php foreach ($bundles as $bundle):
          $bundleInfo = getBundlePrice($bundle['id']);
      ?>
      <article class="bundle-card" role="listitem">
        <div class="bundle-card-img-wrap">
          <?php $bFallbacks = [33664383,14931950,5706984]; $bIdx = ($bundle['id'] - 1) % 3; ?>
          <img src="<?= htmlspecialchars($bundle['image'] ?? 'https://images.pexels.com/photos/'.$bFallbacks[$bIdx].'/pexels-photo-'.$bFallbacks[$bIdx].'.jpeg?auto=compress&cs=tinysrgb&w=400') ?>"
               alt="<?= htmlspecialchars($bundle['name']) ?>"
               loading="lazy" width="400" height="260">
          <?php if ($bundleInfo && $bundleInfo['discount_pct'] > 0): ?>
          <span class="bundle-badge">Save <?= round($bundleInfo['discount_pct']) ?>%</span>
          <?php endif; ?>
        </div>
        <div class="bundle-card-body">
          <h3 class="bundle-card-title"><?= htmlspecialchars($bundle['name']) ?></h3>
          <p class="bundle-card-desc"><?= htmlspecialchars($bundle['description'] ?? '') ?></p>
          <?php if ($bundleInfo): ?>
          <div class="bundle-card-price">
            <span class="bundle-price-original"><?= formatPrice($bundleInfo['subtotal']) ?></span>
            <span class="bundle-price-now"><?= formatPrice($bundleInfo['total']) ?></span>
          </div>
          <?php endif; ?>
          <a href="<?= SITE_URL ?>/bundle.php?slug=<?= urlencode($bundle['slug']) ?>" class="btn btn-gold btn-sm">
            Shop Bundle
          </a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     SECTION 8: BEFORE / AFTER SHOWCASE
════════════════════════════════════════════════════════ -->
<?php if (!empty($beforeAfterImages)): ?>
<section class="section-before-after" aria-labelledby="ba-heading">
  <div class="container">
    <div class="section-header section-header--center">
      <div>
        <p class="section-eyebrow">See the Difference</p>
        <h2 class="section-title" id="ba-heading">Before &amp; After</h2>
        <p class="section-subtitle">Drag the slider to reveal the transformation</p>
      </div>
    </div>
    <div class="ba-showcase">
      <?php foreach (array_slice($beforeAfterImages, 0, 3) as $i => $img): ?>
      <div class="ba-item <?= $i === 0 ? 'ba-item--main' : 'ba-item--thumb' ?>">
        <div class="ba-container" data-ba-id="ba-<?= $img['id'] ?>">
          <div class="ba-before">
            <img src="<?= htmlspecialchars($img['before_image_url']) ?>"
                 alt="Before - <?= htmlspecialchars($img['title']) ?>"
                 loading="lazy" draggable="false">
            <span class="ba-label ba-label--before">Before</span>
          </div>
          <div class="ba-after" style="clip-path: inset(0 50% 0 0)">
            <img src="<?= htmlspecialchars($img['image_url']) ?>"
                 alt="After - <?= htmlspecialchars($img['title']) ?>"
                 loading="lazy" draggable="false">
            <span class="ba-label ba-label--after">After</span>
          </div>
          <div class="ba-divider" style="left:50%">
            <div class="ba-handle">
              <i data-lucide="chevrons-left-right" style="width:18px;height:18px;"></i>
            </div>
          </div>
        </div>
        <p class="ba-caption"><?= htmlspecialchars($img['title']) ?></p>
        <?php if ($i === 0): ?>
        <a href="<?= SITE_URL ?>/book-appointment.php?style_id=<?= $img['id'] ?>" class="btn btn-gold btn-sm" style="margin-top:0.75rem;">
          Book This Style
        </a>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     SECTION 9: CLOTHING TEASER
════════════════════════════════════════════════════════ -->
<?php if (!empty($clothingProducts)): ?>
<section class="section-clothing" aria-labelledby="clothing-heading">
  <div class="section-clothing-inner">
    <div class="clothing-text-col">
      <p class="section-eyebrow" style="color:#C9A84C;">African Fashion</p>
      <h2 class="section-title" id="clothing-heading" style="color:#fff;">
        Wear Your<br><span class="text-gold">Heritage</span>
      </h2>
      <p style="color:#B7E4C7;font-size:1rem;line-height:1.7;max-width:360px;margin-bottom:1.5rem;">
        From vibrant ankara midi dresses to stunning kimono wraps — GYC Naturals brings you African fashion that celebrates who you are.
      </p>
      <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
        <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn btn-gold btn-lg">
          Shop Clothing
          <i data-lucide="arrow-right" style="width:18px;height:18px;"></i>
        </a>
        <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn btn-outline-white btn-lg">View All</a>
      </div>
    </div>
    <div class="clothing-products-col">
      <div class="clothing-scroll" id="clothing-scroll">
        <?php foreach ($clothingProducts as $cp): ?>
        <article class="clothing-item">
          <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($cp['slug']) ?>">
            <img src="<?= htmlspecialchars($cp['image']) ?>"
                 alt="<?= htmlspecialchars($cp['name']) ?>"
                 loading="lazy" width="280" height="380">
            <div class="clothing-item-caption">
              <span><?= htmlspecialchars($cp['name']) ?></span>
              <strong><?= formatPrice($cp['price']) ?></strong>
            </div>
          </a>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     SECTION 10: TESTIMONIALS
════════════════════════════════════════════════════════ -->
<?php if (!empty($testimonials)): ?>
<section class="section-testimonials" aria-labelledby="testimonials-heading">
  <div class="container">
    <div class="section-header section-header--center">
      <div>
        <p class="section-eyebrow">What Our Clients Say</p>
        <h2 class="section-title" id="testimonials-heading">Real Reviews</h2>
        <div class="testimonials-stars" aria-label="5 stars out of 5">
          <?php for ($s = 0; $s < 5; $s++): ?><span class="star">★</span><?php endfor; ?>
          <span style="margin-left:8px;color:#888;font-size:0.9rem;">5.0 average from 200+ reviews</span>
        </div>
      </div>
    </div>

    <div class="testimonials-grid">
      <?php foreach ($testimonials as $t): ?>
      <article class="testimonial-card">
        <div class="testimonial-stars" aria-label="<?= $t['rating'] ?> stars">
          <?php for ($s = 0; $s < $t['rating']; $s++): ?><span class="star">★</span><?php endfor; ?>
        </div>
        <blockquote class="testimonial-quote">
          &ldquo;<?= htmlspecialchars($t['content']) ?>&rdquo;
        </blockquote>
        <div class="testimonial-author">
          <?php if (!empty($t['photo_url'])): ?>
          <img src="<?= htmlspecialchars($t['photo_url']) ?>"
               alt="<?= htmlspecialchars($t['author_name']) ?>"
               width="44" height="44" class="testimonial-avatar"
               loading="lazy">
          <?php else: ?>
          <div style="width:44px;height:44px;border-radius:50%;background:var(--gyc-green-700);color:#fff;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;"><?= strtoupper(substr($t['author_name'],0,1)) ?></div>
          <?php endif; ?>
          <div>
            <strong class="testimonial-name"><?= htmlspecialchars($t['author_name']) ?></strong>
            <span class="testimonial-style"><?= htmlspecialchars($t['service'] ?? '') ?></span>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="section-footer-cta">
      <a href="<?= SITE_URL ?>/testimonials.php" class="btn btn-outline-green">
        Read All Reviews
        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     SECTION 11: ABOUT STRIP
════════════════════════════════════════════════════════ -->
<section class="section-about-strip" aria-labelledby="about-heading">
  <div class="container">
    <div class="about-strip-inner">
      <div class="about-strip-image">
        <div class="about-img-wrap">
          <img src="https://images.pexels.com/photos/713527/pexels-photo-713527.jpeg?auto=compress&cs=tinysrgb&w=600"
               alt="Juliet Arah — Founder, GYC Naturals Calabar"
               loading="lazy" width="420" height="520">
          <div class="about-kente-accent"></div>
          <div class="about-adinkra-float">
            <img src="<?= SITE_URL ?>/assets/images/adinkra-sankofa.svg" alt="" width="60" height="60" aria-hidden="true">
          </div>
        </div>
      </div>
      <div class="about-strip-text">
        <p class="section-eyebrow">Our Story</p>
        <h2 class="section-title" id="about-heading">Where Every Crown<br>is Celebrated</h2>
        <?php $bio = getSetting('about_owner_bio'); ?>
        <p style="color:#444;font-size:1.05rem;line-height:1.8;margin-bottom:1.5rem;">
          <?= htmlspecialchars($bio ?: 'Welcome to GYC Naturals — where African hair culture meets modern care. We believe every woman deserves to wear her natural crown with confidence.') ?>
        </p>
        <ul class="about-strip-points">
          <li>
            <i data-lucide="leaf" style="width:20px;height:20px;color:#52B788;"></i>
            <span>100% natural ingredients, ethically sourced from West Africa</span>
          </li>
          <li>
            <i data-lucide="shield-check" style="width:20px;height:20px;color:#52B788;"></i>
            <span>Professional stylists trained in African hair techniques</span>
          </li>
          <li>
            <i data-lucide="heart" style="width:20px;height:20px;color:#52B788;"></i>
            <span>A safe space for every texture, every length, every crown</span>
          </li>
        </ul>
        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1.5rem;">
          <a href="<?= SITE_URL ?>/about.php" class="btn btn-green">Our Full Story</a>
          <a href="<?= SITE_URL ?>/services.php" class="btn btn-outline-green">Our Services</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     SECTION 12: BLOG PREVIEW
════════════════════════════════════════════════════════ -->
<?php if (!empty($blogPosts)): ?>
<section class="section-blog" aria-labelledby="blog-heading">
  <div class="container">
    <div class="section-header">
      <div>
        <p class="section-eyebrow">Hair Knowledge</p>
        <h2 class="section-title" id="blog-heading">From the Blog</h2>
      </div>
      <a href="<?= SITE_URL ?>/blog.php" class="btn btn-outline-green">
        All Articles
        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
      </a>
    </div>
    <div class="blog-grid">
      <?php foreach ($blogPosts as $i => $post): ?>
      <article class="blog-card <?= $i === 0 ? 'blog-card--featured' : '' ?>">
        <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="blog-card-img-wrap">
          <?php $blogFallbacks = [28383173,11641088,6960735,34191088]; $bpIdx = ($post['id'] - 1) % 4; ?>
          <img src="<?= htmlspecialchars($post['featured_image'] ?? 'https://images.pexels.com/photos/'.$blogFallbacks[$bpIdx].'/pexels-photo-'.$blogFallbacks[$bpIdx].'.jpeg?auto=compress&cs=tinysrgb&w=800') ?>"
               alt="<?= htmlspecialchars($post['title']) ?>"
               loading="lazy" width="800" height="450" class="blog-card-img">
        </a>
        <div class="blog-card-body">
          <span class="blog-tag"><?= htmlspecialchars($post['category'] ?? 'Hair Care') ?></span>
          <h3 class="blog-card-title">
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>">
              <?= htmlspecialchars($post['title']) ?>
            </a>
          </h3>
          <p class="blog-card-excerpt"><?= htmlspecialchars($post['excerpt'] ?? '') ?></p>
          <div class="blog-card-meta">
            <span><i data-lucide="user" style="width:13px;height:13px;"></i> <?= htmlspecialchars($post['author'] ?? 'GYC Naturals') ?></span>
            <span><i data-lucide="clock" style="width:13px;height:13px;"></i> <?= readTime($post['body'] ?? '') ?> min read</span>
            <a href="<?= SITE_URL ?>/blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="blog-read-more">
              Read More <i data-lucide="arrow-right" style="width:13px;height:13px;"></i>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     SECTION 13: INSTAGRAM / SOCIAL STRIP
════════════════════════════════════════════════════════ -->
<section class="section-social" aria-labelledby="social-heading">
  <div class="container">
    <div class="section-header section-header--center">
      <div>
        <p class="section-eyebrow">Follow Our Journey</p>
        <h2 class="section-title" id="social-heading">
          <a href="https://instagram.com/<?= htmlspecialchars(getSetting('instagram_handle') ?: 'gycnaturals') ?>"
             target="_blank" rel="noopener"
             style="text-decoration:none;color:inherit;">
            @<?= htmlspecialchars(getSetting('instagram_handle') ?: 'gycnaturals') ?>
          </a>
        </h2>
      </div>
    </div>
    <div class="social-grid">
      <?php
      // Use a mix of gallery images as "Instagram" posts
      $socialImages = getDB()->fetchAll("SELECT * FROM gallery_images WHERE is_active=1 ORDER BY RAND() LIMIT 8");
      foreach ($socialImages as $si):
      ?>
      <a href="https://instagram.com/<?= htmlspecialchars(getSetting('instagram_handle') ?: 'gycnaturals') ?>"
         target="_blank" rel="noopener"
         class="social-grid-item"
         aria-label="View on Instagram">
        <img src="<?= htmlspecialchars($si['image_url']) ?>"
             alt="<?= htmlspecialchars($si['title']) ?>"
             loading="lazy" width="300" height="300">
        <div class="social-grid-overlay">
          <i data-lucide="instagram" style="width:28px;height:28px;color:#fff;"></i>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="section-footer-cta">
      <a href="https://instagram.com/<?= htmlspecialchars(getSetting('instagram_handle') ?: 'gycnaturals') ?>"
         target="_blank" rel="noopener"
         class="btn btn-outline-green btn-lg">
        <i data-lucide="instagram" style="width:20px;height:20px;"></i>
        Follow on Instagram
      </a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     SECTION 14: BOOK CTA BANNER
════════════════════════════════════════════════════════ -->
<section class="section-book-cta" aria-labelledby="book-cta-heading">
  <div class="book-cta-ankara" aria-hidden="true"></div>
  <div class="container book-cta-inner">
    <div class="book-cta-text">
      <p class="section-eyebrow" style="color:#C9A84C;">Ready to Grow Your Crown?</p>
      <h2 class="section-title" id="book-cta-heading" style="color:#fff;font-size:clamp(1.8rem,4vw,3rem);">
        Book Your Appointment Today
      </h2>
      <p style="color:#B7E4C7;font-size:1.1rem;max-width:500px;margin-bottom:0;line-height:1.7;">
        Choose your style from our gallery, pick a date, and we will take care of the rest. Online booking takes less than 3 minutes.
      </p>
    </div>
    <div class="book-cta-actions">
      <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold btn-lg">
        <i data-lucide="calendar-check" style="width:20px;height:20px;"></i>
        Book Appointment
      </a>
      <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-outline-white btn-lg">
        Browse Styles First
      </a>
      <?php
      $waPhone = getSetting('site_whatsapp');
      if ($waPhone):
          $waMsg = 'Hello GYC Naturals! I would like to book an appointment.';
          $waUrl = whatsappMessage($waPhone, $waMsg);
      ?>
      <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp btn-lg">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
        Chat on WhatsApp
      </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Inline add-to-cart handlers -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const pid = btn.dataset.productId;
      if (!pid) return;
      addToCart(pid, 1, btn);
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
