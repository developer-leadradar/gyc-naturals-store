<?php
/**
 * GYC Naturals — Dynamic XML Sitemap
 * Access: /gyc-store/sitemap.php
 */
define('GYC_ACCESS', true);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');
header('X-Robots-Tag: noindex');   // Don't index the sitemap itself

$db   = getDB();
$base = rtrim(SITE_URL, '/');

// Helper: XML-safe URL
function sitemapUrl(string $loc, string $lastmod = '', string $changefreq = 'monthly', string $priority = '0.5'): string {
    $xml = "  <url>\n    <loc>" . htmlspecialchars($loc) . "</loc>\n";
    if ($lastmod) $xml .= "    <lastmod>" . htmlspecialchars($lastmod) . "</lastmod>\n";
    $xml .= "    <changefreq>$changefreq</changefreq>\n";
    $xml .= "    <priority>$priority</priority>\n";
    $xml .= "  </url>\n";
    return $xml;
}

$today = date('Y-m-d');
$xml   = '';

// ── Static pages ──────────────────────────────────────────
$staticPages = [
    [''                        , $today, 'daily',   '1.0'],
    ['shop.php'                , $today, 'daily',   '0.9'],
    ['gallery.php'             , $today, 'weekly',  '0.9'],
    ['book-appointment.php'    , $today, 'weekly',  '0.9'],
    ['services.php'            , $today, 'monthly', '0.8'],
    ['about.php'               , $today, 'monthly', '0.7'],
    ['clothing.php'            , $today, 'weekly',  '0.8'],
    ['blog.php'                , $today, 'daily',   '0.8'],
    ['testimonials.php'        , $today, 'weekly',  '0.7'],
    ['contact.php'             , $today, 'monthly', '0.6'],
    ['faq.php'                 , $today, 'monthly', '0.6'],
    ['quiz.php'                , $today, 'monthly', '0.6'],
    ['moodboard.php'           , $today, 'monthly', '0.5'],
    ['login.php'               , $today, 'yearly',  '0.3'],
    ['register.php'            , $today, 'yearly',  '0.3'],
    ['privacy.php'             , $today, 'yearly',  '0.3'],
    ['terms.php'               , $today, 'yearly',  '0.3'],
    ['refund.php'              , $today, 'yearly',  '0.4'],
];

foreach ($staticPages as [$path, $lm, $cf, $pri]) {
    $loc = $path ? "$base/$path" : "$base/";
    $xml .= sitemapUrl($loc, $lm, $cf, $pri);
}

// ── Products ──────────────────────────────────────────────
$products = $db->fetchAll("SELECT slug, updated_at FROM products WHERE is_active=1 ORDER BY updated_at DESC");
foreach ($products as $p) {
    $xml .= sitemapUrl(
        "$base/product.php?slug=" . urlencode($p['slug']),
        $p['updated_at'] ? substr($p['updated_at'],0,10) : $today,
        'weekly',
        '0.7'
    );
}

// ── Shop category pages ────────────────────────────────────
$cats = $db->fetchAll("SELECT id, slug FROM categories ORDER BY id");
foreach ($cats as $c) {
    $xml .= sitemapUrl("$base/shop.php?category=" . urlencode($c['slug']), $today, 'weekly', '0.7');
}

// ── Gallery styles ────────────────────────────────────────
$styles = $db->fetchAll("SELECT slug, updated_at FROM gallery_images WHERE is_active=1 ORDER BY updated_at DESC");
foreach ($styles as $s) {
    $xml .= sitemapUrl(
        "$base/style-detail.php?slug=" . urlencode($s['slug']),
        $s['updated_at'] ? substr($s['updated_at'],0,10) : $today,
        'monthly',
        '0.6'
    );
}

// ── Blog posts ────────────────────────────────────────────
$posts = $db->fetchAll("SELECT slug, published_at FROM blog_posts WHERE status='published' ORDER BY published_at DESC");
foreach ($posts as $bp) {
    $xml .= sitemapUrl(
        "$base/blog-post.php?slug=" . urlencode($bp['slug']),
        $bp['published_at'] ? substr($bp['published_at'],0,10) : $today,
        'monthly',
        '0.6'
    );
}

// ── Blog category pages ───────────────────────────────────
$blogCats = $db->fetchAll("SELECT DISTINCT category FROM blog_posts WHERE status='published' AND category IS NOT NULL AND category!=''");
foreach ($blogCats as $bc) {
    $xml .= sitemapUrl("$base/blog.php?category=" . urlencode($bc['category']), $today, 'weekly', '0.5');
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
echo '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
echo '          http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
echo $xml;
echo '</urlset>';
