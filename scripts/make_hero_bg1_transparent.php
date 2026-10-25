<?php

/**
 * Convierte el "tablero de transparencia" falsO de hero-bg1 en transparencia REAL.
 *
 * Muchos editores exportan el PNG con el damero gris/blanco ya dibujado.
 * Este script rellena desde los bordes: todo píxel claro y neutro conectado
 * con el borde (el fondo) se vuelve transparente; el personaje (oscuro o
 * con color) se conserva. El original queda respaldado en
 * storage/app/hero-bg1-original.png.
 *
 * Uso: php scripts/make_hero_bg1_transparent.php
 */

$file = __DIR__.'/../public/images/index/hero-bg1.jpg';
$backup = __DIR__.'/../storage/app/hero-bg1-original.png';

// El "jpg" puede ser en realidad un PNG
$im = imagecreatefrompng($file);
if (! $im) {
    fwrite(STDERR, "No se pudo leer hero-bg1.jpg como PNG.\n");
    exit(1);
}

if (! is_file($backup)) {
    copy($file, $backup);
    echo "✔ Respaldo del original: storage/app/hero-bg1-original.png\n";
}

$w = imagesx($im);
$h = imagesy($im);

/** ¿Fondo falso (claro y neutro tipo damero/blanco)? */
$bgish = function (int $rgb): bool {
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;
    $min = min($r, $g, $b);
    $max = max($r, $g, $b);

    return $min >= 190 && ($max - $min) <= 25;
};

$transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
// Clave: sin blending, el píxel transparente se escribe de verdad
// (con blending, alpha 127 = invisible y el píxel no cambia → bucle infinito)
imagealphablending($im, false);
// No hace falta mapa de visitados: al volver un píxel transparente su RGB
// es (0,0,0) → ya no pasa el filtro de fondo y el recorrido se detiene ahí.
$stack = [];

// Semillas: bordes que sean fondo (coordenadas codificadas en un entero)
for ($x = 0; $x < $w; $x++) {
    $stack[] = ($x << 16);
    $stack[] = (($x << 16) | ($h - 1));
}
for ($y = 0; $y < $h; $y++) {
    $stack[] = $y;
    $stack[] = (($w - 1) << 16) | $y;
}

$removed = 0;
while ($stack !== []) {
    $v = array_pop($stack);
    $x = $v >> 16;
    $y = $v & 0xFFFF;
    if ($x < 0 || $y < 0 || $x >= $w || $y >= $h) {
        continue;
    }
    $rgb = imagecolorat($im, $x, $y);
    if (! $bgish($rgb)) {
        continue;
    }
    imagesetpixel($im, $x, $y, $transparent);
    $removed++;

    $stack[] = (($x + 1) << 16) | $y;
    $stack[] = (($x - 1) << 16) | $y;
    $stack[] = ($x << 16) | ($y + 1);
    $stack[] = ($x << 16) | ($y - 1);
}

// Liberar la pila antes de codificar (PHP conserva el buffer del array)
$stack = [];

gc_collect_cycles();
imagesavealpha($im, true);
imagepng($im, $file);
imagedestroy($im);

$pct = round($removed / ($w * $h) * 100, 1);
echo "✔ hero-bg1.jpg ahora es PNG transparente ({$removed} píxeles = {$pct}% del fondo).\n";
echo "  Para deshacer: copia storage/app/hero-bg1-original.png sobre el archivo.\n";
