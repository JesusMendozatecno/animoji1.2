<?php

/**
 * Genera imágenes de PRUEBA para cada tema de color del INDEX.
 *
 * Crea public/images/index/<color>/ con el mismo conjunto de imágenes del
 * index (hero-bg, hero-bg1, categorías y destacados) teñidas con el color
 * del tema, más un README con las anotaciones de dónde va cada una.
 *
 * Uso: php scripts/generate_index_theme_placeholders.php
 *
 * El usuario reemplaza cada archivo manteniendo nombre y formato; si falta
 * alguno, Home.vue sigue usando la imagen por defecto de public/images/index/.
 */

$base = __DIR__.'/../public/images/index';

// color => [r, g, b] (mismo color de la carpeta de login del mismo nombre)
$themes = [
    'rojo' => [255, 38, 60],
    'azul' => [59, 130, 246],
    'aquamarine' => [45, 212, 191],
    'azul oscuro' => [99, 102, 241],
    'morado' => [168, 85, 247],
    'rosado' => [236, 72, 153],
    'vinotinto' => [185, 28, 60],
];

/** Degradado vertical oscuro → color + halo */
function makeImage(int $w, int $h, array $rgb, string $label, ?int $num = null)
{
    $im = imagecreatetruecolor($w, $h);
    [$r, $g, $b] = $rgb;

    // Degradado vertical: negro azulado abajo → color oscuro arriba
    for ($y = 0; $y < $h; $y++) {
        $t = $y / max(1, $h - 1);
        $red = (int) (10 + ($r * 0.35 * $t));
        $green = (int) (12 + ($g * 0.35 * $t));
        $blue = (int) (22 + ($b * 0.45 * $t));
        imageline($im, 0, $y, $w, $y, imagecolorallocate($im, $red, $green, $blue));
    }

    // Halo del color en el centro
    $cx = (int) ($w / 2);
    $cy = (int) ($h * 0.42);
    $rad = (int) (min($w, $h) * 0.55);
    for ($i = $rad; $i > 0; $i -= max(1, (int) ($rad / 45))) {
        $alpha = (int) (96 - ($i / $rad) * 80); // más opaco en el centro
        $col = imagecolorallocatealpha($im, $r, $g, $b, max(0, min(127, $alpha)));
        imagefilledellipse($im, $cx, $cy, $i * 2, $i * 2, $col);
    }

    // Etiqueta
    $text = $num !== null ? (string) $num : strtoupper($label);
    $font = 5;
    $tw = imagefontwidth($font) * strlen($text);
    $th = imagefontheight($font);
    $white = imagecolorallocate($im, 255, 255, 255);
    imagestring($im, $font, (int) (($w - $tw) / 2), (int) ($h - $th - 24), $text, $white);

    return $im;
}

$fileMap = [
    // archivo => [ancho, alto, etiqueta]
    'hero-bg.jpg' => [1920, 1080, 'FONDO GENERAL DEL INDEX'],
    'hero-bg1.jpg' => [1920, 900, 'BANNER SUPERIOR'],
    'anime.jpg' => [640, 400, 'TARJETA ANIME'],
    'manga.jpg' => [640, 400, 'TARJETA MANGA'],
    'musica.jpg' => [640, 400, 'TARJETA MUSICA'],
    'juegos.jpg' => [640, 400, 'TARJETA VIDEOJUEGOS'],
];

foreach ($themes as $name => $rgb) {
    $dir = $base.'/'.$name;
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    foreach ($fileMap as $file => [$w, $h, $label]) {
        imagejpeg(makeImage($w, $h, $rgb, $label), $dir.'/'.$file, 86);
    }

    for ($n = 1; $n <= 7; $n++) {
        imagejpeg(makeImage(320, 460, $rgb, 'DESTACADO', $n), $dir.'/destacado-'.$n.'.jpg', 86);
    }

    // README con las anotaciones de la carpeta
    $label = ucfirst($name);
    $hex = sprintf('#%02x%02x%02x', ...$rgb);
    $readme = <<<MD
    # 📸 Imágenes del INDEX — tema {$label}

    Carpeta de imágenes del **index** para el color **{$label}** ({$hex}).
    El usuario las elige en **Cuenta → Personalización → Imágenes del index por color**;
    al elegir "{$label}" la página de inicio usa los archivos de ESTA carpeta.
    Las imágenes actuales son de **PRUEBA**; reemplázalas manteniendo el mismo
    nombre y formato, y recarga el index con `Ctrl + F5`.

    Regenerar las de prueba: `php scripts/generate_index_theme_placeholders.php`

    | Archivo | Dónde va en el index |
    |---|---|
    | `hero-bg.jpg` | Fondo de TODA la página de inicio (detrás de todas las secciones) |
    | `hero-bg1.jpg` | Fondo del banner superior (detrás de "BIENVENIDO A ANIMOJI" y los botones) |
    | `anime.jpg` | Fondo de la tarjeta ANIME |
    | `manga.jpg` | Fondo de la tarjeta MANGA |
    | `musica.jpg` | Fondo de la tarjeta MÚSICA |
    | `juegos.jpg` | Fondo de la tarjeta VIDEOJUEGOS |
    | `destacado-1.jpg` … `destacado-7.jpg` | Portadas del carrusel "7 CAPITALES" |

    > Nota: logo y avatar siempre salen de `public/images/index/` (son de marca,
    > no cambian con el color).
    MD;

    file_put_contents($dir.'/README.md', $readme);
    echo "✔ {$name}\n";
}

echo "\nListo. Carpetas creadas en public/images/index/ (".count($themes)." colores).\n";
