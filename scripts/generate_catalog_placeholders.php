<?php

/**
 * Genera las imágenes de PRUEBA de los apartados de presentación:
 *   - public/images/anime/   (tema rojo/rosa)
 *   - public/images/manga/   (tema monocromo B/N)
 *   - public/images/musica/  (tema azul/púrpura)
 *   - public/images/juegos/  (tema turquesa gamer)
 *
 * Uso:  php scripts/generate_catalog_placeholders.php
 *
 * Para usar tus imágenes reales, reemplaza cada archivo manteniendo
 * EL MISMO NOMBRE y formato. Ver el README.md de cada carpeta.
 */

$root = __DIR__.'/../public/images';

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
    function streaks(GdImage $img, int $count, array $rgb, int $alpha = 105): void
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
}

if (! function_exists('label')) {
    function label(GdImage $img, string $text, int $x, int $y, array $rgb, int $font = 5): void
    {
        $col = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
        imagestring($img, $font, $x, $y, strtoupper($text), $col);
    }
}

// Configuración de cada apartado: [carpeta, color de acento, inicial del logo, subtítulo]
$apartados = [
    'anime' => ['base' => [15, 9, 24], 'glow' => [255, 46, 99], 'letra' => 'A', 'sub' => 'ANIME'],
    'manga' => ['base' => [9, 9, 11], 'glow' => [225, 225, 230], 'letra' => 'M', 'sub' => 'MANGA'],
    'musica' => ['base' => [8, 12, 28], 'glow' => [59, 130, 246], 'letra' => 'N', 'sub' => 'MUSICA'],
    'juegos' => ['base' => [4, 12, 14], 'glow' => [45, 212, 191], 'letra' => 'J', 'sub' => 'VIDEOJUEGOS'],
];

foreach ($apartados as $dir => $cfg) {
    $base = $root.'/'.$dir;
    if (! is_dir($base)) {
        mkdir($base, 0755, true);
    }
    $a = $cfg['glow'];
    $b = $cfg['base'];

    // ── hero.jpg (banner del apartado) ─────────────────────────────
    $img = gradVertical(1400, 420, $b, [max(0, $b[0] - 4), max(0, $b[1] - 4), max(0, $b[2] - 6)]);
    glow($img, 1050, 180, 420, $a);
    glow($img, 200, 380, 260, [(int) ($a[0] * 0.6), (int) ($a[1] * 0.6), (int) ($a[2] * 0.6)], 110);
    streaks($img, 12, $a);
    imagefilledpolygon($img, [1000, 420, 1120, 120, 1240, 420], imagecolorallocatealpha($img, $a[0], $a[1], $a[2], 95));
    label($img, 'banner de prueba - reemplazar ('.$cfg['sub'].')', 28, 28, [200, 200, 210], 3);
    imagejpeg($img, $base.'/hero.jpg', 86);
    imagedestroy($img);

    // ── cover-1..8.jpg (portadas de las tarjetas destacadas) ───────
    for ($n = 1; $n <= 8; $n++) {
        $img = gradVertical(400, 260, $b, [max(0, $b[0] - 2), max(0, $b[1] - 2), max(0, $b[2] - 4)]);
        glow($img, (int) (400 * (($n % 3) * 0.25 + 0.25)), 110, 190, $a, 105);
        imagefilledpolygon(
            $img,
            [120, 260, 200 + ($n * 8), 70, 280 + ($n * 6), 260],
            imagecolorallocatealpha($img, $a[0], $a[1], $a[2], 88),
        );
        label($img, 'portada '.$n, 168, 220, [235, 235, 240]);
        imagejpeg($img, $base.'/cover-'.$n.'.jpg', 86);
        imagedestroy($img);
    }

    // ── reco-1..5.jpg (miniaturas de "Recomendados para ti") ───────
    for ($n = 1; $n <= 5; $n++) {
        $img = gradVertical(120, 120, $b, [max(0, $b[0] - 2), max(0, $b[1] - 2), max(0, $b[2] - 4)]);
        glow($img, 60, 45, 90, $a, 100);
        imagefilledellipse($img, 60, 78, 70, 52, imagecolorallocatealpha($img, $a[0], $a[1], $a[2], 82));
        label($img, (string) $n, 56, 26, [240, 240, 245]);
        imagejpeg($img, $base.'/reco-'.$n.'.jpg', 86);
        imagedestroy($img);
    }

    // ── sidebar.jpg (decoración vertical del menú lateral) ─────────
    $img = gradVertical(360, 900, $b, [max(0, $b[0] - 3), max(0, $b[1] - 3), max(0, $b[2] - 5)]);
    glow($img, 180, 620, 300, $a, 112);
    streaks($img, 8, $a);
    imagefilledpolygon($img, [90, 900, 180, 380, 270, 900], imagecolorallocatealpha($img, $a[0], $a[1], $a[2], 92));
    imagejpeg($img, $base.'/sidebar.jpg', 84);
    imagedestroy($img);

    // ── logo.png (rombo con la inicial del apartado) ───────────────
    $logo = imagecreatetruecolor(128, 128);
    imagealphablending($logo, true);
    imagesavealpha($logo, true);
    imagefill($logo, 0, 0, imagecolorallocatealpha($logo, 0, 0, 0, 127));
    for ($grow = 6; $grow > 0; $grow--) {
        $gc = imagecolorallocatealpha($logo, $a[0], $a[1], $a[2], max(0, 127 - (6 - $grow) * 12));
        imagefilledpolygon($logo, [64, 4 - $grow, 124 + $grow, 64, 64, 124 + $grow, 4 - $grow, 64], $gc);
    }
    imagefilledpolygon($logo, [64, 12, 116, 64, 64, 116, 12, 64], imagecolorallocate($logo, max(0, $a[0] - 60), max(0, $a[1] - 30), max(0, $a[2] - 30)));
    imagefilledpolygon($logo, [64, 24, 104, 64, 64, 104, 24, 64], imagecolorallocate($logo, 12, 10, 16));
    $blanco = imagecolorallocate($logo, 255, 255, 255);
    imagesetthickness($logo, 5);
    imageline($logo, 64, 34, 46, 92, $blanco);
    imageline($logo, 64, 34, 82, 92, $blanco);
    imageline($logo, 53, 76, 75, 76, $blanco);
    imagepng($logo, $base.'/logo.png');
    imagedestroy($logo);

    // ── extras solo de música ──────────────────────────────────────
    if ($dir === 'musica') {
        $p = [139, 92, 246]; // púrpura secundario del diseño de música
        for ($n = 1; $n <= 5; $n++) {
            $rgb = $n % 2 === 0 ? $p : $a;
            $img = gradVertical(400, 240, $b, [max(0, $b[0] - 2), max(0, $b[1] - 2), max(0, $b[2] - 4)]);
            glow($img, (int) (400 * (0.2 + 0.15 * $n)), 100, 180, $rgb, 102);
            imagefilledpolygon($img, [110, 240, 190, 60, 270, 240], imagecolorallocatealpha($img, $rgb[0], $rgb[1], $rgb[2], 88));
            label($img, 'playlist '.$n, 152, 200, [235, 235, 240]);
            imagejpeg($img, $base.'/playlist-'.$n.'.jpg', 86);
            imagedestroy($img);
        }

        $img = gradVertical(120, 120, $b, [10, 14, 34]);
        glow($img, 60, 60, 80, $a, 95);
        imagefilledellipse($img, 60, 60, 74, 74, imagecolorallocatealpha($img, $a[0], $a[1], $a[2], 70));
        imagepng($img, $base.'/player-cover.png');
        imagedestroy($img);
    }

    // ── extras solo de videojuegos ─────────────────────────────────
    if ($dir === 'juegos') {
        // Géneros (chip en el hero): Acción, Aventura, RPG, Deportes, Indie
        for ($n = 1; $n <= 5; $n++) {
            $img = gradVertical(96, 96, $b, [6, 18, 20]);
            glow($img, 48, 48, 58, $a, 95);
            imagefilledpolygon($img, [30, 68, 48, 26, 66, 68], imagecolorallocatealpha($img, $a[0], $a[1], $a[2], 80));
            imagepng($img, $base.'/genre-'.$n.'.png');
            imagedestroy($img);
        }

        // Portada del jugador (zona ESTADO ACTUAL)
        $img = gradVertical(380, 460, $b, [6, 18, 20]);
        glow($img, 190, 160, 170, $a, 100);
        imagefilledellipse($img, 190, 170, 150, 150, imagecolorallocatealpha($img, $a[0], $a[1], $a[2], 62));
        imagefilledpolygon($img, [90, 460, 190, 240, 290, 460], imagecolorallocatealpha($img, $a[0], $a[1], $a[2], 78));
        label($img, 'portada jugador', 120, 410, [235, 235, 240]);
        imagejpeg($img, $base.'/status-cover.jpg', 86);
        imagedestroy($img);
    }

    echo "✔ imágenes del apartado {$dir} generadas".PHP_EOL;
}

echo PHP_EOL.'Listo. Reemplaza cada archivo manteniendo el mismo nombre para usar tus imágenes reales.'.PHP_EOL;
