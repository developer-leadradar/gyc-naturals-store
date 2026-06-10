<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Load clothing products (category slug = 'clothing')
$clothingCat = getDB()->fetchOne(
    "SELECT * FROM product_categories WHERE slug = 'clothing' AND is_active = 1"
);
$catId = $clothingCat ? $clothingCat['id'] : 0;

$clothingItems = getAllProducts(['category_id' => $catId, 'in_stock' => true], 24, 0);
$newArrivals   = getAllProducts(['category_id' => $catId, 'in_stock' => true], 4, 0);
$total         = countProducts(['category_id' => $catId]);

// Sort options (passed to shop.php with category filter)
$sortBy = sanitize($_GET['sort'] ?? 'newest');
$allowedSorts = ['newest','price_asc','price_desc'];
if (!in_array($sortBy, $allowedSorts)) $sortBy = 'newest';

$pageTitle       = 'African Fashion & Clothing — GYC Naturals Calabar';
$pageDescription = 'Shop GYC Naturals clothing line — vibrant African prints, co-ord sets, and everyday natural-living fashion. Delivered across Nigeria.';
$canonicalUrl    = SITE_URL . '/clothing.php';
require_once __DIR__ . '/includes/header.php';
?>

<!-- JSON-LD: Product collection -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "GYC Naturals Clothing Line",
  "description": "<?= addslashes($pageDescription) ?>",
  "url": "<?= $canonicalUrl ?>",
  "provider": {
    "@type": "Organization",
    "name": "GYC Naturals",
    "url": "<?= SITE_URL ?>"
  }
}
</script>

<div style="min-height:72px;"></div>

<!-- ══════════════════════════════════════════════════════
     HERO BANNER
══════════════════════════════════════════════════════ -->
<section style="position:relative;overflow:hidden;min-height:520px;display:flex;align-items:center;background:#1a0a00;">
  <!-- Background pattern -->
  <div style="position:absolute;inset:0;background-image:url('https://images.pexels.com/photos/20370167/pexels-photo-20370167.jpeg?auto=compress&cs=tinysrgb&w=1400');background-size:cover;background-position:center;opacity:.35;"></div>
  <!-- African kente-inspired accent bars -->
  <div style="position:absolute;top:0;left:0;right:0;height:6px;background:linear-gradient(90deg,var(--gyc-gold) 0%,var(--gyc-terra) 33%,var(--gyc-green-600) 66%,var(--gyc-gold) 100%);"></div>

  <div class="container" style="position:relative;z-index:2;padding:5rem 0;">
    <div style="max-width:640px;">
      <p style="font-size:.75rem;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">GYC Naturals Fashion</p>
      <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5vw,3.5rem);color:#fff;line-height:1.2;margin-bottom:1.25rem;">
        Wear Your<br><em style="color:var(--gyc-gold);">Natural Beauty</em>
      </h1>
      <p style="font-size:1.05rem;color:rgba(255,255,255,.8);line-height:1.75;margin-bottom:2rem;max-width:520px;">
        Vibrant African prints, co-ord sets, and everyday natural-living fashion — designed for the modern African woman who wears her culture proudly.
      </p>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <a href="#collection" class="btn btn-gold" style="padding:.9rem 2rem;">Shop the Collection</a>
        <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn" style="padding:.9rem 2rem;background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.35);">View All Products</a>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     CATEGORY STRIPS
══════════════════════════════════════════════════════ -->
<section style="background:#FFF9F0;padding:3.5rem 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;">

      <?php
      $clothingCats = [
        ['icon'=>'🌺','label'=>'Ankara Prints',   'filter'=>'ankara'],
        ['icon'=>'👗','label'=>'Co-ord Sets',      'filter'=>'coord-sets'],
        ['icon'=>'🧣','label'=>'Headwraps',        'filter'=>'headwraps'],
        ['icon'=>'👚','label'=>'T-Shirts & Tops',  'filter'=>'tops'],
        ['icon'=>'👖','label'=>'Trousers',         'filter'=>'trousers'],
        ['icon'=>'💼','label'=>'Accessories',      'filter'=>'accessories'],
      ];
      foreach ($clothingCats as $cc):
      ?>
      <a href="<?= SITE_URL ?>/shop.php?category=clothing&tag=<?= urlencode($cc['filter']) ?>"
         style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;padding:1.4rem 1rem;background:#fff;border:1.5px solid #F3E8D8;border-radius:var(--gyc-radius-lg);text-decoration:none;transition:all .2s;color:var(--gyc-dark);"
         onmouseover="this.style.borderColor='var(--gyc-gold)';this.style.transform='translateY(-3px)'"
         onmouseout="this.style.borderColor='#F3E8D8';this.style.transform='translateY(0)'">
        <span style="font-size:1.8rem;"><?= $cc['icon'] ?></span>
        <span style="font-size:.82rem;font-weight:600;text-align:center;"><?= $cc['label'] ?></span>
      </a>
      <?php endforeach; ?>

    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     BRAND STORY STRIP
══════════════════════════════════════════════════════ -->
<section style="background:var(--gyc-green-900);color:#fff;padding:4rem 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;">
      <div>
        <p style="font-size:.75rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">Our Fashion Philosophy</p>
        <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.6rem,3vw,2.2rem);margin-bottom:1.25rem;line-height:1.3;">Rooted in Africa.<br>Made for Today.</h2>
        <p style="opacity:.85;line-height:1.8;margin-bottom:1.25rem;">At GYC Naturals, fashion is an extension of our philosophy — embrace your natural self, wear your heritage with pride. Every piece in our clothing line is inspired by the rich textile traditions of West Africa, reimagined for the modern Nigerian woman.</p>
        <p style="opacity:.75;line-height:1.8;font-size:.9rem;">We source our fabrics locally, supporting Nigerian artisans and fabric markets. From Ankara to Adire, our collections celebrate the diversity of African textiles.</p>
        <div style="display:flex;gap:2rem;margin-top:2rem;">
          <div style="text-align:center;">
            <div style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:var(--gyc-gold);">100%</div>
            <div style="font-size:.75rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;">Local Fabrics</div>
          </div>
          <div style="text-align:center;">
            <div style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:var(--gyc-gold);">Made</div>
            <div style="font-size:.75rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;">in Calabar</div>
          </div>
          <div style="text-align:center;">
            <div style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:var(--gyc-gold);">Fast</div>
            <div style="font-size:.75rem;opacity:.7;text-transform:uppercase;letter-spacing:.08em;">Delivery</div>
          </div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <img src="https://images.pexels.com/photos/37994008/pexels-photo-37994008.jpeg?auto=compress&cs=tinysrgb&w=400" alt="African print fashion"
             style="width:100%;aspect-ratio:3/4;object-fit:cover;border-radius:var(--gyc-radius-lg);">
        <img src="https://images.pexels.com/photos/37994020/pexels-photo-37994020.jpeg?auto=compress&cs=tinysrgb&w=400" alt="Fashion lookbook"
             style="width:100%;aspect-ratio:3/4;object-fit:cover;border-radius:var(--gyc-radius-lg);margin-top:2rem;">
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     PRODUCT COLLECTION
══════════════════════════════════════════════════════ -->
<section id="collection" style="padding:5rem 0;background:#FAFAF8;">
  <div class="container">

    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2.5rem;flex-wrap:wrap;gap:1rem;">
      <div>
        <p style="font-size:.75rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-terra);margin-bottom:.4rem;">The Collection</p>
        <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.6rem,3vw,2.2rem);margin:0;">
          <?= $total > 0 ? "Shop All Styles" : "Coming Soon" ?>
        </h2>
        <?php if ($total > 0): ?>
        <p style="color:#6B7280;font-size:.875rem;margin-top:.4rem;"><?= $total ?> pieces available</p>
        <?php endif; ?>
      </div>
      <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap;">
        <!-- Sort -->
        <select onchange="location.href='<?= SITE_URL ?>/clothing.php?sort='+this.value"
                style="padding:.55rem 1rem;border:1.5px solid #E5E7EB;border-radius:6px;font-size:.875rem;cursor:pointer;background:#fff;">
          <option value="newest"    <?= $sortBy==='newest'    ? 'selected' : '' ?>>Newest First</option>
          <option value="price_asc" <?= $sortBy==='price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
          <option value="price_desc"<?= $sortBy==='price_desc'? 'selected' : '' ?>>Price: High to Low</option>
        </select>
        <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn btn-outline-green" style="font-size:.85rem;padding:.55rem 1.2rem;">View in Shop</a>
      </div>
    </div>

    <?php if (empty($clothingItems)): ?>
    <!-- ── EMPTY STATE ── -->
    <div style="text-align:center;padding:6rem 2rem;background:#fff;border-radius:var(--gyc-radius-xl);border:2px dashed #E5E7EB;">
      <div style="font-size:4rem;margin-bottom:1rem;">👗</div>
      <h3 style="font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:.75rem;color:var(--gyc-dark);">Collection Launching Soon</h3>
      <p style="color:#6B7280;max-width:400px;margin:0 auto 2rem;line-height:1.7;">
        Our clothing line is being curated with love. Sign up to be first to shop when we launch — exclusive early-bird discount included!
      </p>

      <!-- Notify form -->
      <form id="notify-form" style="display:flex;gap:.75rem;max-width:400px;margin:0 auto;" onsubmit="handleNotify(event)">
        <input type="email" id="notify-email" placeholder="your@email.com" required
               style="flex:1;padding:.75rem 1rem;border:1.5px solid #D1D5DB;border-radius:6px;font-size:.9rem;">
        <button type="submit" class="btn btn-gold" style="white-space:nowrap;">Notify Me</button>
      </form>
      <p id="notify-msg" style="display:none;margin-top:1rem;color:var(--gyc-green-700);font-weight:600;">✅ You're on the list! We'll notify you first.</p>
    </div>

    <?php else: ?>
    <!-- ── PRODUCT GRID ── -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.5rem;" id="clothing-grid">
      <?php foreach ($clothingItems as $item): ?>
      <?php
        $img     = $item['image'] ?? 'https://images.pexels.com/photos/32093798/pexels-photo-32093798.jpeg?auto=compress&cs=tinysrgb&w=400';
        $price   = formatPrice($item['price']);
        $oldPrice = !empty($item['compare_at_price']) && $item['compare_at_price'] > $item['price']
                    ? formatPrice($item['compare_at_price']) : null;
        $inStock = ($item['stock_quantity'] ?? 1) > 0;
        $isNew   = strtotime($item['created_at'] ?? '') > strtotime('-30 days');
        $isSale  = $oldPrice !== null;
      ?>
      <article class="product-card" style="background:#fff;border:1.5px solid #E9F0EB;border-radius:var(--gyc-radius-lg);overflow:hidden;transition:all .25s;position:relative;"
               onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 32px rgba(0,0,0,.1)'"
               onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">

        <!-- Badges -->
        <div style="position:absolute;top:10px;left:10px;z-index:2;display:flex;flex-direction:column;gap:4px;">
          <?php if ($isNew && !$isSale): ?><span style="background:var(--gyc-green-700);color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:99px;letter-spacing:.06em;">NEW</span><?php endif; ?>
          <?php if ($isSale): ?><span style="background:var(--gyc-terra);color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:99px;">SALE</span><?php endif; ?>
          <?php if (!$inStock): ?><span style="background:#9CA3AF;color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:99px;">SOLD OUT</span><?php endif; ?>
        </div>

        <!-- Wishlist -->
        <button onclick="toggleWishlist(<?= $item['id'] ?>, this)" title="Save to Wishlist"
                style="position:absolute;top:10px;right:10px;z-index:2;background:#fff;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.15);transition:transform .2s;"
                onmouseover="this.style.transform='scale(1.15)'" onmouseout="this.style.transform='scale(1)'">
          <i data-lucide="heart" style="width:16px;height:16px;color:#9CA3AF;"></i>
        </button>

        <!-- Image -->
        <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>">
          <div style="aspect-ratio:3/4;overflow:hidden;background:#F3F4F6;">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                 loading="lazy" style="width:100%;height:100%;object-fit:cover;transition:transform .45s cubic-bezier(.25,.46,.45,.94);"
                 onmouseover="this.style.transform='scale(1.07)'" onmouseout="this.style.transform='scale(1)'">
          </div>
        </a>

        <!-- Info -->
        <div style="padding:1rem;">
          <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>"
             style="text-decoration:none;color:inherit;">
            <h3 style="font-size:.9rem;font-weight:700;margin:0 0 .35rem;line-height:1.35;color:var(--gyc-dark);"><?= htmlspecialchars($item['name']) ?></h3>
          </a>
          <?php if (!empty($item['short_description'])): ?>
          <p style="font-size:.78rem;color:#6B7280;margin:0 0 .75rem;line-height:1.5;"><?= htmlspecialchars(substr($item['short_description'], 0, 70)) ?>…</p>
          <?php endif; ?>

          <!-- Sizes if present -->
          <?php if (!empty($item['sizes'])): ?>
          <div style="display:flex;gap:4px;margin-bottom:.75rem;flex-wrap:wrap;">
            <?php foreach (explode(',', $item['sizes']) as $sz): ?>
            <span style="border:1px solid #D1D5DB;border-radius:4px;padding:1px 6px;font-size:.7rem;color:#374151;"><?= trim(htmlspecialchars($sz)) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Price + CTA -->
          <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
            <div>
              <span style="font-weight:800;font-size:1rem;color:var(--gyc-dark);"><?= $price ?></span>
              <?php if ($oldPrice): ?>
              <span style="font-size:.8rem;color:#9CA3AF;text-decoration:line-through;margin-left:.35rem;"><?= $oldPrice ?></span>
              <?php endif; ?>
            </div>
            <?php if ($inStock): ?>
            <button onclick="addToCart(<?= $item['id'] ?>, 1, this)"
                    style="background:var(--gyc-green-700);color:#fff;border:none;padding:.45rem .85rem;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;transition:background .15s;"
                    onmouseover="this.style.background='var(--gyc-green-900)'" onmouseout="this.style.background='var(--gyc-green-700)'">
              Add to Bag
            </button>
            <?php else: ?>
            <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>#notify" style="font-size:.78rem;color:var(--gyc-terra);font-weight:600;text-decoration:none;">Notify Me</a>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- Load More -->
    <?php if ($total > count($clothingItems)): ?>
    <div style="text-align:center;margin-top:3rem;">
      <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn btn-outline-green" style="padding:.85rem 2.5rem;">
        View All <?= $total ?> Clothing Items
        <i data-lucide="arrow-right" style="width:16px;height:16px;margin-left:.4rem;vertical-align:middle;"></i>
      </a>
    </div>
    <?php endif; ?>

    <?php endif; ?>

  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     LOOKBOOK / STYLING TIPS
══════════════════════════════════════════════════════ -->
<section style="padding:5rem 0;background:#fff;">
  <div class="container">
    <div style="text-align:center;margin-bottom:3rem;">
      <p style="font-size:.75rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.5rem;">Style Inspiration</p>
      <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.6rem,3vw,2.2rem);">Wear GYC Naturals</h2>
      <p style="color:#6B7280;max-width:520px;margin:.75rem auto 0;line-height:1.7;">Complete the look — pair your GYC Naturals hairstyle with our clothing pieces for a full natural beauty moment.</p>
    </div>

    <!-- 3-up lookbook cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1.5rem;">
      <?php
      $looks = [
        ['img'=>'https://images.pexels.com/photos/20370167/pexels-photo-20370167.jpeg?auto=compress&cs=tinysrgb&w=600',
         'title'=>'The Ankara Queen',
         'desc'=>'Box braids + vibrant Ankara dress — pure Calabar energy.',
         'tag'=>'ankara'],
        ['img'=>'https://images.pexels.com/photos/37994018/pexels-photo-37994018.jpeg?auto=compress&cs=tinysrgb&w=600',
         'title'=>'Natural & Neutral',
         'desc'=>'Protective twists + earthy linen tones for your everyday look.',
         'tag'=>'coord-sets'],
        ['img'=>'https://images.pexels.com/photos/37514997/pexels-photo-37514997.jpeg?auto=compress&cs=tinysrgb&w=600',
         'title'=>'The Gele Moment',
         'desc'=>'Knotless braids + a statement headwrap — ready for any occasion.',
         'tag'=>'headwraps'],
      ];
      foreach ($looks as $look):
      ?>
      <div style="border-radius:var(--gyc-radius-xl);overflow:hidden;position:relative;aspect-ratio:3/4;cursor:pointer;background:#111;"
           onmouseover="this.querySelector('.look-overlay').style.opacity='1'" onmouseout="this.querySelector('.look-overlay').style.opacity='0'">
        <img src="<?= $look['img'] ?>" alt="<?= htmlspecialchars($look['title']) ?>"
             loading="lazy" style="width:100%;height:100%;object-fit:cover;transition:transform .6s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
        <!-- Gradient overlay always-on -->
        <div style="position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,.75) 0%, transparent 55%);pointer-events:none;"></div>
        <!-- Text at bottom -->
        <div style="position:absolute;bottom:0;left:0;right:0;padding:1.5rem;color:#fff;">
          <h3 style="font-family:'Playfair Display',serif;font-size:1.15rem;margin:0 0 .35rem;"><?= htmlspecialchars($look['title']) ?></h3>
          <p style="font-size:.82rem;opacity:.8;margin:0 0 1rem;"><?= htmlspecialchars($look['desc']) ?></p>
          <a href="<?= SITE_URL ?>/shop.php?category=clothing&tag=<?= urlencode($look['tag']) ?>"
             class="btn btn-gold" style="font-size:.8rem;padding:.5rem 1.2rem;">Shop This Look</a>
        </div>
        <!-- Hover overlay -->
        <div class="look-overlay" style="position:absolute;inset:0;background:rgba(21,83,52,.45);opacity:0;transition:opacity .3s;pointer-events:none;"></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     SIZE GUIDE
══════════════════════════════════════════════════════ -->
<section style="padding:4rem 0;background:#F8FAF9;">
  <div class="container" style="max-width:760px;">
    <div style="text-align:center;margin-bottom:2rem;">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:.5rem;">Size Guide</h2>
      <p style="color:#6B7280;font-size:.875rem;">All measurements in centimetres (cm). When in between sizes, size up.</p>
    </div>
    <div style="background:#fff;border-radius:var(--gyc-radius-xl);overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.06);">
      <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
        <thead>
          <tr style="background:var(--gyc-green-900);color:#fff;">
            <th style="padding:12px 16px;text-align:left;">Size</th>
            <th style="padding:12px 16px;text-align:center;">Bust (cm)</th>
            <th style="padding:12px 16px;text-align:center;">Waist (cm)</th>
            <th style="padding:12px 16px;text-align:center;">Hips (cm)</th>
            <th style="padding:12px 16px;text-align:center;">NG Size</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sizes = [
            ['XS','80–84','62–66','88–92','6–8'],
            ['S', '84–88','66–70','92–96','8–10'],
            ['M', '88–94','70–76','96–102','10–12'],
            ['L', '94–102','76–84','102–110','12–14'],
            ['XL','102–110','84–92','110–118','14–16'],
            ['XXL','110–120','92–102','118–128','16–18'],
          ];
          foreach ($sizes as $i => $s):
          ?>
          <tr style="background:<?= $i%2===0 ? '#fff' : '#F8FAF9' ?>;">
            <td style="padding:11px 16px;font-weight:700;color:var(--gyc-green-700);"><?= $s[0] ?></td>
            <td style="padding:11px 16px;text-align:center;color:#374151;"><?= $s[1] ?></td>
            <td style="padding:11px 16px;text-align:center;color:#374151;"><?= $s[2] ?></td>
            <td style="padding:11px 16px;text-align:center;color:#374151;"><?= $s[3] ?></td>
            <td style="padding:11px 16px;text-align:center;color:#6B7280;font-size:.82rem;"><?= $s[4] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p style="font-size:.8rem;color:#9CA3AF;margin-top:1rem;text-align:center;">Need help with sizing? <a href="<?= SITE_URL ?>/contact.php" style="color:var(--gyc-green-600);">Contact us</a> — we're happy to advise before you order.</p>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     WHY BUY FROM US
══════════════════════════════════════════════════════ -->
<section style="padding:4rem 0;background:var(--gyc-green-900);color:#fff;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:2rem;text-align:center;">
      <?php
      $perks = [
        ['🚚','Fast Nationwide Delivery','Same-day in Calabar, 1–3 days across Nigeria.'],
        ['🔄','Easy Returns','7-day returns on unworn items — no questions asked.'],
        ['🧵','Quality Fabrics','We source from trusted Nigerian fabric markets.'],
        ['📏','Custom Sizing','Need a custom size? Reach us on WhatsApp.'],
      ];
      foreach ($perks as $p):
      ?>
      <div>
        <div style="font-size:2.2rem;margin-bottom:.75rem;"><?= $p[0] ?></div>
        <h3 style="font-size:.95rem;font-weight:700;margin:0 0 .4rem;color:var(--gyc-gold);"><?= $p[1] ?></h3>
        <p style="font-size:.82rem;opacity:.75;line-height:1.65;margin:0;"><?= $p[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════
     CTA + WHATSAPP CUSTOM ORDER
══════════════════════════════════════════════════════ -->
<section style="padding:5rem 0;background:#FFF9F0;text-align:center;">
  <div class="container" style="max-width:600px;">
    <div style="font-size:2.5rem;margin-bottom:1rem;">✂️</div>
    <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,3vw,2rem);margin-bottom:1rem;">Need a Custom Piece?</h2>
    <p style="color:#6B7280;line-height:1.75;margin-bottom:2rem;">
      Want a custom Ankara outfit, headwrap styling, or a co-ord set in your exact measurements? We can help. Chat with us on WhatsApp and we'll create something just for you.
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <?php
      $waMsg  = "Hello GYC Naturals! I'm interested in a custom clothing piece. Can you help?";
      $waUrl  = 'https://wa.me/' . preg_replace('/\D/','',(getSetting('site_whatsapp') ?: SITE_WHATSAPP)) . '?text=' . rawurlencode($waMsg);
      ?>
      <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp" style="padding:.9rem 2rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        Chat Custom Order
      </a>
      <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn btn-outline-green" style="padding:.9rem 2rem;">Browse All Clothing</a>
    </div>
  </div>
</section>

<script>
// Notify me when collection launches
function handleNotify(e) {
    e.preventDefault();
    const email = document.getElementById('notify-email').value;
    // Store in localStorage for now (no backend endpoint needed for empty state)
    localStorage.setItem('gyc_notify_clothing', email);
    document.getElementById('notify-form').style.display = 'none';
    document.getElementById('notify-msg').style.display = 'block';
}

// Add to cart with visual feedback
function addToCart(productId, qty, btn) {
    const original = btn.textContent;
    btn.disabled = true;
    btn.textContent = '…';
    fetch('<?= SITE_URL ?>/api/add-to-cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'product_id=' + productId + '&quantity=' + qty
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            btn.textContent = '✓ Added';
            btn.style.background = 'var(--gyc-green-900)';
            // Update cart count if present
            const badge = document.querySelector('.cart-badge');
            if (badge && d.item_count) badge.textContent = d.item_count;
            setTimeout(function() {
                btn.textContent = original;
                btn.style.background = '';
                btn.disabled = false;
            }, 1800);
        } else {
            btn.textContent = original;
            btn.disabled = false;
            alert(d.message || 'Could not add to bag. Please try again.');
        }
    })
    .catch(function() {
        btn.textContent = original;
        btn.disabled = false;
    });
}

// Toggle wishlist
function toggleWishlist(productId, btn) {
    fetch('<?= SITE_URL ?>/api/add-to-wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'product_id=' + productId
    })
    .then(r => r.json())
    .then(d => {
        const icon = btn.querySelector('[data-lucide]');
        if (d.success) {
            icon.style.color = d.wishlisted ? 'var(--gyc-terra)' : '#9CA3AF';
            icon.setAttribute('fill', d.wishlisted ? 'var(--gyc-terra)' : 'none');
        }
    });
}

// Init Lucide icons
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
