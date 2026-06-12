<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$clothingCat = getDB()->fetchOne("SELECT * FROM product_categories WHERE slug = 'clothing'");
if (!$clothingCat) $clothingCat = getDB()->fetchOne("SELECT * FROM product_categories WHERE id = 4");
$catId = $clothingCat ? $clothingCat['id'] : 4;

$clothingItems = getAllProducts(['category_id' => $catId], 24, 0);
$total         = countProducts(['category_id' => $catId]);

$pageTitle       = 'The Boutique — Fashion for Every Occasion | GYC Naturals Calabar';
$pageDescription = 'Shop GYC Naturals boutique fashion — carefully selected everyday clothing for the modern Nigerian woman. Dresses, casual wear, office looks & more.';
require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<!-- HERO -->
<section style="position:relative;overflow:hidden;min-height:480px;display:flex;align-items:center;background:#1a1a2e;">
  <div style="position:absolute;inset:0;background-image:url('https://images.pexels.com/photos/3762875/pexels-photo-3762875.jpeg?auto=compress&cs=tinysrgb&w=1400');background-size:cover;background-position:center top;opacity:.38;"></div>
  <div style="position:absolute;top:0;left:0;right:0;height:5px;background:linear-gradient(90deg,var(--gyc-gold),var(--gyc-terra),var(--gyc-green-600),var(--gyc-gold));"></div>

  <div class="container" style="position:relative;z-index:2;padding:5rem 0;">
    <div style="max-width:580px;">
      <p style="font-size:.72rem;font-weight:700;letter-spacing:.22em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">GYC Naturals — The Boutique</p>
      <h1 style="font-family:'Playfair Display',serif;font-size:clamp(2.2rem,5vw,3.5rem);color:#fff;line-height:1.2;margin-bottom:1.25rem;">
        Dress With<br><em style="color:var(--gyc-gold);">Confidence</em>
      </h1>
      <p style="font-size:1.05rem;color:rgba(255,255,255,.82);line-height:1.75;margin-bottom:2rem;max-width:480px;">
        Everyday fashion for the modern Nigerian woman — carefully selected pieces for every occasion.
      </p>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;">
        <a href="#collection" class="btn btn-gold" style="padding:.9rem 2rem;">Shop the Collection</a>
        <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn" style="padding:.9rem 2rem;background:rgba(255,255,255,.12);color:#fff;border:1.5px solid rgba(255,255,255,.35);">View All Clothing</a>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORY CHIPS -->
<section style="background:#FFF9F0;padding:3rem 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.85rem;">
      <?php
      $fashionCats = [
        ['👗','Dresses',       'dresses'],
        ['👚','Tops & Blouses','tops'],
        ['👖','Trousers',      'trousers'],
        ['💼','Work Wear',     'workwear'],
        ['🌙','Evening Wear',  'evening'],
        ['👟','Casual Wear',   'casual'],
      ];
      foreach ($fashionCats as $fc):
      ?>
      <a href="<?= SITE_URL ?>/shop.php?category=clothing"
         style="display:flex;flex-direction:column;align-items:center;gap:.4rem;padding:1.25rem .75rem;background:#fff;border:1.5px solid #F0E8D8;border-radius:var(--gyc-radius-lg);text-decoration:none;color:var(--gyc-dark);transition:all .2s;text-align:center;"
         onmouseover="this.style.borderColor='var(--gyc-gold)';this.style.transform='translateY(-2px)'"
         onmouseout="this.style.borderColor='#F0E8D8';this.style.transform='translateY(0)'">
        <span style="font-size:1.6rem;"><?= $fc[0] ?></span>
        <span style="font-size:.8rem;font-weight:600;"><?= $fc[1] ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- BRAND STRIP -->
<section style="background:var(--gyc-green-900);color:#fff;padding:3.5rem 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:3.5rem;align-items:center;">
      <div>
        <p style="font-size:.72rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-gold);margin-bottom:.75rem;">About the Boutique</p>
        <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,2.5vw,2rem);margin-bottom:1.25rem;line-height:1.3;">Your Style,<br>Your Story.</h2>
        <p style="opacity:.85;line-height:1.8;margin-bottom:1rem;">At GYC Naturals, we believe looking good goes hand in hand with feeling good. Our boutique stocks everyday fashion pieces — from casual daywear to polished office looks and elegant evening options.</p>
        <p style="opacity:.72;line-height:1.8;font-size:.9rem;">Every piece is handpicked for quality, fit, and wearability — clothes that work with your lifestyle, whether you're running errands in Calabar or stepping out for the evening.</p>
        <div style="display:flex;gap:2rem;margin-top:2rem;">
          <div>
            <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--gyc-gold);">Fresh</div>
            <div style="font-size:.7rem;opacity:.65;text-transform:uppercase;letter-spacing:.08em;">New Arrivals Weekly</div>
          </div>
          <div>
            <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--gyc-gold);">Fast</div>
            <div style="font-size:.7rem;opacity:.65;text-transform:uppercase;letter-spacing:.08em;">Nationwide Delivery</div>
          </div>
          <div>
            <div style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:var(--gyc-gold);">Easy</div>
            <div style="font-size:.7rem;opacity:.65;text-transform:uppercase;letter-spacing:.08em;">Returns Policy</div>
          </div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <img src="https://images.pexels.com/photos/1036623/pexels-photo-1036623.jpeg?auto=compress&cs=tinysrgb&w=400"
             alt="Fashion boutique" style="width:100%;aspect-ratio:3/4;object-fit:cover;border-radius:var(--gyc-radius-lg);" loading="lazy">
        <img src="https://images.pexels.com/photos/2862259/pexels-photo-2862259.jpeg?auto=compress&cs=tinysrgb&w=400"
             alt="Fashion boutique" style="width:100%;aspect-ratio:3/4;object-fit:cover;border-radius:var(--gyc-radius-lg);margin-top:1.5rem;" loading="lazy">
      </div>
    </div>
  </div>
</section>

<!-- PRODUCT COLLECTION -->
<section id="collection" style="padding:5rem 0;background:#FAFAF8;">
  <div class="container">

    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:2.5rem;flex-wrap:wrap;gap:1rem;">
      <div>
        <p style="font-size:.72rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:var(--gyc-terra);margin-bottom:.4rem;">The Collection</p>
        <h2 style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,2.5vw,2rem);margin:0;">
          <?= $total > 0 ? 'Shop All Styles' : 'Coming Soon' ?>
        </h2>
        <?php if ($total > 0): ?>
        <p style="color:#6B7280;font-size:.875rem;margin-top:.4rem;"><?= $total ?> pieces available</p>
        <?php endif; ?>
      </div>
      <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn btn-outline-green" style="font-size:.85rem;padding:.55rem 1.2rem;">View in Shop</a>
    </div>

    <?php if (empty($clothingItems)): ?>
    <div style="text-align:center;padding:6rem 2rem;background:#fff;border-radius:var(--gyc-radius-xl);border:2px dashed #E5E7EB;">
      <div style="font-size:4rem;margin-bottom:1rem;">👗</div>
      <h3 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.75rem;">Boutique Launching Soon</h3>
      <p style="color:#6B7280;max-width:380px;margin:0 auto 2rem;line-height:1.7;">
        Our clothing selection is being curated. Check back soon or chat with us on WhatsApp to ask about specific pieces.
      </p>
      <a href="<?= SITE_URL ?>/shop.php" class="btn btn-green">Browse All Products</a>
    </div>

    <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.5rem;">
      <?php foreach ($clothingItems as $item):
        $img      = $item['image'] ?? 'https://images.pexels.com/photos/1036623/pexels-photo-1036623.jpeg?auto=compress&cs=tinysrgb&w=400';
        $price    = formatPrice($item['price']);
        $oldPrice = (!empty($item['compare_at_price']) && $item['compare_at_price'] > $item['price']) ? formatPrice($item['compare_at_price']) : null;
        $inStock  = ($item['stock_quantity'] ?? 1) > 0;
      ?>
      <article style="background:#fff;border:1.5px solid #E9F0EB;border-radius:var(--gyc-radius-lg);overflow:hidden;transition:all .25s;position:relative;"
               onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 32px rgba(0,0,0,.1)'"
               onmouseout="this.style.transform='';this.style.boxShadow=''">
        <?php if (!$inStock): ?>
        <span style="position:absolute;top:10px;left:10px;z-index:2;background:#9CA3AF;color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:99px;">SOLD OUT</span>
        <?php endif; ?>
        <?php if ($oldPrice): ?>
        <span style="position:absolute;top:10px;left:10px;z-index:2;background:var(--gyc-terra);color:#fff;font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:99px;">SALE</span>
        <?php endif; ?>
        <button onclick="toggleWishlist(<?= $item['id'] ?>, this)" title="Save to Wishlist"
                style="position:absolute;top:10px;right:10px;z-index:2;background:#fff;border:none;width:32px;height:32px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.15);">
          <i data-lucide="heart" style="width:16px;height:16px;color:#9CA3AF;"></i>
        </button>
        <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>">
          <div style="aspect-ratio:3/4;overflow:hidden;background:#F3F4F6;">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                 loading="lazy" style="width:100%;height:100%;object-fit:cover;transition:transform .4s;"
                 onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
          </div>
        </a>
        <div style="padding:1rem;">
          <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>" style="text-decoration:none;color:inherit;">
            <h3 style="font-size:.9rem;font-weight:700;margin:0 0 .35rem;color:var(--gyc-dark);"><?= htmlspecialchars($item['name']) ?></h3>
          </a>
          <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-top:.75rem;">
            <div>
              <span style="font-weight:800;font-size:1rem;color:var(--gyc-dark);"><?= $price ?></span>
              <?php if ($oldPrice): ?><span style="font-size:.8rem;color:#9CA3AF;text-decoration:line-through;margin-left:.3rem;"><?= $oldPrice ?></span><?php endif; ?>
            </div>
            <?php if ($inStock): ?>
            <button onclick="addToCartQuick(<?= $item['id'] ?>, this)"
                    style="background:var(--gyc-green-700);color:#fff;border:none;padding:.45rem .85rem;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer;">
              Add to Bag
            </button>
            <?php else: ?>
            <span style="font-size:.78rem;color:#9CA3AF;">Out of stock</span>
            <?php endif; ?>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if ($total > count($clothingItems)): ?>
    <div style="text-align:center;margin-top:3rem;">
      <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn btn-outline-green" style="padding:.85rem 2.5rem;">
        View All <?= $total ?> Clothing Items <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
      </a>
    </div>
    <?php endif; ?>

    <?php endif; ?>
  </div>
</section>

<!-- SIZE GUIDE -->
<section style="padding:4rem 0;background:#F8FAF9;">
  <div class="container" style="max-width:720px;">
    <div style="text-align:center;margin-bottom:2rem;">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:.5rem;">Size Guide</h2>
      <p style="color:#6B7280;font-size:.875rem;">All measurements in centimetres (cm). When between sizes, size up.</p>
    </div>
    <div style="background:#fff;border-radius:var(--gyc-radius-xl);overflow:hidden;box-shadow:0 2px 20px rgba(0,0,0,.06);">
      <table style="width:100%;border-collapse:collapse;font-size:.875rem;">
        <thead>
          <tr style="background:var(--gyc-green-900);color:#fff;">
            <th style="padding:11px 16px;text-align:left;">Size</th>
            <th style="padding:11px 16px;text-align:center;">Bust (cm)</th>
            <th style="padding:11px 16px;text-align:center;">Waist (cm)</th>
            <th style="padding:11px 16px;text-align:center;">Hips (cm)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sizes = [['XS','80–84','62–66','88–92'],['S','84–88','66–70','92–96'],['M','88–94','70–76','96–102'],
                    ['L','94–102','76–84','102–110'],['XL','102–110','84–92','110–118'],['XXL','110–120','92–102','118–128']];
          foreach ($sizes as $i => $s):
          ?>
          <tr style="background:<?= $i%2===0?'#fff':'#F8FAF9' ?>;">
            <td style="padding:10px 16px;font-weight:700;color:var(--gyc-green-700);"><?= $s[0] ?></td>
            <td style="padding:10px 16px;text-align:center;color:#374151;"><?= $s[1] ?></td>
            <td style="padding:10px 16px;text-align:center;color:#374151;"><?= $s[2] ?></td>
            <td style="padding:10px 16px;text-align:center;color:#374151;"><?= $s[3] ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p style="font-size:.8rem;color:#9CA3AF;margin-top:1rem;text-align:center;">Need help? <a href="<?= SITE_URL ?>/contact.php" style="color:var(--gyc-green-600);">Contact us</a> or chat on WhatsApp — we're happy to advise before you order.</p>
  </div>
</section>

<!-- PERKS BAR -->
<section style="background:var(--gyc-green-900);color:#fff;padding:3.5rem 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:2rem;text-align:center;">
      <?php foreach ([
        ['🚚','Fast Delivery','Same-day in Calabar, 1–3 days nationwide.'],
        ['🔄','Easy Returns','7-day returns on unworn items.'],
        ['✨','Curated Picks','Every piece handpicked for quality & fit.'],
        ['💬','Styling Advice','Chat with us on WhatsApp anytime.'],
      ] as $p): ?>
      <div>
        <div style="font-size:2rem;margin-bottom:.6rem;"><?= $p[0] ?></div>
        <h3 style="font-size:.9rem;font-weight:700;margin:0 0 .35rem;color:var(--gyc-gold);"><?= $p[1] ?></h3>
        <p style="font-size:.8rem;opacity:.72;line-height:1.6;margin:0;"><?= $p[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- WHATSAPP CTA -->
<section style="padding:4rem 0;background:#FFF9F0;text-align:center;">
  <div class="container" style="max-width:540px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;margin-bottom:.75rem;">Looking for Something Specific?</h2>
    <p style="color:#6B7280;line-height:1.75;margin-bottom:2rem;">
      Can't find your size, want styling advice, or need a specific piece? Chat with us on WhatsApp — we respond quickly.
    </p>
    <?php
    $waMsg = "Hello GYC Naturals! I'd like some help with your clothing boutique.";
    $waUrl = 'https://wa.me/' . preg_replace('/\D/', '', (getSetting('site_whatsapp') ?: SITE_WHATSAPP)) . '?text=' . rawurlencode($waMsg);
    ?>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
      <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp" style="padding:.9rem 2rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
        Chat on WhatsApp
      </a>
      <a href="<?= SITE_URL ?>/shop.php?category=clothing" class="btn btn-outline-green" style="padding:.9rem 2rem;">Browse All Clothing</a>
    </div>
  </div>
</section>

<script>
function addToCartQuick(id, btn) {
  var orig = btn.textContent;
  btn.disabled = true; btn.textContent = '…';
  fetch('<?= SITE_URL ?>/api/add-to-cart.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: 'product_id='+id+'&quantity=1'
  }).then(function(r){ return r.json(); }).then(function(d){
    if (d.success) {
      btn.textContent = '✓ Added'; btn.style.background = 'var(--gyc-green-900)';
      var badge = document.getElementById('cart-count');
      if (badge && d.item_count) { badge.textContent = d.item_count; badge.style.display = ''; }
      setTimeout(function(){ btn.textContent = orig; btn.style.background = ''; btn.disabled = false; }, 1800);
    } else { btn.textContent = orig; btn.disabled = false; alert(d.message || 'Try again.'); }
  }).catch(function(){ btn.textContent = orig; btn.disabled = false; });
}
function toggleWishlist(id, btn) {
  fetch('<?= SITE_URL ?>/api/add-to-wishlist.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded','X-Requested-With':'XMLHttpRequest'},
    body: 'product_id='+id
  }).then(function(r){ return r.json(); }).then(function(d){
    var icon = btn.querySelector('[data-lucide]');
    if (d.success && icon) { icon.style.color = d.wishlisted ? 'var(--gyc-terra)' : '#9CA3AF'; }
  });
}
if (typeof lucide !== 'undefined') lucide.createIcons();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
