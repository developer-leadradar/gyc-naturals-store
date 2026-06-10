<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug   = sanitize($_GET['slug'] ?? '');
$bundle = $slug ? getBundleBySlug($slug) : null;

if (!$bundle) {
    redirect(SITE_URL . '/shop.php');
}

$items  = getBundleItems($bundle['id']);
$bPrice = getBundlePrice($bundle['id']);

// Other bundles
$otherBundles = getDB()->fetchAll(
    "SELECT * FROM bundles WHERE id != ? AND is_active = 1 ORDER BY is_featured DESC LIMIT 3",
    [$bundle['id']]
);

$pageTitle       = htmlspecialchars($bundle['name']) . ' Bundle — GYC Naturals Calabar';
$pageDescription = $bundle['description']
    ? htmlspecialchars(substr($bundle['description'], 0, 155))
    : 'Get the ' . $bundle['name'] . ' bundle at GYC Naturals and save on your hair care routine.';

require_once __DIR__ . '/includes/header.php';
?>

<div style="min-height:72px;"></div>

<section style="padding:3rem 0 5rem;">
  <div class="container">

    <!-- Breadcrumb -->
    <nav aria-label="Breadcrumb" style="font-size:.82rem;color:#888;margin-bottom:2rem;">
      <a href="<?= SITE_URL ?>">Home</a> /
      <a href="<?= SITE_URL ?>/shop.php">Shop</a> /
      <a href="<?= SITE_URL ?>/shop.php#bundles">Bundles</a> /
      <span style="color:var(--gyc-dark);"><?= htmlspecialchars($bundle['name']) ?></span>
    </nav>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:start;">

      <!-- Bundle image -->
      <div>
        <div style="border-radius:var(--gyc-radius-lg);overflow:hidden;aspect-ratio:1/1;background:var(--gyc-green-100);">
          <img src="<?= htmlspecialchars($bundle['image'] ?? '') ?>"
               alt="<?= htmlspecialchars($bundle['name']) ?>"
               loading="eager"
               style="width:100%;height:100%;object-fit:cover;">
        </div>

        <!-- What's included thumbnails -->
        <?php if (!empty($items)): ?>
        <div style="margin-top:1.25rem;">
          <h4 style="font-size:.82rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.75rem;">Included Products</h4>
          <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
            <?php foreach ($items as $item): ?>
            <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>"
               title="<?= htmlspecialchars($item['name']) ?>"
               style="display:block;">
              <div style="width:56px;height:56px;border-radius:var(--gyc-radius);overflow:hidden;border:2px solid var(--gyc-green-100);">
                <img src="<?= htmlspecialchars($item['image']) ?>"
                     alt="<?= htmlspecialchars($item['name']) ?>"
                     style="width:100%;height:100%;object-fit:cover;">
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- Bundle info -->
      <div>
        <span style="font-size:.72rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gyc-gold-600);">
          Bundle &amp; Save
        </span>
        <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.5rem,3vw,2.2rem);color:var(--gyc-dark);margin:.4rem 0 1rem;line-height:1.2;">
          <?= htmlspecialchars($bundle['name']) ?>
        </h1>

        <?php if ($bundle['description']): ?>
        <p style="color:#444;line-height:1.75;font-size:.95rem;margin-bottom:1.5rem;">
          <?= nl2br(htmlspecialchars($bundle['description'])) ?>
        </p>
        <?php endif; ?>

        <!-- Price block -->
        <?php if ($bPrice): ?>
        <div style="background:var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
          <div style="display:flex;align-items:baseline;gap:1rem;flex-wrap:wrap;margin-bottom:.5rem;">
            <span style="font-family:'Playfair Display',serif;font-size:2rem;color:var(--gyc-green-700);font-weight:700;"><?= formatPrice($bPrice['total']) ?></span>
            <span style="text-decoration:line-through;color:#aaa;font-size:1.1rem;"><?= formatPrice($bPrice['subtotal']) ?></span>
            <span style="background:var(--gyc-terra);color:#fff;font-size:.78rem;font-weight:700;padding:.25rem .75rem;border-radius:20px;">
              You Save <?= formatPrice($bPrice['discount']) ?> (<?= round($bPrice['discount_pct']) ?>%)
            </span>
          </div>
          <p style="font-size:.82rem;color:#666;margin:0;">Bundle price includes <?= count($items) ?> product<?= count($items) !== 1 ? 's' : '' ?></p>
        </div>
        <?php endif; ?>

        <!-- Bundle items breakdown -->
        <?php if (!empty($items)): ?>
        <div style="margin-bottom:1.5rem;">
          <h3 style="font-size:.82rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--gyc-green-500);margin-bottom:.75rem;">What's Inside</h3>
          <div style="display:flex;flex-direction:column;gap:.6rem;">
            <?php foreach ($items as $item): ?>
            <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;background:#fff;border:1px solid var(--gyc-green-100);border-radius:var(--gyc-radius);">
              <img src="<?= htmlspecialchars($item['image']) ?>"
                   alt="<?= htmlspecialchars($item['name']) ?>"
                   style="width:40px;height:40px;object-fit:cover;border-radius:6px;flex-shrink:0;">
              <div style="flex:1;min-width:0;">
                <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>"
                   style="font-size:.87rem;font-weight:600;color:var(--gyc-dark);text-decoration:none;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                  <?= htmlspecialchars($item['name']) ?>
                </a>
                <?php if ($item['volume_ml']): ?>
                <span style="font-size:.75rem;color:#888;"><?= $item['volume_ml'] ?>ml</span>
                <?php endif; ?>
              </div>
              <span style="font-size:.85rem;font-weight:600;color:var(--gyc-green-700);flex-shrink:0;"><?= formatPrice($item['price']) ?></span>
              <?php if ($item['quantity'] > 1): ?>
              <span style="font-size:.75rem;color:#888;flex-shrink:0;">×<?= $item['quantity'] ?></span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Add whole bundle to cart -->
        <div style="display:flex;gap:.75rem;margin-bottom:1.25rem;flex-wrap:wrap;">
          <button class="btn btn-gold btn-lg" id="add-bundle-btn" style="flex:1;justify-content:center;"
                  data-bundle-id="<?= $bundle['id'] ?>">
            <i data-lucide="shopping-bag" style="width:18px;height:18px;"></i>
            Add Bundle to Bag
          </button>
        </div>

        <!-- WhatsApp -->
        <?php
        $waPhone = getSetting('site_whatsapp');
        if ($waPhone):
            $waMsg  = 'Hi! I am interested in the ' . $bundle['name'] . ' bundle. How do I order?';
            $waUrl  = whatsappMessage($waPhone, $waMsg);
        ?>
        <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" rel="noopener" class="btn btn-whatsapp" style="width:100%;justify-content:center;margin-bottom:1.5rem;">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          Order via WhatsApp
        </a>
        <?php endif; ?>

        <!-- Trust badges -->
        <div style="display:flex;gap:1rem;flex-wrap:wrap;font-size:.78rem;color:#666;">
          <span style="display:flex;align-items:center;gap:.35rem;"><i data-lucide="truck" style="width:14px;height:14px;color:var(--gyc-green-600);"></i>Nationwide delivery</span>
          <span style="display:flex;align-items:center;gap:.35rem;"><i data-lucide="shield-check" style="width:14px;height:14px;color:var(--gyc-green-600);"></i>100% natural</span>
          <span style="display:flex;align-items:center;gap:.35rem;"><i data-lucide="refresh-ccw" style="width:14px;height:14px;color:var(--gyc-green-600);"></i>14-day returns</span>
        </div>
      </div>
    </div>

    <!-- Other bundles -->
    <?php if (!empty($otherBundles)): ?>
    <div style="margin-top:5rem;">
      <h2 style="font-family:'Playfair Display',serif;font-size:1.5rem;margin-bottom:1.75rem;color:var(--gyc-dark);">More Bundles</h2>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.5rem;">
        <?php foreach ($otherBundles as $ob):
          $obPrice = getBundlePrice($ob['id']);
        ?>
        <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);overflow:hidden;">
          <a href="<?= SITE_URL ?>/bundle.php?slug=<?= urlencode($ob['slug']) ?>" style="display:block;height:200px;overflow:hidden;background:var(--gyc-green-100);">
            <img src="<?= htmlspecialchars($ob['image'] ?? '') ?>" alt="<?= htmlspecialchars($ob['name']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
          </a>
          <div style="padding:1.1rem;">
            <h3 style="font-family:'Playfair Display',serif;font-size:1rem;margin:0 0 .5rem;">
              <a href="<?= SITE_URL ?>/bundle.php?slug=<?= urlencode($ob['slug']) ?>" style="color:var(--gyc-dark);text-decoration:none;"><?= htmlspecialchars($ob['name']) ?></a>
            </h3>
            <?php if ($obPrice): ?>
            <div style="display:flex;align-items:center;gap:.75rem;">
              <span style="font-weight:700;color:var(--gyc-green-700);"><?= formatPrice($obPrice['total']) ?></span>
              <span style="font-size:.75rem;background:var(--gyc-gold-100);color:var(--gyc-gold-700);padding:.15rem .5rem;border-radius:20px;">Save <?= round($obPrice['discount_pct']) ?>%</span>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

  </div>
</section>

<script>
document.getElementById('add-bundle-btn').addEventListener('click', function() {
  const btn      = this;
  const bundleId = btn.dataset.bundleId;
  const origTxt  = btn.innerHTML;
  btn.disabled   = true;
  btn.innerHTML  = '<span style="opacity:.7">Adding…</span>';

  // Add all bundle items to cart
  const items = <?= json_encode(array_map(fn($i) => ['id' => $i['product_id'], 'qty' => $i['quantity']], $items)) ?>;
  const bundleIdNum = <?= (int)$bundle['id'] ?>;
  let promises = items.map(function(item) {
    const body = 'product_id=' + item.id + '&qty=' + item.qty + '&bundle_id=' + bundleIdNum;
    return fetch('<?= SITE_URL ?>/api/add-to-cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
      body: body
    }).then(r => r.json());
  });

  Promise.all(promises).then(function(results) {
    btn.disabled  = false;
    btn.innerHTML = '<i data-lucide="check" style="width:18px;height:18px;"></i> Added to Bag!';
    btn.style.background = 'var(--gyc-green-600)';
    if (typeof lucide !== 'undefined') lucide.createIcons();
    // Update cart count in header
    const lastResult = results[results.length - 1];
    if (lastResult && lastResult.cart_count !== undefined) {
      document.querySelectorAll('.cart-count').forEach(function(el) {
        el.textContent = lastResult.cart_count;
      });
    }
    setTimeout(function() {
      btn.innerHTML = origTxt;
      btn.style.background = '';
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }, 2500);
  }).catch(function() {
    btn.disabled = false;
    btn.innerHTML = origTxt;
    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
