<?php
/**
 * GYC Naturals — Database Seeder
 * Populates the database with realistic demo data.
 * Run: http://localhost/gyc-store/seed.php
 * ⚠ DELETE this file after seeding in production.
 */
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Safety: only run from CLI or with a secret token in production
// if (!isset($_GET['token']) || $_GET['token'] !== 'gyc-seed-2025') die('Forbidden');

$db  = getDB()->getConnection();
$out = [];
$ok  = function($msg) use (&$out) { $out[] = ['✅', $msg]; };
$err = function($msg) use (&$out) { $out[] = ['❌', $msg]; };

function tryInsert(PDO $db, string $table, array $data): int {
    $cols = implode(',', array_map(fn($k) => "`$k`", array_keys($data)));
    $phs  = implode(',', array_fill(0, count($data), '?'));
    $stmt = $db->prepare("INSERT IGNORE INTO `$table` ($cols) VALUES ($phs)");
    $stmt->execute(array_values($data));
    return (int)$db->lastInsertId();
}

try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("SET NAMES utf8mb4");

    // ── 1. Product Categories ────────────────────────────
    $categories = [
        ['name'=>'Hair Care',        'slug'=>'hair-care',      'description'=>'Shampoos, conditioners, oils, and treatments for natural hair.', 'is_active'=>1],
        ['name'=>'Styling Products', 'slug'=>'styling',        'description'=>'Gels, creams, and sprays for protective styles.', 'is_active'=>1],
        ['name'=>'Scalp Care',       'slug'=>'scalp-care',     'description'=>'Products targeting scalp health and growth.', 'is_active'=>1],
        ['name'=>'Clothing',         'slug'=>'clothing',       'description'=>'GYC Naturals African-inspired fashion line.', 'is_active'=>1],
        ['name'=>'Accessories',      'slug'=>'accessories',    'description'=>'Hair accessories, tools, and essentials.', 'is_active'=>1],
        ['name'=>'Kits & Bundles',   'slug'=>'kits-bundles',   'description'=>'Curated product kits for complete hair routines.', 'is_active'=>1],
    ];
    foreach ($categories as $c) {
        $db->prepare("INSERT IGNORE INTO categories (name,slug,description,is_active) VALUES (?,?,?,?)")
           ->execute([$c['name'],$c['slug'],$c['description'],$c['is_active']]);
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
        ['name'=>'GYC Crown Shea Butter Shampoo', 'slug'=>'gyc-crown-shea-butter-shampoo', 'sku'=>'GYC-SHP-001', 'price'=>4500, 'compare_at_price'=>5500, 'category_id'=>$catIds['hair-care']??1, 'stock_quantity'=>85, 'hair_type'=>'natural', 'concern'=>'dryness', 'product_type'=>'shampoo', 'key_ingredient'=>'Shea Butter', 'volume_ml'=>250, 'is_featured'=>1, 'description'=>'A deeply moisturising shampoo infused with pure shea butter and African black soap. Gently cleanses while preserving your hair\'s natural oils. Free from sulphates, parabens, and synthetic fragrances. Suitable for all natural hair types including 4A, 4B, and 4C.', 'image'=>'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=600&q=80'],
        ['name'=>'GYC Deep Repair Conditioner', 'slug'=>'gyc-deep-repair-conditioner', 'sku'=>'GYC-CON-001', 'price'=>5200, 'compare_at_price'=>6000, 'category_id'=>$catIds['hair-care']??1, 'stock_quantity'=>72, 'hair_type'=>'natural', 'concern'=>'breakage', 'product_type'=>'conditioner', 'key_ingredient'=>'Argan Oil', 'volume_ml'=>300, 'is_featured'=>1, 'description'=>'Intense repair conditioner with argan oil, raw honey, and baobab protein. Penetrates the cortex to repair damage from tension and heat. Leaves hair soft, detangled, and visibly healthier after first use.', 'image'=>'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&q=80'],
        ['name'=>'GYC Castor & Chebe Growth Oil', 'slug'=>'gyc-castor-chebe-growth-oil', 'sku'=>'GYC-OIL-001', 'price'=>6800, 'compare_at_price'=>8000, 'category_id'=>$catIds['hair-care']??1, 'stock_quantity'=>104, 'hair_type'=>'all', 'concern'=>'growth', 'product_type'=>'oil', 'key_ingredient'=>'Jamaican Black Castor Oil', 'volume_ml'=>120, 'is_featured'=>1, 'description'=>'Our bestselling growth oil combines Jamaican black castor oil with chebe powder, moringa, and rosemary. Massaging into the scalp stimulates blood flow, strengthens roots, and dramatically reduces shedding. Used by 500+ GYC clients.', 'image'=>'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=600&q=80'],
        ['name'=>'GYC Loc Butter Leave-In Cream', 'slug'=>'gyc-loc-butter-leave-in', 'sku'=>'GYC-LVN-001', 'price'=>4800, 'compare_at_price'=>null, 'category_id'=>$catIds['hair-care']??1, 'stock_quantity'=>63, 'hair_type'=>'locs', 'concern'=>'moisture', 'product_type'=>'leave-in', 'key_ingredient'=>'Mango Butter', 'volume_ml'=>200, 'is_featured'=>0, 'description'=>'Specifically formulated for locs and twists. Mango butter, aloe vera, and green tea extract provide lasting moisture without buildup. Lightweight formula dries quickly and won\'t attract lint.', 'image'=>'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&q=80'],
        // Styling
        ['name'=>'GYC Edge Control & Shine Gel', 'slug'=>'gyc-edge-control-shine-gel', 'sku'=>'GYC-GEL-001', 'price'=>2500, 'compare_at_price'=>3000, 'category_id'=>$catIds['styling']??2, 'stock_quantity'=>150, 'hair_type'=>'natural', 'concern'=>'definition', 'product_type'=>'gel', 'key_ingredient'=>'Aloe Vera', 'volume_ml'=>100, 'is_featured'=>1, 'description'=>'Firm hold edge control enriched with aloe vera, black castor oil, and vitamin E. Lays edges flat for up to 48 hours. No white residue, no flaking. Humidity-resistant for Lagos weather.', 'image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=600&q=80'],
        ['name'=>'GYC Braid & Twist Spray', 'slug'=>'gyc-braid-twist-spray', 'sku'=>'GYC-SPR-001', 'price'=>3200, 'compare_at_price'=>null, 'category_id'=>$catIds['styling']??2, 'stock_quantity'=>89, 'hair_type'=>'natural', 'concern'=>'frizz', 'product_type'=>'spray', 'key_ingredient'=>'Glycerin', 'volume_ml'=>200, 'is_featured'=>0, 'description'=>'A lightweight mist that keeps braids and twists looking fresh for weeks. Glycerin attracts moisture, while tea tree oil maintains scalp health. Perfect between salon visits.', 'image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=600&q=80'],
        // Scalp Care
        ['name'=>'GYC Scalp Revival Treatment', 'slug'=>'gyc-scalp-revival-treatment', 'sku'=>'GYC-SCP-001', 'price'=>7500, 'compare_at_price'=>9000, 'category_id'=>$catIds['scalp-care']??3, 'stock_quantity'=>47, 'hair_type'=>'all', 'concern'=>'scalp health', 'product_type'=>'treatment', 'key_ingredient'=>'Peppermint Oil', 'volume_ml'=>150, 'is_featured'=>1, 'description'=>'Intensive scalp treatment with peppermint, tea tree, neem oil, and vitamin B5. Targets dandruff, itching, and thinning. Formulated by our in-house trichologist for Nigerian climate conditions.', 'image'=>'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=600&q=80'],
        ['name'=>'GYC Rosemary Scalp Serum', 'slug'=>'gyc-rosemary-scalp-serum', 'sku'=>'GYC-SRM-001', 'price'=>8900, 'compare_at_price'=>10500, 'category_id'=>$catIds['scalp-care']??3, 'stock_quantity'=>38, 'hair_type'=>'all', 'concern'=>'growth', 'product_type'=>'serum', 'key_ingredient'=>'Rosemary Extract', 'volume_ml'=>50, 'is_featured'=>1, 'description'=>'Clinically studied rosemary extract serum for hair regrowth and scalp stimulation. Shown to be as effective as minoxidil in independent studies, without side effects. Apply 10 drops to scalp nightly.', 'image'=>'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?w=600&q=80'],
        // Clothing
        ['name'=>'GYC Ankara Wrap Dress', 'slug'=>'gyc-ankara-wrap-dress', 'sku'=>'GYC-CLT-001', 'price'=>28000, 'compare_at_price'=>35000, 'category_id'=>$catIds['clothing']??4, 'stock_quantity'=>25, 'hair_type'=>null, 'concern'=>null, 'product_type'=>null, 'key_ingredient'=>null, 'volume_ml'=>null, 'is_featured'=>1, 'description'=>'Bold Ankara print wrap dress. 100% cotton. Made in Lagos. Adjustable fit. Available in sizes S–XL. Each piece is unique — fabric patterns may vary slightly.', 'image'=>'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=600&q=80'],
        ['name'=>'GYC Kente Print Headwrap', 'slug'=>'gyc-kente-print-headwrap', 'sku'=>'GYC-CLT-002', 'price'=>4500, 'compare_at_price'=>6000, 'category_id'=>$catIds['clothing']??4, 'stock_quantity'=>60, 'hair_type'=>null, 'concern'=>null, 'product_type'=>null, 'key_ingredient'=>null, 'volume_ml'=>null, 'is_featured'=>0, 'description'=>'Premium kente-inspired fabric headwrap. 2m x 0.5m. Versatile styling — wrap, twist, or tie. Pre-washed, soft-to-touch cotton blend. Protects natural hair while making a statement.', 'image'=>'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=600&q=80'],
        // Accessories
        ['name'=>'GYC Satin Bonnet (Adjustable)', 'slug'=>'gyc-satin-bonnet', 'sku'=>'GYC-ACC-001', 'price'=>3500, 'compare_at_price'=>null, 'category_id'=>$catIds['accessories']??5, 'stock_quantity'=>120, 'hair_type'=>'all', 'concern'=>'protection', 'product_type'=>null, 'key_ingredient'=>null, 'volume_ml'=>null, 'is_featured'=>0, 'description'=>'Premium satin bonnet with adjustable elastic band. Fits all hair sizes including loc-wearers. Reduces overnight friction to minimise breakage and preserve styles. Available in 6 colours.', 'image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=600&q=80'],
        ['name'=>'GYC Wide-Tooth Seamless Comb', 'slug'=>'gyc-wide-tooth-comb', 'sku'=>'GYC-ACC-002', 'price'=>1800, 'compare_at_price'=>null, 'category_id'=>$catIds['accessories']??5, 'stock_quantity'=>200, 'hair_type'=>'natural', 'concern'=>'detangling', 'product_type'=>null, 'key_ingredient'=>null, 'volume_ml'=>null, 'is_featured'=>0, 'description'=>'Seamless wide-tooth comb designed specifically for natural hair textures 3C–4C. No seams means no snags or breakage. Detangle from ends to roots with ease.', 'image'=>'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?w=600&q=80'],
    ];

    foreach ($products as $p) {
        $stmt = $db->prepare(
            "INSERT IGNORE INTO products (name,slug,sku,description,price,compare_price,category_id,
             stock_quantity,hair_type,concern,product_type,key_ingredient,volume_ml,
             is_active,is_featured,image,display_order,created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,1,?,?,?,NOW())"
        );
        $stmt->execute([
            $p['name'],$p['slug'],$p['sku'],$p['description'],$p['price'],
            $p['compare_at_price']??null,$p['category_id'],$p['stock_quantity'],
            $p['hair_type']??null,$p['concern']??null,$p['product_type']??null,
            $p['key_ingredient']??null,$p['volume_ml']??null,
            $p['is_featured'],$p['image']??'',99,
        ]);
    }
    $ok("Products seeded (" . count($products) . ")");

    // ── 3. Gallery Categories ────────────────────────────
    $galCats = [
        ['name'=>'Knotless Box Braids','slug'=>'knotless-box-braids','is_active'=>1],
        ['name'=>'Faux Locs',          'slug'=>'faux-locs',          'is_active'=>1],
        ['name'=>'Cornrows',           'slug'=>'cornrows',           'is_active'=>1],
        ['name'=>'Senegalese Twists',  'slug'=>'senegalese-twists',  'is_active'=>1],
        ['name'=>'Natural Styles',     'slug'=>'natural-styles',     'is_active'=>1],
        ['name'=>'Starter Locs',       'slug'=>'starter-locs',       'is_active'=>1],
    ];
    foreach ($galCats as $gc) {
        $db->prepare("INSERT IGNORE INTO gallery_categories (name,slug,is_active) VALUES (?,?,?)")
           ->execute([$gc['name'],$gc['slug'],$gc['is_active']]);
    }
    $ok("Gallery categories seeded");

    $gcIds = [];
    foreach ($db->query("SELECT id, slug FROM gallery_categories")->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $gcIds[$r['slug']] = (int)$r['id'];
    }

    // ── 4. Gallery Images ─────────────────────────────────
    $styles = [
        ['title'=>'Waist-Length Knotless Braids','slug'=>'waist-length-knotless-braids','category_id'=>$gcIds['knotless-box-braids']??1,'hair_type'=>'natural','duration_hours'=>8,'price_from'=>55000,'image'=>'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=800&q=80','is_featured'=>1,'description'=>'Extra-long knotless box braids reaching the waist. Lightweight thanks to the tension-free knotless technique. Low manipulation, scalp-friendly.'],
        ['title'=>'Medium Knotless Braids with Curls','slug'=>'medium-knotless-braids-curls','category_id'=>$gcIds['knotless-box-braids']??1,'hair_type'=>'natural','duration_hours'=>6,'price_from'=>42000,'image'=>'https://images.unsplash.com/photo-1519457431-44ccd64a579b?w=800&q=80','is_featured'=>1,'description'=>'Mid-length knotless braids with boho curly ends. Romantic and professional. Very popular for weddings and corporate settings.'],
        ['title'=>'Jumbo Faux Locs','slug'=>'jumbo-faux-locs','category_id'=>$gcIds['faux-locs']??2,'hair_type'=>'all','duration_hours'=>9,'price_from'=>65000,'image'=>'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=800&q=80','is_featured'=>1,'description'=>'Bold jumbo faux locs for maximum impact. Distressed ends add authenticity. Lightweight wrapping technique keeps the look for 8–10 weeks.'],
        ['title'=>'Butterfly Locs','slug'=>'butterfly-locs','category_id'=>$gcIds['faux-locs']??2,'hair_type'=>'all','duration_hours'=>8,'price_from'=>58000,'image'=>'https://images.unsplash.com/photo-1536697246787-1f7ae568d89a?w=800&q=80','is_featured'=>0,'description'=>'Trendy wavy butterfly locs. Soft and feminine. The looped technique creates a wispy, carefree look.'],
        ['title'=>'Cornrow Updo','slug'=>'cornrow-updo','category_id'=>$gcIds['cornrows']??3,'hair_type'=>'natural','duration_hours'=>3,'price_from'=>18000,'image'=>'https://images.unsplash.com/photo-1555116505-38ab61800975?w=800&q=80','is_featured'=>1,'description'=>'Elegant cornrow updo with intricate patterning. A classic protective style elevated with artistic braiding lines.'],
        ['title'=>'Fulani Braids','slug'=>'fulani-braids','category_id'=>$gcIds['cornrows']??3,'hair_type'=>'natural','duration_hours'=>4,'price_from'=>22000,'image'=>'https://images.unsplash.com/photo-1542704792-e50d7e45b7cd?w=800&q=80','is_featured'=>1,'description'=>'Traditional Fulani-inspired braiding pattern with a centre cornrow and side braids. Often adorned with gold cuffs and beads.'],
        ['title'=>'Senegalese Rope Twists','slug'=>'senegalese-rope-twists','category_id'=>$gcIds['senegalese-twists']??4,'hair_type'=>'natural','duration_hours'=>7,'price_from'=>38000,'image'=>'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=800&q=80','is_featured'=>0,'description'=>'Silky Senegalese rope twists for a sleek, defined look. Kanekalon hair gives a natural sheen. Lasts up to 6 weeks with proper care.'],
        ['title'=>'Goddess Locs','slug'=>'goddess-locs','category_id'=>$gcIds['natural-styles']??5,'hair_type'=>'natural','duration_hours'=>7,'price_from'=>50000,'image'=>'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=800&q=80','is_featured'=>1,'description'=>'Bohemian goddess locs with soft wavy ends. The blend of straight roots and wavy ends creates a stunning natural effect.'],
        ['title'=>'Starter Locs (Comb Coils)','slug'=>'starter-locs-comb-coils','category_id'=>$gcIds['starter-locs']??6,'hair_type'=>'natural','duration_hours'=>5,'price_from'=>30000,'image'=>'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=800&q=80','is_featured'=>0,'description'=>'Begin your loc journey with our expert starter locs service. Comb coil method for clean, even parts. Includes a full consultation and aftercare kit.'],
    ];
    foreach ($styles as $s) {
        $db->prepare(
            "INSERT IGNORE INTO gallery_images (title,slug,category_id,style_type,duration_hours,
             price_from,image_url,is_featured,is_active,description,created_at)
             VALUES (?,?,?,?,?,?,?,?,1,?,NOW())"
        )->execute([
            $s['title'],$s['slug'],$s['category_id'],'knotless',
            $s['duration_hours'],$s['price_from'],$s['image'],$s['is_featured'],
            $s['description'],
        ]);
    }
    $ok("Gallery images seeded (" . count($styles) . ")");

    // ── 5. Blog Posts ─────────────────────────────────────
    $posts = [
        [
            'title'     => '5 Reasons Knotless Braids Are the Best Protective Style for Nigerian Women',
            'slug'      => '5-reasons-knotless-braids-best-protective-style-nigerian-women',
            'excerpt'   => 'Knotless braids have taken over Lagos salons — and for good reason. Discover why this style is gentler, longer-lasting, and more versatile than traditional box braids.',
            'body'      => '<h2>What Are Knotless Braids?</h2><p>Unlike traditional box braids that start with a tight knot at the root, knotless braids begin with your natural hair and gradually add extensions. The result? Zero tension on your edges, less breakage, and a more natural-looking root.</p><h2>1. No Tension = Healthier Edges</h2><p>Edge loss is a real concern for women who regularly braid. The tight knot at the base of traditional braids creates constant traction, especially on the fragile edges around your hairline. Knotless braids eliminate this entirely.</p><h2>2. Lightweight on the Scalp</h2><p>Because the extension hair is gradually added, knotless braids weigh significantly less than traditional braids — especially in longer styles. This means less headaches and more comfort for the 6–10 weeks you wear them.</p><h2>3. Longer Lifespan</h2><p>With proper care — night bonnet, weekly scalp spritz, and avoiding heavy products — knotless braids can last up to 10 weeks without looking tired.</p><h2>4. More Styling Versatility</h2><p>The flat, seamless base allows for more styling options: high buns, ponytails, half-up-half-down styles, and even messy buns all look cleaner with knotless braids.</p><h2>5. Natural Scalp Access</h2><p>The grid pattern of knotless braids allows easy access to your scalp for washing and oiling — meaning your hair stays healthy throughout the protective style period.</p><p><strong>Ready to try knotless braids?</strong> <a href="/gyc-store/book-appointment.php">Book your appointment at GYC Naturals →</a></p>',
            'category'  => 'protective-styles',
            'tags'      => 'knotless braids, protective styles, Lagos, natural hair, braids',
            'author'    => 'Grace Yakubu, GYC Naturals',
            'read_time' => 5,
            'is_featured'=> 1,
            'status'    => 'published',
            'image'     => 'https://images.unsplash.com/photo-1522338242992-e1a54906a8da?w=1200&q=80',
        ],
        [
            'title'     => 'The GYC Guide to a Healthy Hair Regimen for 4C Hair',
            'slug'      => 'gyc-guide-healthy-hair-regimen-4c-hair',
            'excerpt'   => 'Building a consistent routine is the single most important thing you can do for 4C hair. Here is our complete step-by-step guide, tested by hundreds of GYC clients.',
            'body'      => '<h2>Understanding 4C Hair</h2><p>4C hair is the tightest curl pattern on the natural hair spectrum. It has the least defined curl pattern, shrinks up to 75% of its actual length, and is prone to dryness due to the difficulty natural oils have coating the tightly coiled shaft.</p><p>But 4C hair is also incredibly versatile, full, and beautiful when properly cared for.</p><h2>The GYC 4C Weekly Regimen</h2><h3>Step 1: Pre-Poo (Once a Week)</h3><p>Apply our Castor & Chebe Growth Oil to your hair before washing. Section into 4–6 twists and leave for 30 minutes or overnight. This protects the hair during the cleansing process.</p><h3>Step 2: Cleanse</h3><p>Use our Crown Shea Butter Shampoo. Apply in sections, gently massaging the scalp. Avoid scrubbing the hair itself.</p><h3>Step 3: Deep Condition</h3><p>After rinsing, apply our Deep Repair Conditioner generously from root to tip. Cover with a plastic cap and sit under a hooded dryer for 30 minutes, or use body heat for 45–60 minutes.</p><h3>Step 4: Leave-In & Seal</h3><p>On damp hair, apply our Loc Butter Leave-In Cream, then seal with a light oil. This LOC method (Liquid, Oil, Cream) is the gold standard for 4C moisture retention.</p><h3>Step 5: Style & Protect</h3><p>Style into a protective style of your choice, or wear it loose. Always sleep in a satin bonnet.</p>',
            'category'  => 'hair-care',
            'tags'      => '4c hair, hair regimen, natural hair care, moisturising, GYC Naturals',
            'author'    => 'Adaeze Nwachukwu, Product Dev',
            'read_time' => 7,
            'is_featured'=> 1,
            'status'    => 'published',
            'image'     => 'https://images.unsplash.com/photo-1556228453-efd6c1ff04f6?w=1200&q=80',
        ],
        [
            'title'     => 'How to Maintain Your Braids for 8+ Weeks in Lagos Heat',
            'slug'      => 'maintain-braids-8-weeks-lagos-heat',
            'excerpt'   => 'Lagos humidity and heat can shorten the lifespan of your braids. Our maintenance guide will help you keep them looking fresh for twice as long.',
            'body'      => '<h2>Why Braids Deteriorate Faster in Lagos</h2><p>Lagos weather — hot, humid, and sometimes dusty — is uniquely challenging for braided styles. Humidity causes frizz, sweat accumulates on the scalp, and dust settles into braids. Add the typical Lagos commute and your braids can look tired within 3 weeks.</p><h2>Week 1–2: The Fresh Period</h2><p>Your braids should look polished and tidy. Focus on protecting edges with a light edge control and wearing a satin bonnet nightly.</p><h2>Week 3–4: The Critical Window</h2><p>This is when most people start to see frizz and lifting at the roots. Our Braid & Twist Spray is your best friend here. Spray lightly on braids and smooth with a soft toothbrush.</p><p>At week 4, do a scalp cleanse: dilute a small amount of our shampoo in a spray bottle with water and gently squeeze it into your scalp between braids. Rinse thoroughly.</p><h2>Week 5–8: The Long Game</h2><p>By week 5, new growth will be visible. Use an edge control to refresh your part lines. A mousse or light gel can re-define any frizzy braids.</p><p>At week 6-7, do another scalp cleanse and a light protein treatment applied to the length of your braids.</p><h2>Signs It\'s Time to Take Down</h2><ul><li>Significant new growth causing tension</li><li>Excessive matting at the roots</li><li>Scalp irritation that doesn\'t resolve with cleansing</li></ul>',
            'category'  => 'hair-care',
            'tags'      => 'braid maintenance, braids Lagos, long-lasting braids, braid care',
            'author'    => 'Grace Yakubu, GYC Naturals',
            'read_time' => 6,
            'is_featured'=> 0,
            'status'    => 'published',
            'image'     => 'https://images.unsplash.com/photo-1519457431-44ccd64a579b?w=1200&q=80',
        ],
        [
            'title'     => 'Starting Locs in Lagos: Everything You Need to Know',
            'slug'      => 'starting-locs-lagos-everything-you-need-to-know',
            'excerpt'   => 'Thinking about starting your loc journey? Our comprehensive guide covers methods, timelines, costs, and what to expect in the first year.',
            'body'      => '<h2>Is Loc\'ing Right for You?</h2><p>Starting locs is a long-term commitment. Unlike braids that you take down after 6–10 weeks, locs are a permanent (though removable) hair decision. Before starting, consider: Are you prepared for the awkward stages? Do you have the patience for a 2–3 year journey to mature locs?</p><h2>The Three Main Methods</h2><h3>Comb Coils</h3><p>The most popular method at GYC. We use a fine-tooth comb to create perfectly circular coils from root to tip. Best for 4A–4C hair. Ideal if you want neat, defined parts from day one.</p><h3>Two-Strand Twists</h3><p>Two strands of hair twisted together. Slightly less defined than comb coils initially but very popular because they look beautiful while still loose. Great for all textures.</p><h3>Interlocking</h3><p>A hook is used to thread the roots through the developing loc. This method creates very tight, stable bases — ideal for fine hair or very active lifestyles.</p><h2>The Loc Journey Stages</h2><ol><li><strong>Starter Stage (0–6 months):</strong> Your coils/twists are still loose and may unravel when wet. This is normal.</li><li><strong>Budding Stage (6–12 months):</strong> Locs begin to form and small bumps (buds) appear along the length.</li><li><strong>Teen Stage (12–18 months):</strong> Locs are forming but inconsistent in width. The awkward stage!</li><li><strong>Mature Locs (18–36 months):</strong> Fully formed, sealed ends, uniform width.</li></ol>',
            'category'  => 'protective-styles',
            'tags'      => 'locs, starter locs, dreadlocks, loc journey, Lagos',
            'author'    => 'Chinwe Okafor, Loc Specialist',
            'read_time' => 8,
            'is_featured'=> 0,
            'status'    => 'published',
            'image'     => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=1200&q=80',
        ],
    ];
    foreach ($posts as $p) {
        $db->prepare(
            "INSERT IGNORE INTO blog_posts (title,slug,excerpt,body,featured_image,category,tags,
             author,read_time,is_featured,status,view_count,published_at,created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())"
        )->execute([
            $p['title'],$p['slug'],$p['excerpt'],$p['body'],$p['image'],
            $p['category'],$p['tags'],$p['author'],$p['read_time'],
            $p['is_featured'],$p['status'],rand(45,280),
        ]);
    }
    $ok("Blog posts seeded (" . count($posts) . ")");

    // ── 6. Testimonials ───────────────────────────────────
    $testimonials = [
        ['author_name'=>'Chidinma Obi',       'author_location'=>'Victoria Island, Lagos', 'service'=>'Knotless Box Braids', 'rating'=>5, 'is_featured'=>1, 'content'=>'Grace and her team are absolute magicians! I came in for knotless braids and left with the most beautiful, tension-free style I\'ve ever had. My edges actually grew back stronger after wearing them for 8 weeks. GYC Naturals is my salon for life.'],
        ['author_name'=>'Adaora Eze',          'author_location'=>'Lekki, Lagos',           'service'=>'Faux Locs',          'rating'=>5, 'is_featured'=>1, 'content'=>'I\'ve been trying to find a salon in Lagos that truly understands natural hair for years. GYC Naturals is IT. The faux locs they did for me lasted 10 full weeks and my hair underneath was thriving. The online booking system is also so convenient!'],
        ['author_name'=>'Funmi Adeleke',       'author_location'=>'Ikeja, Lagos',           'service'=>'GYC Growth Oil',     'rating'=>5, 'is_featured'=>1, 'content'=>'The Castor & Chebe Growth Oil is liquid gold. I\'ve been using it for 3 months and my hair has grown almost 2 inches. I also noticed significantly less shedding. My sister ordered two bottles after seeing my results!'],
        ['author_name'=>'Ngozi Nwosu',         'author_location'=>'Ajah, Lagos',            'service'=>'Cornrow Updo',       'rating'=>5, 'is_featured'=>0, 'content'=>'I needed a neat cornrow style for my sister\'s wedding. Chinwe did an absolutely perfect job — intricate pattern, clean edges, and it stayed flawless for 3 weeks. Will definitely be back!'],
        ['author_name'=>'Blessing Okorie',     'author_location'=>'Surulere, Lagos',        'service'=>'Starter Locs',       'rating'=>5, 'is_featured'=>1, 'content'=>'Starting my loc journey at GYC Naturals was the best decision. They consulted with me thoroughly, explained each stage, and made the process so exciting rather than daunting. 6 months in and my locs are budding beautifully.'],
        ['author_name'=>'Amaka Chukwu',        'author_location'=>'VI, Lagos',              'service'=>'Scalp Treatment',    'rating'=>5, 'is_featured'=>0, 'content'=>'I came in with terrible dandruff and scalp inflammation. After just one scalp treatment session and using the Scalp Revival product at home, my scalp is completely clear. The team really knows what they\'re doing.'],
        ['author_name'=>'Temi Ologun',         'author_location'=>'Ikoyi, Lagos',           'service'=>'Senegalese Twists',  'rating'=>4, 'is_featured'=>0, 'content'=>'Beautiful twists that lasted 7 weeks. Would have been 5 stars but the wait was a bit long. However, the result was absolutely worth it. I\'ll definitely pre-book my next appointment!'],
    ];
    foreach ($testimonials as $t) {
        $db->prepare(
            "INSERT IGNORE INTO testimonials (author_name,author_location,service,content,rating,
             is_approved,is_featured,created_at)
             VALUES (?,?,?,?,?,1,?,NOW())"
        )->execute([
            $t['author_name'],$t['author_location'],$t['service'],
            $t['content'],$t['rating'],$t['is_featured'],
        ]);
    }
    $ok("Testimonials seeded (" . count($testimonials) . ")");

    // ── 7. Bundles ────────────────────────────────────────
    $bundleData = [
        'Starter Natural Hair Kit' => ['slug'=>'starter-natural-hair-kit','description'=>'Everything you need to start or refresh your natural hair routine. Curated by our trichologist.','discount_percentage'=>15,'is_featured'=>1,'products'=>['gyc-crown-shea-butter-shampoo','gyc-deep-repair-conditioner','gyc-castor-chebe-growth-oil']],
        'Loc Lovers Bundle'        => ['slug'=>'loc-lovers-bundle','description'=>'Products specifically chosen for loc wearers to keep locs moisturised and scalp healthy.','discount_percentage'=>12,'is_featured'=>1,'products'=>['gyc-loc-butter-leave-in','gyc-scalp-revival-treatment','gyc-braid-twist-spray']],
        'Scalp Rescue Kit'         => ['slug'=>'scalp-rescue-kit','description'=>'Combat dandruff, itching, and hair thinning with our specialist scalp bundle.','discount_percentage'=>10,'is_featured'=>0,'products'=>['gyc-scalp-revival-treatment','gyc-rosemary-scalp-serum','gyc-castor-chebe-growth-oil']],
    ];
    foreach ($bundleData as $name => $b) {
        $stmt = $db->prepare(
            "INSERT IGNORE INTO bundles (name,slug,description,discount_percentage,is_active,is_featured,display_order,created_at)
             VALUES (?,?,?,?,1,?,99,NOW())"
        );
        $stmt->execute([$name,$b['slug'],$b['description'],$b['discount_percentage'],$b['is_featured']]);
        $bundleId = (int)$db->lastInsertId();
        if ($bundleId > 0) {
            foreach ($b['products'] as $pslug) {
                $prod = $db->prepare("SELECT id FROM products WHERE slug=?")->execute([$pslug]) ?
                    $db->query("SELECT id FROM products WHERE slug='$pslug' LIMIT 1")->fetch(PDO::FETCH_ASSOC) : null;
                if ($prod) {
                    $db->prepare("INSERT IGNORE INTO bundle_items (bundle_id,product_id,quantity) VALUES (?,?,1)")
                       ->execute([$bundleId,$prod['id']]);
                }
            }
        }
    }
    $ok("Bundles seeded (" . count($bundleData) . ")");

    // ── 8. Site Settings ──────────────────────────────────
    $settings = [
        'site_name'             => 'GYC Naturals',
        'site_tagline'          => 'Grow Your Crown',
        'site_email'            => 'info@gycnaturals.com',
        'contact_email'         => 'hello@gycnaturals.com',
        'site_phone'            => '+234 800 492 4247',
        'site_whatsapp'         => '2348004924247',
        'site_address'          => '14 Akin Adesola Street, Victoria Island, Lagos, Nigeria',
        'opening_hours'         => 'Mon–Sat: 9:00 AM – 7:00 PM',
        'business_hours'        => 'Mon–Sat: 9am – 7pm',
        'business_address'      => 'Victoria Island, Lagos, Nigeria',
        'instagram_handle'      => 'gycnaturals',
        'instagram_url'         => 'https://instagram.com/gycnaturals',
        'social_instagram'      => 'https://instagram.com/gycnaturals',
        'social_facebook'       => 'https://facebook.com/gycnaturals',
        'social_tiktok'         => 'https://tiktok.com/@gycnaturals',
        'social_twitter'        => 'https://twitter.com/gycnaturals',
        'paystack_public_key'   => 'pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'paystack_secret_key'   => 'sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'free_shipping_threshold'=> '50000',
        'default_shipping_fee'  => '2500',
        'resend_api_key'        => '',
        'from_name'             => 'GYC Naturals',
        'from_email'            => 'info@gycnaturals.com',
    ];
    foreach ($settings as $k => $v) {
        $db->prepare("INSERT INTO site_settings (setting_key,setting_val) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_val=?")
           ->execute([$k,$v,$v]);
    }
    $ok("Site settings seeded (" . count($settings) . ")");

    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

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
  .warn { background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 1rem; margin-top: 1.5rem; font-size: .85rem; color: #92400E; }
  .links { margin-top: 1.5rem; display: flex; gap: .75rem; flex-wrap: wrap; }
  .links a { padding: .5rem 1rem; background: #166534; color: #fff; border-radius: 6px; text-decoration: none; font-size: .85rem; }
  .links a:hover { background: #14532D; }
</style>
</head>
<body>
<h1>🌿 GYC Naturals Database Seeder</h1>
<?php foreach ($out as [$icon, $msg]): ?>
<div class="item"><span class="icon"><?= $icon ?></span><span class="msg"><?= htmlspecialchars($msg) ?></span></div>
<?php endforeach; ?>
<div class="warn">
  <strong>⚠ Security notice:</strong> Delete <code>seed.php</code> from your server now. It inserts demo data and must not remain accessible in production.
</div>
<div class="links">
  <a href="/gyc-store/">View Homepage</a>
  <a href="/gyc-store/shop.php">Shop</a>
  <a href="/gyc-store/gallery.php">Gallery</a>
  <a href="/gyc-store/admin/">Admin Panel</a>
  <a href="/gyc-store/blog.php">Blog</a>
</div>
</body>
</html>
