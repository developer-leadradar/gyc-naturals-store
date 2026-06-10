<?php
/**
 * One-time content update script.
 * Updates site_settings and gallery/product/blog images to real Pexels photos.
 * DELETE THIS FILE after running.
 * Access: /update-content.php?key=GYCupdate2024
 */
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$secret = 'GYCupdate2024';
if (($_GET['key'] ?? '') !== $secret) {
    http_response_code(403);
    die('Forbidden');
}

$db = getDB();
$log = [];

// ── Helper ─────────────────────────────────────────────────────────────
function upsertSetting($db, $key, $value) {
    $existing = $db->fetchOne("SELECT id FROM site_settings WHERE setting_key = ?", [$key]);
    if ($existing) {
        $db->query("UPDATE site_settings SET setting_val = ? WHERE setting_key = ?", [$value, $key]);
    } else {
        $db->insert('site_settings', ['setting_key' => $key, 'setting_val' => $value]);
    }
}

// ── 1. Site settings ───────────────────────────────────────────────────
$calabarAddress = 'Big Qua Mall, Ediba Road, Off Big Qua Town by Marian Market, Calabar, Cross River State';

upsertSetting($db, 'site_address',      $calabarAddress);
upsertSetting($db, 'business_address',  $calabarAddress);
upsertSetting($db, 'site_phone',        '+2347037256585');
upsertSetting($db, 'site_whatsapp',     '2347037256585');
upsertSetting($db, 'business_name',     'GYC Naturals');
upsertSetting($db, 'about_owner_name',  'Juliet Arah');
upsertSetting($db, 'about_owner_bio',   'Welcome to GYC Naturals — Calabar\'s premier destination for African hair braiding and natural hair care. Founded by Juliet Arah in 2024, we celebrate the beauty of African hair with every service we offer. Visit us at Big Qua Mall, Ediba Road, Calabar.');
$log[] = '✅ Site settings updated (address, phone, WhatsApp, owner bio)';

// ── 2. Gallery images — replace with Pexels African hair photos ────────
$pexelsImages = [
    ['title' => 'Knotless Box Braids',     'url' => 'https://images.pexels.com/photos/14883868/pexels-photo-14883868.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ['title' => 'Tribal Braids with Beads','url' => 'https://images.pexels.com/photos/31473242/pexels-photo-31473242.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ['title' => 'Cornrow Artistry',        'url' => 'https://images.pexels.com/photos/33664383/pexels-photo-33664383.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ['title' => 'Box Braids — Jumbo',      'url' => 'https://images.pexels.com/photos/17463802/pexels-photo-17463802.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ['title' => 'Senegalese Twists',       'url' => 'https://images.pexels.com/photos/5722771/pexels-photo-5722771.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ['title' => 'Braids with Wooden Beads','url' => 'https://images.pexels.com/photos/11268995/pexels-photo-11268995.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ['title' => 'Feed-In Cornrows',        'url' => 'https://images.pexels.com/photos/37115258/pexels-photo-37115258.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ['title' => 'Colorful Goddess Braids', 'url' => 'https://images.pexels.com/photos/5706984/pexels-photo-5706984.jpeg?auto=compress&cs=tinysrgb&w=800'],
    ['title' => 'Elegant Braided Updo',    'url' => 'https://images.pexels.com/photos/31065905/pexels-photo-31065905.jpeg?auto=compress&cs=tinysrgb&w=800'],
];

$galleryImages = $db->fetchAll("SELECT id FROM gallery_images ORDER BY id");
foreach ($galleryImages as $i => $gi) {
    if (isset($pexelsImages[$i])) {
        $db->query(
            "UPDATE gallery_images SET image_url = ?, title = ? WHERE id = ?",
            [$pexelsImages[$i]['url'], $pexelsImages[$i]['title'], $gi['id']]
        );
    }
}
$log[] = '✅ Gallery images updated (' . count($galleryImages) . ' images)';

// ── 3. Blog featured images ────────────────────────────────────────────
$blogImages = [
    'https://images.pexels.com/photos/28383173/pexels-photo-28383173.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/11641088/pexels-photo-11641088.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/6960735/pexels-photo-6960735.jpeg?auto=compress&cs=tinysrgb&w=800',
    'https://images.pexels.com/photos/34191088/pexels-photo-34191088.jpeg?auto=compress&cs=tinysrgb&w=800',
];
$blogPosts = $db->fetchAll("SELECT id FROM blog_posts ORDER BY id");
foreach ($blogPosts as $i => $bp) {
    $img = $blogImages[$i % count($blogImages)];
    $db->query("UPDATE blog_posts SET featured_image = ? WHERE id = ?", [$img, $bp['id']]);
}
$log[] = '✅ Blog post images updated (' . count($blogPosts) . ' posts)';

// ── 4. Product images ──────────────────────────────────────────────────
$productImages = [
    'https://images.pexels.com/photos/4046316/pexels-photo-4046316.jpeg?auto=compress&cs=tinysrgb&w=600',
    'https://images.pexels.com/photos/4465124/pexels-photo-4465124.jpeg?auto=compress&cs=tinysrgb&w=600',
    'https://images.pexels.com/photos/3735149/pexels-photo-3735149.jpeg?auto=compress&cs=tinysrgb&w=600',
    'https://images.pexels.com/photos/4041392/pexels-photo-4041392.jpeg?auto=compress&cs=tinysrgb&w=600',
    'https://images.pexels.com/photos/5069432/pexels-photo-5069432.jpeg?auto=compress&cs=tinysrgb&w=600',
    'https://images.pexels.com/photos/3735185/pexels-photo-3735185.jpeg?auto=compress&cs=tinysrgb&w=600',
];
$products = $db->fetchAll("SELECT id FROM products ORDER BY id");
foreach ($products as $i => $p) {
    $img = $productImages[$i % count($productImages)];
    $db->query("UPDATE products SET image = ? WHERE id = ?", [$img, $p['id']]);
}
$log[] = '✅ Product images updated (' . count($products) . ' products)';

// ── 5. Bundles ─────────────────────────────────────────────────────────
$bundleImages = [
    'https://images.pexels.com/photos/33664380/pexels-photo-33664380.jpeg?auto=compress&cs=tinysrgb&w=600',
    'https://images.pexels.com/photos/29731065/pexels-photo-29731065.jpeg?auto=compress&cs=tinysrgb&w=600',
    'https://images.pexels.com/photos/14931950/pexels-photo-14931950.jpeg?auto=compress&cs=tinysrgb&w=600',
];
$bundles = $db->fetchAll("SELECT id FROM bundles ORDER BY id");
foreach ($bundles as $i => $b) {
    $img = $bundleImages[$i % count($bundleImages)];
    $db->query("UPDATE bundles SET image = ? WHERE id = ?", [$img, $b['id']]);
}
$log[] = '✅ Bundle images updated (' . count($bundles) . ' bundles)';

// ── 6. Testimonial photos ──────────────────────────────────────────────
$testimonialPhotos = [
    'https://images.pexels.com/photos/4708719/pexels-photo-4708719.jpeg?auto=compress&cs=tinysrgb&w=200',
    'https://images.pexels.com/photos/33664380/pexels-photo-33664380.jpeg?auto=compress&cs=tinysrgb&w=200',
    'https://images.pexels.com/photos/25752048/pexels-photo-25752048.jpeg?auto=compress&cs=tinysrgb&w=200',
    'https://images.pexels.com/photos/7078204/pexels-photo-7078204.jpeg?auto=compress&cs=tinysrgb&w=200',
    'https://images.pexels.com/photos/6960735/pexels-photo-6960735.jpeg?auto=compress&cs=tinysrgb&w=200',
    'https://images.pexels.com/photos/8266868/pexels-photo-8266868.jpeg?auto=compress&cs=tinysrgb&w=200',
    'https://images.pexels.com/photos/10825829/pexels-photo-10825829.jpeg?auto=compress&cs=tinysrgb&w=200',
];
$testimonials = $db->fetchAll("SELECT id FROM testimonials ORDER BY id");
foreach ($testimonials as $i => $t) {
    $photo = $testimonialPhotos[$i % count($testimonialPhotos)];
    $db->query("UPDATE testimonials SET photo_url = ? WHERE id = ?", [$photo, $t['id']]);
}
$log[] = '✅ Testimonial photos updated (' . count($testimonials) . ' testimonials)';

?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Content Update — GYC Naturals</title>
<style>body{font-family:monospace;background:#0f172a;color:#4ade80;padding:2rem;} pre{line-height:2;} .err{color:#f87171;}</style>
</head>
<body>
<h2 style="color:#C9A84C;">GYC Naturals — Content Update Complete</h2>
<pre>
<?php foreach ($log as $line) echo htmlspecialchars($line) . "\n"; ?>

⚠️  DELETE THIS FILE NOW: /update-content.php
</pre>
<p style="color:#94a3b8;margin-top:2rem;">Visit <a href="/" style="color:#4ade80;">the homepage</a> to verify changes.</p>
</body>
</html>
