<?php
// GYC Naturals — Generate PWA Icons + OG Image + Favicon
define('GYC_ACCESS', true);
header('Content-Type: text/plain; charset=utf-8');
$base = __DIR__ . '/assets/images/';

function makeIcon($size, $path) {
    $img = imagecreatetruecolor($size, $size);
    // Background circle
    $bg   = imagecolorallocate($img, 254, 250, 224);    // cream
    $green= imagecolorallocate($img, 45, 106, 79);      // green-700
    $lt   = imagecolorallocate($img, 82, 183, 136);     // light green
    $white= imagecolorallocate($img, 255, 255, 255);
    imagefilledrectangle($img, 0, 0, $size, $size, $bg);
    imagefilledellipse($img, $size/2, $size/2, $size, $size, $green);

    // Simple three-leaf motif (scaled)
    $s = $size / 100;
    // Center leaf
    $cx = 50 * $s; $cy = 70 * $s;
    for ($i = 0; $i <= 100; $i++) {
        $t   = $i / 100.0;
        $bx1 = 35; $by1 = 55; $bx2 = 35; $by2 = 20; $ex = 50; $ey = 10;
        $x1  = (1-$t)*(1-$t)*(1-$t)*50 + 3*(1-$t)*(1-$t)*$t*$bx1 + 3*(1-$t)*$t*$t*$bx2 + $t*$t*$t*$ex;
        $y1  = (1-$t)*(1-$t)*(1-$t)*70 + 3*(1-$t)*(1-$t)*$t*$by1 + 3*(1-$t)*$t*$t*$by2 + $t*$t*$t*$ey;
        $x2  = (1-$t)*(1-$t)*(1-$t)*50 + 3*(1-$t)*(1-$t)*$t*65   + 3*(1-$t)*$t*$t*65   + $t*$t*$t*$ex;
        $y2  = (1-$t)*(1-$t)*(1-$t)*70 + 3*(1-$t)*(1-$t)*$t*55   + 3*(1-$t)*$t*$t*20   + $t*$t*$t*$ey;
        imagefilledellipse($img, (int)($x1*$s), (int)($y1*$s), (int)(8*$s), (int)(8*$s), $lt);
        imagefilledellipse($img, (int)($x2*$s), (int)($y2*$s), (int)(8*$s), (int)(8*$s), $lt);
    }
    imageline($img, (int)(50*$s), (int)(70*$s), (int)(50*$s), (int)(12*$s), $white);

    // Text "GYC" for larger icons
    if ($size >= 192 && function_exists('imagestring')) {
        $textColor = imagecolorallocate($img, 255, 255, 255);
        $fontSize  = max(3, (int)($size / 48));
        $textX     = (int)($size * 0.28);
        $textY     = (int)($size * 0.78);
        imagestring($img, $fontSize, $textX, $textY, 'GYC', $textColor);
    }
    imagepng($img, $path);
    imagedestroy($img);
    echo "✓ Created: $path\n";
}

// Generate icons
makeIcon(192, $base . 'icon-192.png');
makeIcon(512, $base . 'icon-512.png');
makeIcon(32,  $base . 'icon-32.png');

// Favicon (copy 32px as .ico — basic)
copy($base . 'icon-32.png', $base . 'favicon.ico');
echo "✓ favicon.ico created\n";

// OG default image 1200x630
$og   = imagecreatetruecolor(1200, 630);
$bg   = imagecolorallocate($og, 45, 106, 79);
$bg2  = imagecolorallocate($og, 27, 67, 50);
$gold = imagecolorallocate($og, 201, 168, 76);
$white= imagecolorallocate($og, 255, 255, 255);
$cream= imagecolorallocate($og, 254, 250, 224);
// Gradient-ish background
imagefilledrectangle($og, 0, 0, 1200, 630, $bg2);
imagefilledrectangle($og, 0, 0, 1200, 315, $bg);
// Leaf shapes
for ($i = 0; $i < 60; $i++) {
    $alpha = imagecolorallocatealpha($og, 64, 145, 108, 110 - $i);
    imagefilledellipse($og, 600 + $i * 3, 200 + $i * 2, 80, 160, $alpha);
}
// Title text
if (function_exists('imagettftext') && file_exists('C:/Windows/Fonts/arialbd.ttf')) {
    imagettftext($og, 64, 0, 200, 280, $white, 'C:/Windows/Fonts/arialbd.ttf', 'GYC NATURALS');
    imagettftext($og, 28, 0, 205, 340, $gold,  'C:/Windows/Fonts/arialbd.ttf', 'Grow Your Crown');
} else {
    imagestring($og, 5, 200, 250, 'GYC NATURALS',  $white);
    imagestring($og, 4, 205, 280, 'Grow Your Crown', $gold);
    imagestring($og, 3, 200, 310, 'Professional Hair Braiding · Lagos, Nigeria', $cream);
}
imagejpeg($og, $base . 'og-default.jpg', 90);
imagedestroy($og);
echo "✓ og-default.jpg created\n";

echo "\n✅ All assets generated.\n";
