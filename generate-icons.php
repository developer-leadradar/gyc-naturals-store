<?php
/**
 * GYC Naturals — Icon Generator
 * Run once to create missing PWA icon sizes from the 512px source.
 * Access: http://localhost/gyc-store/generate-icons.php
 * DELETE after use.
 */

$src   = __DIR__ . '/assets/images/icon-512.png';
$sizes = [72, 96, 128, 384];

if (!file_exists($src)) {
    die('<b>Error:</b> icon-512.png not found in assets/images/');
}

if (!function_exists('imagecreatefromstring')) {
    die('<b>Error:</b> GD library not available. Enable gd extension in php.ini.');
}

$srcImg = imagecreatefrompng($src);
if (!$srcImg) {
    die('<b>Error:</b> Could not load source icon.');
}

$origW = imagesx($srcImg);
$origH = imagesy($srcImg);
$results = [];

foreach ($sizes as $size) {
    $dest = __DIR__ . '/assets/images/icon-' . $size . '.png';
    if (file_exists($dest)) {
        $results[] = "✓ icon-{$size}.png already exists — skipped.";
        continue;
    }
    $out = imagecreatetruecolor($size, $size);
    imagealphablending($out, false);
    imagesavealpha($out, true);
    $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefilledrectangle($out, 0, 0, $size, $size, $transparent);
    imagecopyresampled($out, $srcImg, 0, 0, 0, 0, $size, $size, $origW, $origH);
    if (imagepng($out, $dest, 9)) {
        $results[] = "✅ icon-{$size}.png created.";
    } else {
        $results[] = "❌ Failed to write icon-{$size}.png — check write permissions.";
    }
    imagedestroy($out);
}
imagedestroy($srcImg);

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>GYC Icon Generator</title>';
echo '<style>body{font-family:sans-serif;padding:2rem;max-width:600px;} li{margin:.4rem 0;}</style></head><body>';
echo '<h2>GYC Naturals — PWA Icon Generator</h2><ul>';
foreach ($results as $r) echo "<li>$r</li>";
echo '</ul>';
echo '<p style="color:#888;margin-top:1rem;font-size:.85rem;">⚠ <strong>Delete this file</strong> after running it: <code>generate-icons.php</code></p>';
echo '</body></html>';
