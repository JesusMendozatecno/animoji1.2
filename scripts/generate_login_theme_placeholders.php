<?php

/**
 * Genera imágenes de PRUEBA para los temas de login que estén vacíos.
 * Lee las rutas oficiales de App\Support\Themes (LOGIN_FILES) para que
 * siempre coincidan con las que usa el sistema.
 *
 * Uso: php scripts/generate_login_theme_placeholders.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\Themes;

$files = (new ReflectionClass(Themes::class))->getConstant('LOGIN_FILES');
$colors = (new ReflectionClass(Themes::class))->getConstant('COLORS');

foreach ($files as $theme => $map) {
    $dir = __DIR__.'/../public/images/login/'.$theme;
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $bgPath = $dir.'/'.$map['bg'];
    $chPath = $dir.'/'.$map['character'];

    // ── Fondo (fondo <color>.png) ──
    if (! is_file($bgPath)) {
        $im = imagecreatetruecolor(1920, 1080);
        [$r, $g, $b] = $colors[$theme] ?? [226, 232, 240];

        for ($y = 0; $y < 1080; $y++) {
            $t = $y / 1079;
            // Blanco/gris claro arriba → gris medio abajo (tema claro)
            $red = (int) (245 - 60 * $t + $r * 0.06 * $t);
            $green = (int) (245 - 60 * $t + $g * 0.06 * $t);
            $blue = (int) (248 - 55 * $t + $b * 0.06 * $t);
            imageline($im, 0, $y, 1920, $y, imagecolorallocate($im, $red, $green, $blue));
        }

        // Bandas diagonales sutiles
        $band = imagecolorallocatealpha($im, $r, $g, $b, 110);
        for ($i = -400; $i < 2400; $i += 260) {
            for ($t = 0; $t < 90; $t++) {
                imageline($im, $i + $t, 0, $i + $t + 540, 1080, $band);
            }
        }

        // Franja de acento del tema (abajo)
        $accent = imagecolorallocate($im, $r, $g, $b);
        imagefilledrectangle($im, 0, 1032, 1920, 1080, $accent);

        imagepng($im, $bgPath);
        imagedestroy($im);
        echo "✔ {$theme}/{$map['bg']}\n";
    }

    // ── Personaje (silueta de prueba, PNG con transparencia) ──
    if (! is_file($chPath)) {
        $w = 640;
        $h = 1400;
        $im = imagecreatetruecolor($w, $h);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefilledrectangle($im, 0, 0, $w, $h, $transparent);

        $body = imagecolorallocate($im, 30, 34, 46);
        $bodySoft = imagecolorallocatealpha($im, 30, 34, 46, 60);

        // Sombra suave tras la silueta
        imagefilledellipse($im, 330, 760, 470, 900, $bodySoft);

        // Cabeza
        imagefilledellipse($im, 320, 300, 300, 340, $body);
        // Cuello
        imagefilledrectangle($im, 285, 440, 355, 540, $body);
        // Hombros / torso
        for ($y = 520; $y < 1400; $y++) {
            $half = 170 + (int) (($y - 520) * 0.18);
            imageline($im, 320 - $half, $y, 320 + $half, $y, $body);
        }

        imagepng($im, $chPath);
        imagedestroy($im);
        echo "✔ {$theme}/{$map['character']}\n";
    }
}

echo "\nListo. Temas de login con fondo+personaje.\n";
