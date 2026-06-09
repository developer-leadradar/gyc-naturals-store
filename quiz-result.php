<?php
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(SITE_URL . '/quiz.php');
}

verifyCsrf();

$hairType  = sanitize($_POST['hair_type'] ?? '');
$concern   = sanitize($_POST['concern']   ?? '');
$lifestyle = sanitize($_POST['lifestyle'] ?? '');
$goal      = sanitize($_POST['goal']      ?? '');
$name      = sanitize($_POST['name']      ?? '');
$email     = sanitize($_POST['email']     ?? '');

// Build recommendation logic
$recommendations = [];
$styleRecs       = [];

// Product recommendations based on concern + hair type
if ($concern === 'growth' || $goal === 'edges' || $goal === 'length') {
    $recommendations[] = 2; // Jamaican Black Castor Oil
    $recommendations[] = 8; // Herbal Growth Scalp Oil
    $recommendations[] = 3; // Peppermint Scalp Treatment
}
if ($concern === 'moisture' || in_array($hairType, ['4C','4B'])) {
    $recommendations[] = 1; // Shea Butter Deep Moisturizer
    $recommendations[] = 5; // Curl Defining Leave-In Conditioner
}
if ($concern === 'breakage' || $goal === 'thickness') {
    $recommendations[] = 7; // Keratin Protein Reconstructor
    $recommendations[] = 10; // Detangling Pre-Poo
}
if ($concern === 'definition') {
    $recommendations[] = 5; // Leave-In Conditioner
    $recommendations[] = 6; // Fermented Rice Water Rinse
}

$recommendations = array_unique($recommendations);
$recommendations = array_slice($recommendations, 0, 4);

// Style recommendations based on lifestyle + concern
if ($lifestyle === 'protective' || $concern === 'breakage' || $concern === 'growth') {
    $styleRecs = ['knotless', 'box_braids'];
} elseif ($lifestyle === 'minimal') {
    $styleRecs = ['cornrows', 'knotless'];
} elseif ($lifestyle === 'styling') {
    $styleRecs = ['knotless', 'twists', 'natural'];
} else {
    $styleRecs = ['natural', 'twists'];
}

// Fetch recommended products
$products = [];
if (!empty($recommendations)) {
    $placeholders = implode(',', array_fill(0, count($recommendations), '?'));
    $products = getDB()->fetchAll(
        "SELECT * FROM products WHERE id IN ($placeholders) AND is_active=1",
        $recommendations
    );
}

// Fetch recommended styles
$styleImages = [];
if (!empty($styleRecs)) {
    $placeholders = implode(',', array_fill(0, count($styleRecs), '?'));
    $styleImages  = getDB()->fetchAll(
        "SELECT * FROM gallery_images WHERE style_type IN ($placeholders) AND is_active=1 ORDER BY is_featured DESC LIMIT 4",
        $styleRecs
    );
}

// Build bundle recommendation
$bundleRec = null;
if ($concern === 'growth' || $goal === 'edges') {
    $bundleRec = getBundleBySlug('4c-growth-regimen');
} elseif ($lifestyle === 'protective') {
    $bundleRec = getBundleBySlug('braid-ready-kit');
} elseif ($concern === 'moisture') {
    $bundleRec = getBundleBySlug('deep-moisture-bundle');
}

// Save quiz result
$quizData = [
    'hair_type'  => $hairType,
    'concern'    => $concern,
    'lifestyle'  => $lifestyle,
    'goal'       => $goal,
    'name'       => $name,
    'email'      => $email,
];
$resultId = null;
try {
    $resultId = getDB()->insert('quiz_results', [
        'hair_type'       => $hairType,
        'concern'         => $concern,
        'lifestyle'       => $lifestyle,
        'goal'            => $goal,
        'customer_name'   => $name ?: null,
        'customer_email'  => $email ?: null,
        'recommendation'  => json_encode(['products' => $recommendations, 'styles' => $styleRecs]),
    ]);
} catch (Exception $e) { /* ignore if table missing columns */ }

// Send email if provided
if ($email) {
    $emailHtml = '<h2>Your GYC Naturals Hair Consultation Results</h2>';
    $emailHtml .= '<p>Hello ' . ($name ?: 'there') . ',</p>';
    $emailHtml .= '<p>Based on your responses, here are our personalised recommendations for your <strong>' . strtoupper($hairType) . '</strong> hair.</p>';
    foreach ($products as $p) {
        $emailHtml .= '<p><strong>' . htmlspecialchars($p['name']) . '</strong> — ' . formatPrice($p['price']) . '</p>';
    }
    $emailHtml .= '<p><a href="' . SITE_URL . '/shop.php">Shop All Products</a> | <a href="' . SITE_URL . '/book-appointment.php">Book Appointment</a></p>';
    sendEmail($email, 'Your GYC Naturals Hair Consultation Results', $emailHtml);
}

$pageTitle = 'Your Hair Consultation Results — GYC Naturals';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Results hero -->
<div style="background:linear-gradient(135deg,var(--gyc-green-900),var(--gyc-gold-700));padding:4rem 0 2.5rem;text-align:center;color:#fff;">
  <div class="container">
    <div style="font-size:3rem;margin-bottom:0.75rem;">👑</div>
    <h1 style="font-family:'Playfair Display',serif;font-size:clamp(1.75rem,4vw,2.8rem);color:#fff;margin:0 0 0.75rem;">
      <?= $name ? 'Here Are Your Results, ' . htmlspecialchars(explode(' ', $name)[0]) . '!' : 'Your Personalised Results' ?>
    </h1>
    <p style="color:rgba(255,255,255,0.8);max-width:480px;margin:0 auto;font-size:0.95rem;line-height:1.65;">
      Based on your <?= strtoupper(htmlspecialchars($hairType)) ?> hair type
      and focus on <?= htmlspecialchars($concern) ?>,
      here is what we recommend.
    </p>
  </div>
</div>

<section style="padding:3rem 0 5rem;background:#F8FAF9;">
  <div class="container">

    <!-- Hair profile summary -->
    <div style="background:#fff;border:1.5px solid var(--gyc-green-100);border-radius:var(--gyc-radius-lg);padding:1.5rem 2rem;margin-bottom:3rem;display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;">
      <div style="text-align:center;">
        <div style="font-size:1.8rem;margin-bottom:0.3rem;">🌀</div>
        <strong style="display:block;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Hair Type</strong>
        <span style="font-weight:700;font-size:1rem;color:var(--gyc-dark);"><?= strtoupper(htmlspecialchars($hairType)) ?></span>
      </div>
      <div style="text-align:center;">
        <div style="font-size:1.8rem;margin-bottom:0.3rem;">🎯</div>
        <strong style="display:block;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Main Concern</strong>
        <span style="font-weight:700;font-size:1rem;color:var(--gyc-dark);"><?= ucwords(htmlspecialchars($concern)) ?></span>
      </div>
      <div style="text-align:center;">
        <div style="font-size:1.8rem;margin-bottom:0.3rem;">💆</div>
        <strong style="display:block;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Lifestyle</strong>
        <span style="font-weight:700;font-size:1rem;color:var(--gyc-dark);"><?= ucwords(htmlspecialchars($lifestyle)) ?></span>
      </div>
      <div style="text-align:center;">
        <div style="font-size:1.8rem;margin-bottom:0.3rem;">🏆</div>
        <strong style="display:block;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;color:var(--gyc-green-500);">Goal</strong>
        <span style="font-weight:700;font-size:1rem;color:var(--gyc-dark);"><?= ucwords(htmlspecialchars($goal)) ?></span>
      </div>
    </div>

    <!-- Recommended products -->
    <?php if (!empty($products)): ?>
    <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:1.5rem;color:var(--gyc-dark);">
      Recommended Products for You
    </h2>
    <div class="products-grid" style="margin-bottom:2.5rem;">
      <?php foreach ($products as $prod): ?>
      <article class="product-card">
        <a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>" class="product-card-img-wrap">
          <img src="<?= htmlspecialchars($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy" class="product-card-img">
          <span class="product-badge product-badge--low" style="background:var(--gyc-green-700);color:#fff;">Recommended</span>
        </a>
        <div class="product-card-body">
          <h3 class="product-card-name"><a href="<?= SITE_URL ?>/product.php?slug=<?= urlencode($prod['slug']) ?>"><?= htmlspecialchars($prod['name']) ?></a></h3>
          <div class="product-card-footer">
            <span class="product-price"><?= formatPrice($prod['price']) ?></span>
            <button class="btn btn-gold btn-sm add-to-cart-btn" data-product-id="<?= $prod['id'] ?>">Add to Bag</button>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Bundle recommendation -->
    <?php if ($bundleRec): ?>
    <?php $bInfo = getBundlePrice($bundleRec['id']); ?>
    <div style="background:linear-gradient(135deg,var(--gyc-green-100),#fff);border:2px solid var(--gyc-green-300);border-radius:var(--gyc-radius-lg);padding:2rem;margin-bottom:3rem;display:flex;align-items:center;gap:2rem;flex-wrap:wrap;">
      <img src="<?= htmlspecialchars($bundleRec['image'] ?? '') ?>" alt="<?= htmlspecialchars($bundleRec['name']) ?>" loading="lazy" style="width:160px;height:120px;object-fit:cover;border-radius:var(--gyc-radius);">
      <div style="flex:1;min-width:200px;">
        <span style="font-size:0.72rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:var(--gyc-green-500);">Best Match Bundle</span>
        <h3 style="font-family:'Playfair Display',serif;font-size:1.3rem;margin:0.3rem 0 0.5rem;"><?= htmlspecialchars($bundleRec['name']) ?></h3>
        <p style="font-size:0.85rem;color:#555;margin-bottom:1rem;"><?= htmlspecialchars($bundleRec['description'] ?? '') ?></p>
        <?php if ($bInfo): ?>
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
          <span style="font-family:'Playfair Display',serif;font-size:1.3rem;color:var(--gyc-green-700);font-weight:700;"><?= formatPrice($bInfo['total']) ?></span>
          <span style="text-decoration:line-through;color:#bbb;font-size:0.88rem;"><?= formatPrice($bInfo['subtotal']) ?></span>
          <span style="background:var(--gyc-gold-500);color:var(--gyc-dark);font-size:0.72rem;font-weight:700;padding:0.2rem 0.6rem;border-radius:20px;">Save <?= round($bInfo['discount_pct']) ?>%</span>
        </div>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/bundle.php?slug=<?= urlencode($bundleRec['slug']) ?>" class="btn btn-gold">Shop This Bundle</a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Style recommendations -->
    <?php if (!empty($styleImages)): ?>
    <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;margin-bottom:1.5rem;color:var(--gyc-dark);">
      Styles Perfect for Your Hair
    </h2>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2.5rem;">
      <?php foreach ($styleImages as $si): ?>
      <article class="gallery-card">
        <a href="<?= SITE_URL ?>/style-detail.php?slug=<?= urlencode($si['slug']) ?>" class="gallery-card-img-wrap">
          <img src="<?= htmlspecialchars($si['image_url']) ?>" alt="<?= htmlspecialchars($si['title']) ?>" loading="lazy" class="gallery-card-img" style="height:240px;object-fit:cover;">
          <div class="gallery-card-overlay">
            <div class="gallery-card-info">
              <h3 class="gallery-card-title" style="font-size:0.9rem;"><?= htmlspecialchars($si['title']) ?></h3>
              <?php if ($si['price_from']): ?><span class="gallery-card-price">from <?= formatPrice($si['price_from']) ?></span><?php endif; ?>
              <a href="<?= SITE_URL ?>/book-appointment.php?style_id=<?= $si['id'] ?>" class="btn btn-gold btn-sm" onclick="event.stopPropagation();">Book</a>
            </div>
          </div>
        </a>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CTA actions -->
    <div style="text-align:center;padding:2rem;background:var(--gyc-dark);border-radius:var(--gyc-radius-lg);">
      <h3 style="font-family:'Playfair Display',serif;font-size:1.4rem;color:#fff;margin-bottom:0.75rem;">
        Ready to Transform Your Crown?
      </h3>
      <p style="color:rgba(255,255,255,0.7);margin-bottom:1.5rem;font-size:0.95rem;">Book your appointment or start shopping your personalised routine.</p>
      <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
        <a href="<?= SITE_URL ?>/book-appointment.php" class="btn btn-gold btn-lg">
          <i data-lucide="calendar-check" style="width:18px;height:18px;"></i>
          Book Appointment
        </a>
        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline-white btn-lg">Shop Products</a>
        <a href="<?= SITE_URL ?>/quiz.php" class="btn btn-outline-white btn-lg" style="border-color:rgba(255,255,255,0.3);">Retake Quiz</a>
      </div>
    </div>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.add-to-cart-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      addToCart(btn.dataset.productId, 1, btn);
    });
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
