<?php

/**
 * Genera las imágenes de PRUEBA de la página de inicio (apartado index).
 *
 * Uso:  php scripts/generate_home_placeholders.php
 *
 * Todas se guardan en public/images/index/. Para usar tus imágenes reales,
 * reemplaza cada archivo manteniendo EL MISMO NOMBRE (y formato .jpg/.png).
 * Ver public/images/index/README.md con la lista de cada imagen y dónde va.
 */

$base = __DIR__.'/../public/images/index';
if (! is_dir($base)) {
    mkdir($base, 0755, true);
}

/** Gradiente vertical de dos colores. */
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

/** Glow radial (concentrico, con alfa). */
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

/** Rayas diagonales decorativas (estilo sci-fi). */
function streaks(GdImage $img, int $count, array $rgb, int $alpha = 100): void
{
    imagealphablending($img, true);
    imagesetthickness($img, 2);
    $w = imagesx($img);
    $h = imagesy($img);
    for ($i = 0; $i < $count; $i++) {
        $x = random_int(-$h, $w);
        $col = imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], random_int($alpha, 118));
        imageline($img, $x, $h, $x + (int) ($h * 0.6), 0, $col);
    }
}

/** Texto pequeño con la fuente bitmap nativa (marcadores de posición). */
function label(GdImage $img, string $text, int $x, int $y, array $rgb, int $font = 5): void
{
    $col = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
    imagestring($img, $font, $x, $y, strtoupper($text), $col);
}

/** Fondo base oscuro con glow y rayas, reutilizable. */
function darkCanvas(int $w, int $h, array $glowRgb, ?array $glowAt = null): GdImage
{
    $img = gradVertical($w, $h, [8, 8, 14], [16, 4, 8]);
    $g = $glowAt ?? [(int) ($w * 0.75), (int) ($h * 0.35), (int) (min($w, $h) * 0.8)];
    glow($img, $g[0], $g[1], $g[2], $glowRgb);
    streaks($img, 14, $glowRgb);

    return $img;
}

// ---------------------------------------------------------------------------
// 1. HERO (fondo del banner principal)  →  images/index/hero-bg.jpg
// ---------------------------------------------------------------------------
$hero = darkCanvas(1920, 900, [255, 30, 55], [(int) (1920 * 0.78), (int) (900 * 0.30), 700]);
glow($hero, 1450, 260, 240, [255, 60, 70], 90);          // "luna" roja
glow($hero, 300, 700, 320, [120, 10, 25], 110);          // brillo inferior izquierdo
imagejpeg($hero, $base.'/hero-bg.jpg', 86);
imagedestroy($hero);

// ---------------------------------------------------------------------------
// 2. TARJETAS DE CATEGORÍA  →  images/index/{anime,manga,musica,juegos}.jpg
// ---------------------------------------------------------------------------
$categorias = [
    'anime'   => [[34, 211, 238], 'Anime'],
    'manga'   => [[255, 40, 60], 'Manga'],
    'musica'  => [[168, 85, 247], 'Musica'],
    'juegos'  => [[45, 212, 191], 'Videojuegos'],
];
foreach ($categorias as $file => [$rgb, $titulo]) {
    $img = darkCanvas(640, 400, $rgb);
    // siluetas geométricas de relleno (sustituyen a los personajes de prueba)
    imagefilledpolygon($img, [430, 400, 520, 140, 610, 400], imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 90));
    imagefilledpolygon($img, [500, 400, 570, 210, 640, 400], imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 70));
    label($img, $titulo, 24, 24, [255, 255, 255]);
    label($img, 'imagen de prueba - reemplazar', 24, 372, [150, 150, 160], 3);
    imagejpeg($img, $base.'/'.$file.'.jpg', 86);
    imagedestroy($img);
}

// ---------------------------------------------------------------------------
// 3. TARJETAS DEL CARRUSEL "7 CAPITALES"  →  images/index/destacado-N.jpg
// ---------------------------------------------------------------------------
$destacados = [
    [255, 45, 60], [56, 189, 248], [239, 68, 68], [34, 211, 238],
    [168, 85, 247], [232, 121, 249], [251, 146, 60],
];
foreach ($destacados as $i => $rgb) {
    $n = $i + 1;
    $img = darkCanvas(320, 460, $rgb, [(int) (320 * 0.5), (int) (460 * 0.35), 260]);
    imagefilledpolygon($img, [80, 460, 160, 120, 240, 460], imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 85));
    label($img, (string) $n, 152, 200, [255, 255, 255]);
    label($img, 'destacado '.$n, 96, 430, [170, 170, 180], 3);
    imagejpeg($img, $base.'/destacado-'.$n.'.jpg', 86);
    imagedestroy($img);
}

// ---------------------------------------------------------------------------
// 4. LOGO (rombo rojo con A)  →  images/index/logo.png
// ---------------------------------------------------------------------------
$logo = imagecreatetruecolor(128, 128);
imagealphablending($logo, true);
imagesavealpha($logo, true);
$transparent = imagecolorallocatealpha($logo, 0, 0, 0, 127);
imagefill($logo, 0, 0, $transparent);
// rombo exterior con glow
for ($grow = 6; $grow > 0; $grow--) {
    $a = 127 - (int) ((6 - $grow) * 12);
    $gc = imagecolorallocatealpha($logo, 255, 40, 60, max(0, $a));
    imagefilledpolygon($logo, [64, 4 - $grow, 124 + $grow, 64, 64, 124 + $grow, 4 - $grow, 64], $gc);
}
imagefilledpolygon($logo, [64, 12, 116, 64, 64, 116, 12, 64], imagecolorallocate($logo, 190, 12, 30));
imagefilledpolygon($logo, [64, 24, 104, 64, 64, 104, 24, 64], imagecolorallocate($logo, 12, 10, 16));
// "A" blanca angular
$blanco = imagecolorallocate($logo, 255, 255, 255);
imagesetthickness($logo, 5);
imageline($logo, 64, 34, 46, 92, $blanco);
imageline($logo, 64, 34, 82, 92, $blanco);
imageline($logo, 53, 76, 75, 76, $blanco);
imagepng($logo, $base.'/logo.png');
imagedestroy($logo);

// ---------------------------------------------------------------------------
// 5. AVATAR de usuario de prueba  →  images/index/avatar.png
// ---------------------------------------------------------------------------
$avatar = imagecreatetruecolor(96, 96);
imagealphablending($avatar, true);
imagesavealpha($avatar, true);
imagefill($avatar, 0, 0, imagecolorallocatealpha($avatar, 0, 0, 0, 127));
glow($avatar, 48, 48, 46, [255, 40, 60], 90);
imagefilledellipse($avatar, 48, 48, 88, 88, imagecolorallocate($avatar, 24, 24, 32));
$gris = imagecolorallocate($avatar, 120, 124, 138);
imagefilledellipse($avatar, 48, 38, 30, 30, $gris);       // cabeza
imagefilledellipse($avatar, 48, 88, 56, 44, $gris);       // hombros
imagepng($avatar, $base.'/avatar.png');
imagedestroy($avatar);

echo "Imágenes generadas en public/images/index/:".PHP_EOL;
foreach (scandir($base) as $f) {
    if ($f !== '.' && $f !== '..' && $f !== 'README.md') {
        echo '  - '.$f.PHP_EOL;
    }
}
