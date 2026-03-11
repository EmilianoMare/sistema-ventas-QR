<?php
// Generates a simple square logo PNG with a rounded background and a 'V' letter
// Requires GD extension. Writes to app/views/img/logo.png
$outDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'img';
$outFile = $outDir . DIRECTORY_SEPARATOR . 'logo.png';
if (!is_dir($outDir)) mkdir($outDir, 0755, true);
$size = 1024;
$im = imagecreatetruecolor($size, $size);
imagesavealpha($im, true);
$trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
imagefill($im, 0, 0, $trans);

// background rounded rectangle
$bg = imagecolorallocate($im, 50, 115, 220); // blue
$radius = 120;
// fill rounded rect by drawing filled rectangle and four filled ellipses
imagefilledrectangle($im, $radius, 0, $size-$radius, $size, $bg);
imagefilledrectangle($im, 0, $radius, $size, $size-$radius, $bg);
imagefilledellipse($im, $radius, $radius, $radius*2, $radius*2, $bg);
imagefilledellipse($im, $size-$radius, $radius, $radius*2, $radius*2, $bg);
imagefilledellipse($im, $radius, $size-$radius, $radius*2, $radius*2, $bg);
imagefilledellipse($im, $size-$radius, $size-$radius, $radius*2, $radius*2, $bg);

// draw 'V' letter in white
$white = imagecolorallocate($im, 255,255,255);
$thickness = 120;
imagesetthickness($im, $thickness);
$offset = 240;
imageline($im, $offset, 220, $size/2, $size-180, $white);
imageline($im, $size-$offset, 220, $size/2, $size-180, $white);

// small inner accent
$accent = imagecolorallocate($im, 255,215,0); // gold
imagefilledellipse($im, $size/2, 300, 120, 120, $accent);

// save PNG
imagepng($im, $outFile, 9);
imagedestroy($im);
echo "Wrote logo: $outFile\n";
