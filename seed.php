<?php
/**
 * GYC Naturals — Database Seeder (MySQL + PostgreSQL compatible)
 * Populates the database with realistic demo data.
 * Run: http://localhost/gyc-store/seed.php  (local)
 *      https://gyc-naturals.vercel.app/seed.php  (production — then delete file!)
 * ⚠ DELETE this file after seeding in production.
 */
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$dbObj  = getDB();
$db     = $dbObj->getConnection();
$isPg   = ($dbObj->getDriver() === 'pgsql');
$out    = [];
$ok     = function($msg) use (&$out) { $out[] = ['✅', $msg]; };
$err    = function($msg) use (&$out) { $out[] = ['❌', $msg]; };

/**
 * Upsert/insert-ignore helper — works for both MySQL and PostgreSQL.
 * @param string $uniqueCol  The unique column name for ON CONFLICT (pgsql)
 */
function seed_insert(PDO $db, bool $isPg, string $table, array $data,
                     string $uniqueCol = 'slug'): int {
    $cols = array_keys($data);
    $phs  = array_map(fn($c) => ":$c", $cols);
    if ($isPg) {
        $colList = implode(',', $cols);
        $phList  = implode(',', $phs);
        $sql = "INSERT INTO $table ($colList) VALUES ($phList)
                ON CONFLICT ($uniqueCol) DO NOTHING RETURNING id";
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : 0;
    } else {
        $colList = implode(',', array_map(fn($c) => "`$c`", $cols));
        $phList  = implode(',', $phs);
        $sql = "INSERT IGNORE INTO `$table` ($colList) VALUES ($phList)";
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        return (int)$db->lastInsertId();
    }
}

try {
    if (!$isPg) {
        $db->exec("SET FOREIGN_KEY_CHECKS = 0");
        $db->exec("SET NAMES utf8mb4");
    }

    // ── 1. Product Categories ────────────────────────────
    $categories = [
        ['name'=>'Hair Care',        'slug'=>'hair-care',    'description'=>'Shampoos, conditioners, oils, and treatments for natural hair.', 'is_active'=>1, 'display_order'=>1],
        ['name'=>'Styling Products', 'slug'=>'styling',      'description'=>'Gels, creams, and sprays for protective styles.',                 'is_active'=>1, 'display_order'=>2],
        ['name'=>'Scalp Care',       'slug'=>'scalp-care',   'description'=>'Products targeting scalp health and growth.',                     'is_active'=>1, 'display_order'=>3],
        ['name'=>'Clothing',         'slug'=>'clothing',     'description'=>'GYC Naturals African-inspired fashion line.',                      'is_active'=>1, 'display_order'=>4],
        ['name'=>'Accessories',      'slug'=>'accessories',  'description'=>'Hair accessories, tools, and essentials.',                         'is_active'=>1, 'display_order'=>5],
        ['name'=>'Kits & Bundles',   'slug'=>'kits-bundles', 'description'=>'Curated product kits for complete hair routines.',                 'is_active'=>1, 'display_order'=>6],
    ];
    foreach ($categories as $c) {
        seed_insert($db, $isPg, 'categories',
            ['name'=>$c['name'],'slug'=>$c['slug'],'description'=>$c['description'],
             'is_active'=>$c['is_active'],'display_order'=>$c['display_order']]);
    }
    $ok("Categories seeded (" . count($categories) . ")");

    // Get category IDs
    $catIds = [];
    foreach ($db->query("SELECT id, slug FROM categories")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $catIds[$r['slug']] = (int)$r['id'];
    }

    // ── 2. Products ──────────────────────────────────────
    $products = [
        // Hair Care
        ['name'=>'GYC Crown Shea Butter Shampoo','slug'=>'gyc-crown-shea-butter-shampoo','sku'=>'GYC-SHP-001','price'=>4500,'compare_price'=>5500,'category_id'=>$catIds['hair-care']??1,'stock_quantity'=>85,'hair_type'=>'natural','concern'=>'dryness','product_type'=>'shampoo','key_ingredient'=>'Shea Butter','volume_ml'=>250,'is_featured'=>1,'description'=>'A deeply moisturising shampoo infused with pure shea butter and African black soap. Gently cleanses while preserving your hair\'s natural oils. Free from sulphates, parabens, and synthetic fragrances. Suitable for all natural hair types including 4A, 4B, and 4C.','image'=>'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=600&q=80'],
        ['name'=>'GYC Deep Repair Conditioner','slug'=>'gyc-deep-repair-conditioner','sku'=>'GYC-CON-001','price'=>5200,'compare_price'=>6000,'category_id'=>$catIds['hair-care']??1,'stock_quantity'=>72,'hair_type'=>'natural','concern'=>'breakage','product_type'=>'conditioner','key_ingredient'=>'Argan Oil','volume_ml'=>300,'is_featured'=>1,'description'=>'Intense repair conditioner with argan oil, raw honey, and baobab protein. Penetrates the cortex to repair damage from tension and heat. Leaves hair soft, detangled, and visibly healthier after first use.','image'=>'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&q=80'],
        ['name'=>'GYC Castor & Chebe Growth Oil','slug'=>'gyc-castor-chebe-growth-oil','sku'=>'GYC-OIL-001','price'=>6800,'compare_price'=>8000,'category_id'=>$catIds['hair-care']??1,'stock_quantity'=>104,'hair_type'=>'all','concern'=>'growth','product_type'=>'oil','key_ingredient'=>'Jamaican Black Castor Oil','volume_ml'=>120,'is_featured'=>1,'description'=>'Our bestselling growth oil combines Jamaican black castor oil with chebe powder, moringa, and rosemary. Massaging into the scalp stimulates blood flow, strengthens roots, and dramatically reduces shedding. Used by 500+ GYC clients.','image'=>'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=600&q=80'],
        ['name'=>'GYC Loc Butter Leave-In Cream','slug'=>'gyc-loc-butter-leave-in','sku'=>'GYC-LVN-001','price'=>4800,'compare_price'=>null,'category_id'=>$catIds['hair-care']??1,'stock_quantity'=>63,'hair_type'=>'locs','concern'=>'moisture','product_type'=>'leave-in','key_ingredient'=>'Mango Butter','volume_ml'=>200,'is_featured'=>0,'description'=>'Specifically formulated for locs and twists. Mango butter, aloe vera, and green tea extract provide lasting moisture without buildup. Lightweight formula dries quickly and won\'t attract lint.','image'=>'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&q=80'],
        // Styling
        ['name'=>'GYC Edge Control & Shine Gel','slug'=>'gyc-edge-control-shine-gel','sku'=>'GYC-GEL-001','price'=>2500,'compare_price'=>3000,'category_id'=>$catIds['styling']??2,'stock_quantity'=>150,'hair_type'=>'natural','concern'=>'definition','product_type'=>'gel','key_ingredient'=>'Aloe Vera','volume_ml'=>100,'is_featured'=>1,'description'=>'Firm hold edge control enriched with aloe vera, black castor oil, and vitamin E. Lays edges flat for up to 48 hours. No white residue, no flaking. Humidity-resistant for Lagos weather.','image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=600&q=80'],
        ['name'=>'GYC Braid & Twist Spray','slug'=>'gyc-braid-twist-spray','sku'=>'GYC-SPR-001','price'=>3200,'compare_price'=>null,'category_id'=>$catIds['styling']??2,'stock_quantity'=>89,'hair_type'=>'natural','concern'=>'frizz','product_type'=>'spray','key_ingredient'=>'Glycerin','volume_ml'=>200,'is_featured'=>0,'description'=>'A lightweight mist that keeps braids and twists looking fresh for weeks. Glycerin attracts moisture, while tea tree oil maintains scalp health. Perfect between salon visits.','image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=600&q=80'],
        // Scalp Care
        ['name'=>'GYC Scalp Revival Treatment','slug'=>'gyc-scalp-revival-treatment','sku'=>'GYC-SCP-001','price'=>7500,'compare_price'=>9000,'category_id'=>$catIds['scalp-care']??3,'stock_quantity'=>47,'hair_type'=>'all','concern'=>'scalp health','product_type'=>'treatment','key_ingredient'=>'Peppermint Oil','volume_ml'=>150,'is_featured'=>1,'description'=>'Intensive scalp treatment with peppermint, tea tree, neem oil, and vitamin B5. Targets dandruff, itching, and thinning. Formulated by our in-house trichologist for Nigerian climate conditions.','image'=>'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=600&q=80'],
        ['name'=>'GYC Rosemary Scalp Serum','slug'=>'gyc-rosemary-scalp-serum','sku'=>'GYC-SRM-001','price'=>8900,'compare_price'=>10500,'category_id'=>$catIds['scalp-care']??3,'stock_quantity'=>38,'hair_type'=>'all','concern'=>'growth','product_type'=>'serum','key_ingredient'=>'Rosemary Extract','volume_ml'=>50,'is_featured'=>1,'description'=>'Clinically studied rosemary extract serum for hair regrowth and scalp stimulation. Shown to be as effective as minoxidil in independent studies, without side effects. Apply 10 drops to scalp nightly.','image'=>'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=600&q=80'],
        // Clothing
        ['name'=>'GYC Ankara Wrap Dress','slug'=>'gyc-ankara-wrap-dress','sku'=>'GYC-CLT-001','price'=>28000,'compare_price'=>35000,'category_id'=>$catIds['clothing']??4,'stock_quantity'=>25,'hair_type'=>null,'concern'=>null,'product_type'=>null,'key_ingredient'=>null,'volume_ml'=>null,'is_featured'=>1,'description'=>'Bold Ankara print wrap dress. 100% cotton. Made in Lagos. Adjustable fit. Available in sizes S–XL. Each piece is unique — fabric patterns may vary slightly.','image'=>'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=600&q=80'],
        ['name'=>'GYC Kente Print Headwrap','slug'=>'gyc-kente-print-headwrap','sku'=>'GYC-CLT-002','price'=>4500,'compare_price'=>6000,'category_id'=>$catIds['clothing']??4,'stock_quantity'=>60,'hair_type'=>null,'concern'=>null,'product_type'=>null,'key_ingredient'=>null,'volume_ml'=>null,'is_featured'=>0,'description'=>'Premium kente-inspired fabric headwrap. 2m x 0.5m. Versatile styling — wrap, twist, or tie. Pre-washed, soft-to-touch cotton blend. Protects natural hair while making a statement.','image'=>'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=600&q=80'],
        // Accessories
        ['name'=>'GYC Satin Bonnet (Adjustable)','slug'=>'gyc-satin-bonnet','sku'=>'GYC-ACC-001','price'=>3500,'compare_price'=>null,'category_id'=>$catIds['accessories']??5,'stock_quantity'=>120,'hair_type'=>'all','concern'=>'protection','product_type'=>null,'key_ingredient'=>null,'volume_ml'=>null,'is_featured'=>0,'description'=>'Premium satin bonnet with adjustable elastic band. Fits all hair sizes including loc-wearers. Reduces overnight friction to minimise breakage and preserve styles. Available in 6 colours.','image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=600&q=80'],
        ['name'=>'GYC Wide-Tooth Seamless Comb','slug'=>'gyc-wide-tooth-comb','sku'=>'GYC-ACC-002','price'=>1800,'compare_price'=>null,'category_id'=>$catIds['accessories']??5,'stock_quantity'=>200,'hair_type'=>'natural','concern'=>'detangling','product_type'=>null,'key_ingredient'=>null,'volume_ml'=>null,'is_featured'=>0,'description'=>'Seamless wide-tooth comb designed specifically for natural hair textures 3C–4C. No seams means no snags or breakage. Detangle from ends to roots with ease.','image'=>'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&q=80'],
    ];

    foreach ($products as $p) {
        seed_insert($db, $isPg, 'products', [
            'name'          => $p['name'],
            'slug'          => $p['slug'],
            'sku'           => $p['sku'],
            'description'   => $p['description'],
            'price'         => $p['price'],
            'compare_price' => $p['compare_price'],
            'category_id'   => $p['category_id'],
            'stock_quantity'=> $p['stock_quantity'],
            'hair_type'     => $p['hair_type'],
            'concern'       => $p['concern'],
            'product_type'  => $p['product_type'],
            'key_ingredient'=> $p['key_ingredient'],
            'volume_ml'     => $p['volume_ml'],
            'is_active'     => 1,
            'is_featured'   => $p['is_featured'],
            'image'         => $p['image'] ?? '',
            'display_order' => 99,
        ]);
    }
    $ok("Products seeded (" . count($products) . ")");

    // ── 3. Gallery Categories ────────────────────────────
    $galCats = [
        ['name'=>'Knotless Box Braids','slug'=>'knotless-box-braids','is_active'=>1,'display_order'=>1],
        ['name'=>'Faux Locs',          'slug'=>'faux-locs',          'is_active'=>1,'display_order'=>2],
        ['name'=>'Cornrows',           'slug'=>'cornrows',           'is_active'=>1,'display_order'=>3],
        ['name'=>'Senegalese Twists',  'slug'=>'senegalese-twists',  'is_active'=>1,'display_order'=>4],
        ['name'=>'Natural Styles',     'slug'=>'natural-styles',     'is_active'=>1,'display_order'=>5],
        ['name'=>'Starter Locs',       'slug'=>'starter-locs',       'is_active'=>1,'display_order'=>6],
    ];
    foreach ($galCats as $gc) {
        seed_insert($db, $isPg, 'gallery_categories',
            ['name'=>$gc['name'],'slug'=>$gc['slug'],'is_active'=>$gc['is_active'],'display_order'=>$gc['display_order']]);
    }
    $ok("Gallery categories seeded (" . count($galCats) . ")");

    $gcIds = [];
    foreach ($db->query("SELECT id, slug FROM gallery_categories")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $gcIds[$r['slug']] = (int)$r['id'];
    }

    // ── 4. Gallery Images ─────────────────────────────────
    // style_type enum: box_braids, cornrows, knotless, twists, locs, weave, natural, updo, other
    $styles = [
        ['title'=>'Waist-Length Knotless Braids',      'slug'=>'waist-length-knotless-braids',     'cat'=>'knotless-box-braids','style_type'=>'knotless','hours'=>8,'price'=>55000,'featured'=>1,'image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=800&q=80','description'=>'Extra-long knotless box braids reaching the waist. Lightweight thanks to the tension-free knotless technique. Low manipulation, scalp-friendly.'],
        ['title'=>'Medium Knotless Braids with Curls',  'slug'=>'medium-knotless-braids-curls',     'cat'=>'knotless-box-braids','style_type'=>'knotless','hours'=>6,'price'=>42000,'featured'=>1,'image'=>'https://images.unsplash.com/photo-1519457431-44ccd64a579b?w=800&q=80','description'=>'Mid-length knotless braids with boho curly ends. Romantic and professional.'],
        ['title'=>'Jumbo Faux Locs',                    'slug'=>'jumbo-faux-locs',                  'cat'=>'faux-locs',          'style_type'=>'locs',    'hours'=>9,'price'=>65000,'featured'=>1,'image'=>'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=800&q=80','description'=>'Bold jumbo faux locs for maximum impact. Distressed ends add authenticity.'],
        ['title'=>'Butterfly Locs',                     'slug'=>'butterfly-locs',                   'cat'=>'faux-locs',          'style_type'=>'locs',    'hours'=>8,'price'=>58000,'featured'=>0,'image'=>'https://images.unsplash.com/photo-1536697246787-1f7ae568d89a?w=800&q=80','description'=>'Trendy wavy butterfly locs. Soft and feminine.'],
        ['title'=>'Cornrow Updo',                       'slug'=>'cornrow-updo',                     'cat'=>'cornrows',           'style_type'=>'cornrows','hours'=>3,'price'=>18000,'featured'=>1,'image'=>'https://images.unsplash.com/photo-1555116505-38ab61800975?w=800&q=80','description'=>'Elegant cornrow updo with intricate patterning.'],
        ['title'=>'Fulani Braids',                      'slug'=>'fulani-braids',                    'cat'=>'cornrows',           'style_type'=>'cornrows','hours'=>4,'price'=>22000,'featured'=>1,'image'=>'https://images.unsplash.com/photo-1542704792-e50d7e45b7cd?w=800&q=80','description'=>'Traditional Fulani-inspired braiding pattern with a centre cornrow and side braids.'],
        ['title'=>'Senegalese Rope Twists',             'slug'=>'senegalese-rope-twists',           'cat'=>'senegalese-twists',  'style_type'=>'twists',  'hours'=>7,'price'=>38000,'featured'=>0,'image'=>'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=800&q=80','description'=>'Silky Senegalese rope twists for a sleek, defined look.'],
        ['title'=>'Goddess Locs',                       'slug'=>'goddess-locs',                     'cat'=>'natural-styles',     'style_type'=>'locs',    'hours'=>7,'price'=>50000,'featured'=>1,'image'=>'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=800&q=80','description'=>'Bohemian goddess locs with soft wavy ends.'],
        ['title'=>'Starter Locs (Comb Coils)',          'slug'=>'starter-locs-comb-coils',          'cat'=>'starter-locs',       'style_type'=>'locs',    'hours'=>5,'price'=>30000,'featured'=>0,'image'=>'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=800&q=80','description'=>'Begin your loc journey with our expert starter locs service.'],
    ];
    foreach ($styles as $s) {
        seed_insert($db, $isPg, 'gallery_images', [
            'title'         => $s['title'],
            'slug'          => $s['slug'],
            'category_id'   => $gcIds[$s['cat']] ?? 1,
            'style_type'    => $s['style_type'],
            'duration_hours'=> $s['hours'],
            'price_from'    => $s['price'],
            'image_url'     => $s['image'],
            'is_featured'   => $s['featured'],
            'is_active'     => 1,
            'description'   => $s['description'],
        ]);
    }
    $ok("Gallery images seeded (" . count($styles) . ")");

    // ── 5. Blog Posts ─────────────────────────────────────
    $posts = [
        ['title'=>'5 Reasons Knotless Braids Are the Best Protective Style for Nigerian Women','slug'=>'5-reasons-knotless-braids-best-protective-style-nigerian-women','excerpt'=>'Knotless braids have taken over Lagos salons — and for good reason. Discover why this style is gentler, longer-lasting, and more versatile than traditional box braids.','body'=>'<h2>What Are Knotless Braids?</h2><p>Unlike traditional box braids that start with a tight knot at the root, knotless braids begin with your natural hair and gradually add extensions. The result? Zero tension on your edges, less breakage, and a more natural-looking root.</p><h2>1. No Tension = Healthier Edges</h2><p>Edge loss is a real concern for women who regularly braid.</p><h2>2. Lightweight on the Scalp</h2><p>Knotless braids weigh significantly less than traditional braids.</p><h2>3. Longer Lifespan</h2><p>With proper care, knotless braids can last up to 10 weeks.</p><h2>4. More Styling Versatility</h2><p>The flat, seamless base allows high buns, ponytails, and more.</p><h2>5. Natural Scalp Access</h2><p>The grid pattern allows easy access to your scalp for washing and oiling.</p>','category'=>'protective-styles','tags'=>'knotless braids, protective styles, Lagos, natural hair','author'=>'Grace Yakubu, GYC Naturals','read_time'=>5,'is_featured'=>1,'status'=>'published','image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=1200&q=80'],
        ['title'=>'The GYC Guide to a Healthy Hair Regimen for 4C Hair','slug'=>'gyc-guide-healthy-hair-regimen-4c-hair','excerpt'=>'Building a consistent routine is the single most important thing you can do for 4C hair. Here is our complete step-by-step guide, tested by hundreds of GYC clients.','body'=>'<h2>Understanding 4C Hair</h2><p>4C hair is the tightest curl pattern on the natural hair spectrum. It has the least defined curl pattern, shrinks up to 75% of its actual length, and is prone to dryness.</p><h2>The GYC 4C Weekly Regimen</h2><h3>Step 1: Pre-Poo</h3><p>Apply our Castor & Chebe Growth Oil before washing.</p><h3>Step 2: Cleanse</h3><p>Use our Crown Shea Butter Shampoo. Apply in sections.</p><h3>Step 3: Deep Condition</h3><p>Apply our Deep Repair Conditioner generously from root to tip.</p><h3>Step 4: Leave-In & Seal</h3><p>Apply our Loc Butter Leave-In Cream on damp hair, then seal with oil.</p><h3>Step 5: Style & Protect</h3><p>Style into a protective style of your choice. Always sleep in a satin bonnet.</p>','category'=>'hair-care','tags'=>'4c hair, hair regimen, natural hair care, moisturising','author'=>'Adaeze Nwachukwu, Product Dev','read_time'=>7,'is_featured'=>1,'status'=>'published','image'=>'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=1200&q=80'],
        ['title'=>'How to Maintain Your Braids for 8+ Weeks in Lagos Heat','slug'=>'maintain-braids-8-weeks-lagos-heat','excerpt'=>'Lagos humidity and heat can shorten the lifespan of your braids. Our maintenance guide will help you keep them looking fresh for twice as long.','body'=>'<h2>Why Braids Deteriorate Faster in Lagos</h2><p>Lagos weather — hot, humid, and sometimes dusty — is uniquely challenging for braided styles.</p><h2>Week 1–2: The Fresh Period</h2><p>Focus on protecting edges and wearing a satin bonnet nightly.</p><h2>Week 3–4: The Critical Window</h2><p>Spray lightly with our Braid & Twist Spray and smooth with a soft toothbrush.</p><h2>Week 5–8: The Long Game</h2><p>Use edge control to refresh part lines and mousse to re-define frizzy braids.</p>','category'=>'hair-care','tags'=>'braid maintenance, braids Lagos, long-lasting braids','author'=>'Grace Yakubu, GYC Naturals','read_time'=>6,'is_featured'=>0,'status'=>'published','image'=>'https://images.unsplash.com/photo-1519457431-44ccd64a579b?w=1200&q=80'],
        ['title'=>'Starting Locs in Lagos: Everything You Need to Know','slug'=>'starting-locs-lagos-everything-you-need-to-know','excerpt'=>'Thinking about starting your loc journey? Our comprehensive guide covers methods, timelines, costs, and what to expect in the first year.','body'=>'<h2>Is Loc\'ing Right for You?</h2><p>Starting locs is a long-term commitment — a 2–3 year journey to mature locs.</p><h2>The Three Main Methods</h2><h3>Comb Coils</h3><p>The most popular method at GYC. Perfect for 4A–4C hair.</p><h3>Two-Strand Twists</h3><p>Slightly less defined initially but great for all textures.</p><h3>Interlocking</h3><p>Uses a hook to thread roots — ideal for fine hair or active lifestyles.</p><h2>The Loc Journey Stages</h2><ol><li><strong>Starter (0–6 months):</strong> Coils are still loose.</li><li><strong>Budding (6–12 months):</strong> Small buds appear.</li><li><strong>Teen Stage (12–18 months):</strong> Inconsistent width — the awkward stage!</li><li><strong>Mature Locs (18–36 months):</strong> Fully formed with sealed ends.</li></ol>','category'=>'protective-styles','tags'=>'locs, starter locs, dreadlocks, loc journey, Lagos','author'=>'Chinwe Okafor, Loc Specialist','read_time'=>8,'is_featured'=>0,'status'=>'published','image'=>'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=1200&q=80'],
    ];
    foreach ($posts as $p) {
        seed_insert($db, $isPg, 'blog_posts', [
            'title'         => $p['title'],
            'slug'          => $p['slug'],
            'excerpt'       => $p['excerpt'],
            'body'          => $p['body'],
            'featured_image'=> $p['image'],
            'category'      => $p['category'],
            'tags'          => $p['tags'],
            'author'        => $p['author'],
            'read_time'     => $p['read_time'],
            'is_featured'   => $p['is_featured'],
            'status'        => $p['status'],
            'view_count'    => rand(45, 280),
            'published_at'  => date('Y-m-d H:i:s'),
        ]);
    }
    $ok("Blog posts seeded (" . count($posts) . ")");

    // ── 6. Testimonials ───────────────────────────────────
    // Check how many testimonials already exist
    $existingT = (int)$db->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
    if ($existingT === 0) {
        $testimonials = [
            ['author_name'=>'Chidinma Obi',       'author_location'=>'Victoria Island, Lagos','service'=>'Knotless Box Braids','rating'=>5,'is_featured'=>1,'content'=>'Grace and her team are absolute magicians! I came in for knotless braids and left with the most beautiful, tension-free style I\'ve ever had. My edges actually grew back stronger after wearing them for 8 weeks.'],
            ['author_name'=>'Adaora Eze',          'author_location'=>'Lekki, Lagos',          'service'=>'Faux Locs',         'rating'=>5,'is_featured'=>1,'content'=>'I\'ve been trying to find a salon in Lagos that truly understands natural hair for years. GYC Naturals is IT. The faux locs lasted 10 full weeks and my hair underneath was thriving.'],
            ['author_name'=>'Funmi Adeleke',       'author_location'=>'Ikeja, Lagos',          'service'=>'GYC Growth Oil',    'rating'=>5,'is_featured'=>1,'content'=>'The Castor & Chebe Growth Oil is liquid gold. I\'ve been using it for 3 months and my hair has grown almost 2 inches. I also noticed significantly less shedding.'],
            ['author_name'=>'Ngozi Nwosu',         'author_location'=>'Ajah, Lagos',           'service'=>'Cornrow Updo',      'rating'=>5,'is_featured'=>0,'content'=>'I needed a neat cornrow style for my sister\'s wedding. Chinwe did an absolutely perfect job — intricate pattern, clean edges, and it stayed flawless for 3 weeks.'],
            ['author_name'=>'Blessing Okorie',     'author_location'=>'Surulere, Lagos',       'service'=>'Starter Locs',      'rating'=>5,'is_featured'=>1,'content'=>'Starting my loc journey at GYC Naturals was the best decision. They consulted with me thoroughly and made the process so exciting. 6 months in and my locs are budding beautifully.'],
            ['author_name'=>'Amaka Chukwu',        'author_location'=>'VI, Lagos',             'service'=>'Scalp Treatment',   'rating'=>5,'is_featured'=>0,'content'=>'I came in with terrible dandruff and scalp inflammation. After just one scalp treatment session and using the Scalp Revival product at home, my scalp is completely clear.'],
            ['author_name'=>'Temi Ologun',         'author_location'=>'Ikoyi, Lagos',          'service'=>'Senegalese Twists', 'rating'=>4,'is_featured'=>0,'content'=>'Beautiful twists that lasted 7 weeks. Would have been 5 stars but the wait was a bit long. However, the result was absolutely worth it.'],
        ];
        $tableName = $isPg ? 'testimonials' : 'testimonials';
        foreach ($testimonials as $t) {
            if ($isPg) {
                $stmt = $db->prepare(
                    "INSERT INTO testimonials (author_name,author_location,service,content,rating,is_approved,is_featured)
                     VALUES (:author_name,:author_location,:service,:content,:rating,:is_approved,:is_featured)
                     ON CONFLICT DO NOTHING"
                );
            } else {
                $stmt = $db->prepare(
                    "INSERT IGNORE INTO `testimonials` (author_name,author_location,service,content,rating,is_approved,is_featured)
                     VALUES (:author_name,:author_location,:service,:content,:rating,:is_approved,:is_featured)"
                );
            }
            $stmt->execute([
                'author_name'     => $t['author_name'],
                'author_location' => $t['author_location'],
                'service'         => $t['service'],
                'content'         => $t['content'],
                'rating'          => $t['rating'],
                'is_approved'     => 1,
                'is_featured'     => $t['is_featured'],
            ]);
        }
        $ok("Testimonials seeded (" . count($testimonials) . ")");
    } else {
        $ok("Testimonials already present ($existingT rows) — skipped");
    }

    // ── 7. Bundles ────────────────────────────────────────
    $bundleData = [
        'Starter Natural Hair Kit' => ['slug'=>'starter-natural-hair-kit','description'=>'Everything you need to start or refresh your natural hair routine. Curated by our trichologist.','discount'=>15,'featured'=>1,'products'=>['gyc-crown-shea-butter-shampoo','gyc-deep-repair-conditioner','gyc-castor-chebe-growth-oil']],
        'Loc Lovers Bundle'        => ['slug'=>'loc-lovers-bundle','description'=>'Products specifically chosen for loc wearers to keep locs moisturised and scalp healthy.','discount'=>12,'featured'=>1,'products'=>['gyc-loc-butter-leave-in','gyc-scalp-revival-treatment','gyc-braid-twist-spray']],
        'Scalp Rescue Kit'         => ['slug'=>'scalp-rescue-kit','description'=>'Combat dandruff, itching, and hair thinning with our specialist scalp bundle.','discount'=>10,'featured'=>0,'products'=>['gyc-scalp-revival-treatment','gyc-rosemary-scalp-serum','gyc-castor-chebe-growth-oil']],
    ];
    foreach ($bundleData as $name => $b) {
        $bundleId = seed_insert($db, $isPg, 'bundles', [
            'name'                => $name,
            'slug'                => $b['slug'],
            'description'         => $b['description'],
            'discount_percentage' => $b['discount'],
            'is_active'           => 1,
            'is_featured'         => $b['featured'],
            'display_order'       => 99,
        ]);
        if ($bundleId > 0) {
            foreach ($b['products'] as $pslug) {
                $pstmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
                $pstmt->execute([$pslug]);
                $prod = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($prod) {
                    if ($isPg) {
                        $db->prepare(
                            "INSERT INTO bundle_items (bundle_id,product_id,quantity) VALUES (?,?,1) ON CONFLICT DO NOTHING"
                        )->execute([$bundleId, $prod['id']]);
                    } else {
                        $db->prepare(
                            "INSERT IGNORE INTO `bundle_items` (bundle_id,product_id,quantity) VALUES (?,?,1)"
                        )->execute([$bundleId, $prod['id']]);
                    }
                }
            }
        }
    }
    $ok("Bundles seeded (" . count($bundleData) . ")");

    // ── 8. Site Settings ──────────────────────────────────
    $settings = [
        'site_name'              => 'GYC Naturals',
        'site_tagline'           => 'Grow Your Crown',
        'site_email'             => 'info@gycnaturals.com',
        'contact_email'          => 'hello@gycnaturals.com',
        'site_phone'             => '+234 800 492 4247',
        'site_whatsapp'          => '2348004924247',
        'site_address'           => '14 Akin Adesola Street, Victoria Island, Lagos, Nigeria',
        'opening_hours'          => 'Mon–Sat: 9:00 AM – 7:00 PM',
        'business_hours'         => 'Mon–Sat: 9am – 7pm',
        'business_address'       => 'Victoria Island, Lagos, Nigeria',
        'instagram_handle'       => 'gycnaturals',
        'instagram_url'          => 'https://instagram.com/gycnaturals',
        'social_instagram'       => 'https://instagram.com/gycnaturals',
        'social_facebook'        => 'https://facebook.com/gycnaturals',
        'social_tiktok'          => 'https://tiktok.com/@gycnaturals',
        'social_twitter'         => 'https://twitter.com/gycnaturals',
        'paystack_public_key'    => 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'paystack_secret_key'    => 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'free_shipping_threshold'=> '50000',
        'default_shipping_fee'   => '2500',
        'resend_api_key'         => '',
        'from_name'              => 'GYC Naturals',
        'from_email'             => 'info@gycnaturals.com',
    ];
    foreach ($settings as $k => $v) {
        if ($isPg) {
            $db->prepare(
                "INSERT INTO site_settings (setting_key,setting_val) VALUES (:k,:v)
                 ON CONFLICT (setting_key) DO UPDATE SET setting_val = EXCLUDED.setting_val"
            )->execute(['k'=>$k, 'v'=>$v]);
        } else {
            $db->prepare(
                "INSERT INTO `site_settings` (setting_key,setting_val) VALUES (?,?)
                 ON DUPLICATE KEY UPDATE setting_val=?"
            )->execute([$k, $v, $v]);
        }
    }
    $ok("Site settings seeded (" . count($settings) . ")");

    if (!$isPg) {
        $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    }

} catch (PDOException $e) {
    $err("Database error: " . $e->getMessage());
}

// ── Output ───────────────────────────────────────────────
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GYC Naturals — Database Seeder</title>
<style>
  body { font-family: system-ui, sans-serif; background: #F0FFF4; padding: 2rem; max-width: 700px; margin: 0 auto; }
  h1   { font-size: 1.4rem; font-weight: 700; margin-bottom: 1.5rem; color: #166534; }
  .item { display: flex; gap: .75rem; padding: .5rem 0; border-bottom: 1px solid #D1FAE5; font-size: .9rem; align-items: flex-start; }
  .icon { font-size: 1.1rem; flex-shrink: 0; margin-top: .05rem; }
  .msg  { line-height: 1.5; }
  .meta { font-size: .75rem; color: #6B7280; margin-top: .25rem; }
  .warn { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 1rem; margin-top: 1.5rem; font-size: .85rem; color: #92400E; }
  .links { margin-top: 1.5rem; display: flex; gap: .75rem; flex-wrap: wrap; }
  .links a { padding: .5rem 1rem; background: #166534; color: #fff; border-radius: 6px; text-decoration: none; font-size: .85rem; }
  .links a:hover { background: #14532D; }
</style>
</head>
<body>
<h1>🌿 GYC Naturals — Database Seeder</h1>
<p class="meta">Driver: <strong><?= htmlspecialchars($dbObj->getDriver()) ?></strong> | Time: <?= date('H:i:s') ?></p>
<?php foreach ($out as [$icon, $msg]): ?>
<div class="item"><span class="icon"><?= $icon ?></span><span class="msg"><?= htmlspecialchars($msg) ?></span></div>
<?php endforeach; ?>
<div class="warn">
  <strong>⚠ Security notice:</strong> Delete <code>seed.php</code> from your server after seeding. It inserts demo data and must not remain accessible in production.
</div>
<div class="links">
  <a href="/">View Homepage</a>
  <a href="/shop.php">Shop</a>
  <a href="/gallery.php">Gallery</a>
  <a href="/admin/">Admin Panel</a>
  <a href="/blog.php">Blog</a>
</div>
</body>
</html>
