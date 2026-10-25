<?php

/**
 * Genera las imágenes de PRUEBA de las secciones autenticadas:
 *   - public/images/admin/     Panel administrativo (azul/púrpura)
 *   - public/images/galeria/   Galería de imágenes
 *   - public/images/usuario/   Presentación + Mi Colección (púrpura)
 *   - public/images/cuenta/    Panel de cuenta (rojo)
 *   - public/images/carga/     Pantalla de carga (rojo)
 *
 * Uso:  php scripts/generate_panel_placeholders.php
 * Reemplaza cada archivo manteniendo EL MISMO NOMBRE. Ver README.md de cada carpeta.
 */

$root = __DIR__.'/../public/images';

if (! function_exists('gradVertical')) {
    function gradVertical(int $w, int $h, array $top, array $bot): GdImage
    {
        $img = imagecreatetruecolor($w, $h);
        for ($y = 0; $y < $h; $y++) {
            $t = $y / max(1, $h - 1);
            $col = imagecolorallocate($img, (int) ($top[0] + ($bot[0] - $top[0]) * $t), (int) ($top[1] + ($bot[1] - $top[1]) * $t), (int) ($top[2] + ($bot[2] - $top[2]) * $t));
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
            $col = imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], (int) max(0, 127 - ($maxAlpha * $t)));
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
            $col = imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], random_int(105, 118));
            imageline($img, random_int(-$h, $w), $h, random_int(0, $w) + (int) ($h * 0.4), 0, $col);
        }
    }
}

/** Fondo base oscuro con glow + rayas. */
function canvas(int $w, int $h, array $base, array $rgb, ?array $at = null): GdImage
{
    $img = gradVertical($w, $h, $base, [max(0, $base[0] - 4), max(0, $base[1] - 4), max(0, $base[2] - 6)]);
    $g = $at ?? [(int) ($w * 0.75), (int) ($h * 0.35), (int) (min($w, $h) * 0.8)];
    glow($img, $g[0], $g[1], $g[2], $rgb);
    streaks($img, 12, $rgb);

    return $img;
}

function save(string $dir, string $name, GdImage $img): void
{
    $path = $dir.'/'.$name;
    str_ends_with($name, '.png') ? imagepng($img, $path) : imagejpeg($img, $path, 86);
    imagedestroy($img);
}

/**
 * @param array<string, array{base: array, glow: array}> $secciones
 */
function genSeccion(string $root, string $dir, array $cfg, callable $extra): void
{
    $path = $root.'/'.$dir;
    if (! is_dir($path)) {
        mkdir($path, 0755, true);
    }
    $extra($path, $cfg['base'], $cfg['glow']);
    echo "✔ {$dir}: ".count(array_diff(scandir($path), ['.', '..', 'README.md'])).' archivos'.PHP_EOL;
}

// ══════════ ADMIN (azul/púrpura) ══════════
genSeccion($root, 'admin', ['base' => [10, 12, 30], 'glow' => [99, 102, 241]], function (string $p, array $b, array $a) {
    $save = fn (string $n, GdImage $i) => save($p, $n, $i);
    $save('banner.jpg', canvas(1400, 420, $b, $a));
    foreach (['anime' => [255, 46, 99], 'manga' => [255, 255, 255], 'musica' => [56, 189, 248]] as $n => $rgb) {
        $img = canvas(420, 250, $b, $rgb);
        imagefilledpolygon($img, [120, 250, 210, 70, 300, 250], imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 88));
        $save('card-'.$n.'.jpg', $img);
    }
    $save('sidebar.jpg', canvas(360, 900, $b, $a));
});

// ══════════ GALERÍA ══════════
genSeccion($root, 'galeria', ['base' => [10, 8, 18], 'glow' => [255, 46, 99]], function (string $p, array $b, array $a) {
    $save = fn (string $n, GdImage $i) => save($p, $n, $i);
    $save('banner.jpg', canvas(1600, 360, $b, $a));
    $paletas = [[255, 46, 99], [56, 189, 248], [168, 85, 247], [34, 197, 94], [251, 146, 60], [236, 72, 153], [14, 165, 233], [250, 204, 21]];
    for ($n = 1; $n <= 8; $n++) {
        $rgb = $paletas[$n - 1];
        $img = canvas(480, 300, $b, $rgb, [(int) (480 * ($n % 3) * 0.3), 110, 190]);
        imagefilledpolygon($img, [130, 300, 240, 80, 350, 300], imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 85));
        $save('img-'.$n.'.jpg', $img);
    }
});

// ══════════ USUARIO (presentación + mi colección) ══════════
genSeccion($root, 'usuario', ['base' => [12, 10, 32], 'glow' => [139, 92, 246]], function (string $p, array $b, array $a) {
    $save = fn (string $n, GdImage $i) => save($p, $n, $i);
    // tiles de categoría de Presentación
    foreach (['anime' => [236, 72, 153], 'musica' => [139, 92, 246], 'manga' => [59, 130, 246], 'juegos' => [34, 197, 94]] as $n => $rgb) {
        $img = canvas(300, 170, $b, $rgb);
        imagefilledellipse($img, 230, 85, 130, 120, imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 70));
        $save('tile-'.$n.'.jpg', $img);
    }
    // portadas verticales del carrusel
    for ($n = 1; $n <= 5; $n++) {
        $rgb = [[236, 72, 153], [59, 130, 246], [14, 165, 233], [255, 46, 99], [168, 85, 247]][$n - 1];
        $img = canvas(360, 500, $b, $rgb, [180, 170, 220]);
        imagefilledpolygon($img, [80, 500, 180, 140, 280, 500], imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 85));
        $save('cover-'.$n.'.jpg', $img);
    }
    // mi colección
    $save('hero-coleccion.jpg', canvas(1400, 360, $b, $a));
    foreach ([['anime', [255, 46, 99]], ['manga', [168, 85, 247]], ['musica', [59, 130, 246]], ['juegos', [34, 197, 94]]] as [$n, $rgb]) {
        $img = canvas(420, 300, $b, $rgb);
        imagefilledellipse($img, 210, 140, 180, 160, imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 72));
        $save('cat-'.$n.'.jpg', $img);
    }
    for ($n = 1; $n <= 4; $n++) {
        $rgb = [[255, 46, 99], [168, 85, 247], [59, 130, 246], [34, 197, 94]][$n - 1];
        $save('recent-'.$n.'.jpg', canvas(400, 240, $b, $rgb));
    }
    $save('sidebar.jpg', canvas(360, 900, $b, $a));
    $save('destacado.jpg', canvas(400, 260, $b, $a)); // tarjeta lateral "Grandes historias"
});

// ══════════ CUENTA (rojo) ══════════
genSeccion($root, 'cuenta', ['base' => [14, 6, 10], 'glow' => [255, 46, 99]], function (string $p, array $b, array $a) {
    $save = fn (string $n, GdImage $i) => save($p, $n, $i);
    $save('banner.jpg', canvas(1400, 300, $b, $a));
    $save('sidebar.jpg', canvas(360, 900, $b, $a));
    $save('promo.jpg', canvas(640, 420, $b, $a));
    $save('avatar.png', (function () use ($a) {
        $img = imagecreatetruecolor(128, 128);
        imagealphablending($img, true);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));
        glow($img, 64, 64, 60, $a, 95);
        imagefilledellipse($img, 64, 64, 116, 116, imagecolorallocate($img, 24, 16, 22));
        $gris = imagecolorallocate($img, 150, 120, 130);
        imagefilledellipse($img, 64, 52, 40, 40, $gris);
        imagefilledellipse($img, 64, 118, 74, 60, $gris);

        return $img;
    })());
});

// ══════════ CARGA (pantalla splash) ══════════
genSeccion($root, 'carga', ['base' => [8, 2, 5], 'glow' => [200, 20, 40]], function (string $p, array $b, array $a) {
    $img = canvas(1920, 1080, $b, $a, [960, 480, 700]);
    glow($img, 300, 850, 320, [150, 12, 30]);
    // siluetas laterales
    imagealphablending($img, true);
    imagefilledpolygon($img, [0, 0, 340, 0, 180, 1080, 0, 1080], imagecolorallocatealpha($img, 60, 6, 14, 60));
    imagefilledpolygon($img, [1920, 0, 1580, 0, 1740, 1080, 1920, 1080], imagecolorallocatealpha($img, 60, 6, 14, 60));
    save($p, 'bg.jpg', $img);
});

echo PHP_EOL.'Listo. Reemplaza cada archivo manteniendo el mismo nombre para usar tus imágenes reales.'.PHP_EOL;
