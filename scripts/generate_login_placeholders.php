<?php

/**
 * Genera las imágenes de PRUEBA del apartado de LOGIN/REGISTRO (tema rojo).
 *
 * Uso:  php scripts/generate_login_placeholders.php
 *
 * Se guardan en public/images/login/rojo/. Para usar tus imágenes reales,
 * reemplaza cada archivo manteniendo EL MISMO NOMBRE. Ver README.md de la carpeta.
 */

$base = __DIR__.'/../public/images/login/rojo';
if (! is_dir($base)) {
    mkdir($base, 0755, true);
}

if (! function_exists('gradVertical')) {
    function gradVertical(int $w, int $h, array $top, array $bot): GdImage
    {
        $img = imagecreatetruecolor($w, $h);
        for ($y = 0; $y < $h; $y++) {
            $t = $y / max(1, $h - 1);
            $col = imagecolorallocate(
                $img,
                (int) ($top[0] + ($bot[0] - $top[0]) * $t),
                (int) ($top[1] + ($bot[1] - $top[1]) * $t),
                (int) ($top[2] + ($bot[2] - $top[2]) * $t),
            );
            imageline($img, 0, $y, $w, $y, $col);
        }

        return $img;
    }
}

if (! function_exists('glow')) {
    function glow(GdImage $img, int $cx, int $cy, int $radius, array $rgb, int $maxAlpha = 118): void
    {
        imagealphablending($img, true);
        for ($r = $radius; $r > 0; $r -= 6) {
            $t = 1 - ($r / $radius);
            $alpha = (int) max(0, 127 - ($maxAlpha * $t));
            $col = imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], $alpha);
            imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $col);
        }
    }
}

if (! function_exists('streaks')) {
    function streaks(GdImage $img, int $count, array $rgb): void
    {
        imagealphablending($img, true);
        imagesetthickness($img, 2);
        $w = imagesx($img);
        $h = imagesy($img);
        for ($i = 0; $i < $count; $i++) {
            $x = random_int(-$h, $w);
            $col = imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], random_int(105, 118));
            imageline($img, $x, $h, $x + (int) ($h * 0.6), 0, $col);
        }
    }
}

// ── bg.jpg: fondo de la pantalla (ciudad neón roja) ───────────────────
$bg = gradVertical(1920, 1080, [14, 4, 8], [6, 2, 5]);
glow($bg, 1350, 300, 620, [200, 20, 40]);       // luna roja tras el personaje
glow($bg, 250, 850, 380, [120, 8, 22]);         // brillo inferior izquierdo
glow($bg, 1750, 900, 300, [150, 12, 30]);       // brillo inferior derecho
streaks($bg, 16, [255, 40, 60]);
// siluetas de "edificios" (skyline de relleno)
imagealphablending($bg, true);
for ($i = 0; $i < 26; $i++) {
    $bw = random_int(60, 130);
    $bh = random_int(180, 520);
    $bx = random_int(0, 1920 - $bw);
    $col = imagecolorallocatealpha($bg, 20, 6, 10, 40);
    imagefilledrectangle($bg, $bx, 1080 - $bh, $bx + $bw, 1080, $col);
}
imagejpeg($bg, $base.'/bg.jpg', 86);
imagedestroy($bg);

// ── character.png: personaje de prueba (silueta con auriculares) ──────
$ch = imagecreatetruecolor(700, 1000);
imagealphablending($ch, true);
imagesavealpha($ch, true);
imagefill($ch, 0, 0, imagecolorallocatealpha($ch, 0, 0, 0, 127));
$body = imagecolorallocatealpha($ch, 30, 8, 12, 0);
$red = imagecolorallocate($ch, 255, 40, 60);
// cuerpo (silueta estilizada)
imagefilledellipse($ch, 350, 250, 240, 280, $body);                       // cabeza
imagefilledellipse($ch, 230, 220, 90, 120, $body);                        // auricular izq
imagefilledellipse($ch, 470, 220, 90, 120, $body);                        // auricular der
imagefilledellipse($ch, 230, 220, 54, 70, $red);                          // luz auricular izq
imagefilledellipse($ch, 470, 220, 54, 70, $red);                          // luz auricular der
imagefilledpolygon($ch, [180, 1000, 250, 430, 450, 430, 520, 1000], $body); // torso
imagefilledpolygon($ch, [300, 120, 350, 40, 400, 120, 350, 160], $body);  // flequillo
glow($ch, 350, 500, 300, [255, 30, 50], 90);
imagepng($ch, $base.'/character.png');
imagedestroy($ch);

// ── logo.png: rombo rojo con ojo estilizado ───────────────────────────
$logo = imagecreatetruecolor(128, 128);
imagealphablending($logo, true);
imagesavealpha($logo, true);
imagefill($logo, 0, 0, imagecolorallocatealpha($logo, 0, 0, 0, 127));
for ($grow = 6; $grow > 0; $grow--) {
    $gc = imagecolorallocatealpha($logo, 255, 40, 60, max(0, 127 - (6 - $grow) * 12));
    imagefilledpolygon($logo, [64, 4 - $grow, 124 + $grow, 64, 64, 124 + $grow, 4 - $grow, 64], $gc);
}
imagefilledpolygon($logo, [64, 12, 116, 64, 64, 116, 12, 64], imagecolorallocate($logo, 190, 12, 30));
imagefilledellipse($logo, 64, 64, 56, 28, imagecolorallocate($logo, 12, 10, 16)); // "ojo"
imagefilledellipse($logo, 64, 64, 22, 22, imagecolorallocate($logo, 255, 255, 255));
imagepng($logo, $base.'/logo.png');
imagedestroy($logo);

echo "Imágenes de login (tema rojo) generadas en public/images/login/rojo/".PHP_EOL;
foreach (scandir($base) as $f) {
    if ($f !== '.' && $f !== '..' && $f !== 'README.md') {
        echo '  - '.$f.PHP_EOL;
    }
}
