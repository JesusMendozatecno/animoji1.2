<?php

/**
 * Genera los logos de PRUEBA por color en public/images/logo/.
 *
 * El usuario colocará aquí sus logos reales (el mismo logo en varios colores),
 * manteniendo el nombre: logo-<color>.png
 *
 * Uso: php scripts/generate_logo_placeholders.php
 */

$dir = __DIR__.'/../public/images/logo';
if (! is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$colors = [
    'rojo' => [255, 38, 60],
    'azul' => [59, 130, 246],
    'aquamarine' => [45, 212, 191],
    'azul oscuro' => [99, 102, 241],
    'morado' => [168, 85, 247],
    'rosado' => [236, 72, 153],
    'vinotinto' => [185, 28, 60],
    'blanco' => [240, 240, 245],
];

const SIZE = 256;

foreach ($colors as $name => [$r, $g, $b]) {
    $im = imagecreatetruecolor(SIZE, SIZE);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefilledrectangle($im, 0, 0, SIZE, SIZE, $transparent);

    $cx = SIZE / 2;
    $cy = SIZE / 2;
    $w = 86; // semiancho del rombo

    // Sombra suave
    imagefilledpolygon($im, [
        (int) $cx, (int) ($cy - $w - 8),
        (int) ($cx + $w + 8), (int) $cy,
        (int) $cx, (int) ($cy + $w + 8),
        (int) ($cx - $w - 8), (int) $cy,
    ], imagecolorallocatealpha($im, 0, 0, 0, 90));

    // Rombo principal
    imagefilledpolygon($im, [
        (int) $cx, (int) ($cy - $w),
        (int) ($cx + $w), (int) $cy,
        (int) $cx, (int) ($cy + $w),
        (int) ($cx - $w), (int) $cy,
    ], imagecolorallocate($im, $r, $g, $b));

    // N interior (blanca)
    $white = imagecolorallocate($im, 255, 255, 255);
    $thick = 16;
    // diagonal \ de arriba-izq a abajo-der
    for ($t = 0; $t < $thick; $t++) {
        imageline($im, 100 + $t, 92, 156 + $t, 164, $white);
    }
    // diagonal / (izquierda del rombo)
    for ($t = 0; $t < $thick; $t++) {
        imageline($im, 100 + $t, 164, 130 + $t, 92, $white);
    }

    imagepng($im, $dir.'/logo-'.$name.'.png');
    imagedestroy($im);
    echo "✔ logo-{$name}.png\n";
}

// README con las anotaciones
$readme = <<<'MD'
# 🖼️ Carpeta LOGO — logos por color

Aquí viven los **logos del sistema** (el mismo diseño en varios colores).
Se usan en el login/registro (cada tema de color usa su logo) y en cualquier
zona donde se necesite el logo.

## Cómo colocar tus logos

1. Pon tu logo en esta carpeta con el nombre exacto de su color:
   `logo-rojo.png`, `logo-azul.png`, `logo-aquamarine.png`,
   `logo-azul oscuro.png`, `logo-morado.png`, `logo-rosado.png`,
   `logo-vinotinto.png`, `logo-blanco.png`.
2. Formato **PNG cuadrado con fondo transparente** (128×128 o 256×256 px).
3. Refresca con `Ctrl + F5`. Los logos actuales son de PRUEBA.

Regenerar los de prueba: `php scripts/generate_logo_placeholders.php`

> Nota: si un color no tiene `logo-<color>.png`, el sistema usa el logo rojo
> de `public/images/login/rojo/logo.png` como respaldo.
MD;

file_put_contents($dir.'/README.md', $readme);
echo "\n✔ README.md\nListo en public/images/logo/\n";
