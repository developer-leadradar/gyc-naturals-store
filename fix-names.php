<?php
// One-time fix: replace Grace Yakubu/Adaeze Nwachukwu in DB text
define('GYC_ACCESS', true);
if (($_GET['key'] ?? '') !== 'GYCfix2024') { http_response_code(403); exit('Forbidden'); }
header('Content-Type: text/plain; charset=utf-8');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$db = getDB();
$results = [];

// Fix blog post authors
$r = $db->query(
    "UPDATE blog_posts SET author_name = 'Juliet Arah, GYC Naturals' WHERE author_name LIKE '%Grace Yakubu%' OR author_name LIKE '%Adaeze Nwachukwu%' OR author_name LIKE '%Chinwe%'"
);
$results[] = "Blog post authors fixed: " . ($r->rowCount() ?? 0) . " rows";

// Fix testimonial content mentioning Grace
$r2 = $db->query(
    "UPDATE testimonials SET content = REPLACE(content, 'Grace and her team', 'Juliet and her team') WHERE content LIKE '%Grace and her team%'"
);
$results[] = "Testimonial 'Grace and her team' fixed: " . ($r2->rowCount() ?? 0) . " rows";

$r3 = $db->query(
    "UPDATE testimonials SET content = REPLACE(content, 'Grace ', 'Juliet ') WHERE content LIKE '%Grace %'"
);
$results[] = "Testimonial 'Grace' references fixed: " . ($r3->rowCount() ?? 0) . " rows";

// Fix testimonial author names
$r4 = $db->query(
    "UPDATE testimonials SET author_name = REPLACE(author_name, 'Grace', 'Juliet') WHERE author_name LIKE '%Grace%'"
);
$results[] = "Testimonial author names fixed: " . ($r4->rowCount() ?? 0) . " rows";

// Fix blog post body text mentioning Lagos salons in context of GYC
$r5 = $db->query(
    "UPDATE blog_posts SET excerpt = REPLACE(excerpt, 'Lagos salons', 'Calabar salons') WHERE excerpt LIKE '%Lagos salons%'"
);
$results[] = "Blog excerpts Lagos->Calabar: " . ($r5->rowCount() ?? 0) . " rows";

foreach ($results as $line) {
    echo "✅ $line\n";
}
echo "\n⚠️  DELETE THIS FILE NOW: /fix-names.php\n";
echo "\nDone.\n";
