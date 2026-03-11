<?php
// Simple icon generator: requires GD and a square PNG `app/views/img/logo.png`.
$src = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo.png';
$outDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'icons';
$sizes = [72,96,128,144,152,192,384,512];

if (!file_exists($src)){
    echo "Logo source not found: $src\n";
    exit(1);
}
if (!is_dir($outDir)) mkdir($outDir, 0755, true);

$srcImg = imagecreatefrompng($src);
if (!$srcImg){ echo "No se puede abrir logo.png con GD\n"; exit(1); }
$srcW = imagesx($srcImg);
$srcH = imagesy($srcImg);

foreach($sizes as $size){
    $dst = imagecreatetruecolor($size, $size);
    // preserve transparency
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $srcImg, 0,0,0,0, $size, $size, $srcW, $srcH);
    $outFile = $outDir . DIRECTORY_SEPARATOR . "icon-{$size}.png";
    imagepng($dst, $outFile, 9);
    imagedestroy($dst);
    echo "Written: $outFile\n";
}
imagedestroy($srcImg);
echo "Done. Icons created in: $outDir\n";
