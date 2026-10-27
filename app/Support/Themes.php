<?php

namespace App\Support;

use App\Models\Character;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * Temas de color de ANIMOJI.
 *
 * - Temas de LOGIN/REGISTRO: public/images/login/<key>/ con fondo + personaje.
 *   Cada visita a /login o /register elige uno al azar (diseño distinto cada vez).
 * - Temas del INDEX: public/images/index/<key>/ con las imágenes del index
 *   en ese color. El usuario elige el suyo en Cuenta → Personalización.
 *
 * Las carpetas con archivos vacíos o inexistentes se ignoran automáticamente.
 */
class Themes
{
    /**
     * Colores de marca por tema (acento de la interfaz).
     */
    private const COLORS = [
        'rojo' => '#ff263c',
        'morado' => '#a855f7',
        'azul' => '#3b82f6',
        'aquamarine' => '#2dd4bf',
        'rosado' => '#ec4899',
        'azul oscuro' => '#6366f1',
        'vinotinto' => '#b91c3c',
        'amarillo' => '#eab308',
    ];

    /**
     * Orden en que rotan los diseños de login/registro (una visita → un tema).
     */
    public const LOGIN_ORDER = ['rojo', 'morado', 'azul', 'aquamarine', 'rosado', 'azul oscuro', 'blanco', 'vinotinto', 'amarillo'];

    /**
     * Archivos dentro de cada carpeta de login (null = no existe, usa el de rojo).
     */
    private const LOGIN_FILES = [
        'rojo' => ['bg' => 'bg.jpg', 'character' => 'character.png', 'logo' => 'logo.png'],
        'morado' => ['bg' => 'fondo morado.png', 'character' => 'morado.png', 'logo' => null],
        'azul' => ['bg' => 'fondo azul.png', 'character' => 'azul.png', 'logo' => null],
        'aquamarine' => ['bg' => 'fondo aquamarine.png', 'character' => 'aquamarine.png', 'logo' => null],
        'rosado' => ['bg' => 'fondo rosa.png', 'character' => 'rosa.png', 'logo' => null],
        'azul oscuro' => ['bg' => 'fondo azul oscuro.png', 'character' => 'azul oscuro.png', 'logo' => null],
        'blanco' => ['bg' => 'fondo blanco.png', 'character' => 'blanco.png', 'logo' => null],
        'vinotinto' => ['bg' => 'fondo vinotinto.png', 'character' => 'vinotinto.png', 'logo' => null],
        'amarillo' => ['bg' => 'fondo amarillo.png', 'character' => 'amarillo.png', 'logo' => null],
    ];

    /**
     * Lista de temas del index para el selector de personalización.
     * Fusiona los colores fijos con los personajes activos de la BD.
     *
     * @return array<string, array{label: string, hex: string, character?: string, background?: string}>
     */
    public static function indexThemes(): array
    {
        $themes = collect(self::COLORS)
            ->map(fn (string $hex, string $key) => [
                'label' => ucfirst($key),
                'hex' => $hex,
            ]);

        foreach (self::characters() as $key => $character) {
            $existing = $themes->get($key);

            $themes->put($key, [
                'label' => $character['label'],
                'hex' => $character['hex'],
                'character' => $character['character'],
                'background' => $character['background'],
                // Un personaje de BD no pisa el label fijo si ya existía
                'label_fijo' => $existing['label'] ?? null,
            ]);
        }

        return $themes->map(fn (array $theme) => array_filter(
            $theme,
            fn ($value) => $value !== null,
        ))->all();
    }

    /**
     * Personajes activos de la BD indexados por su clave de tema.
     * Solo se incluyen si tienen imagen; el fondo es opcional.
     * Si la tabla no existe (p. ej. en pruebas sin migrar) se ignora.
     *
     * @return array<string, array{label: string, hex: string, character: string, background: string|null, name: string}>
     */
    public static function characters(): array
    {
        if (! self::charactersTableReady()) {
            return [];
        }

        return Character::query()
            ->where('is_active', true)
            ->whereNotNull('image_path')
            ->orderBy('id')
            ->get()
            ->filter(fn (Character $character) => is_file(public_path(ltrim($character->image_path, '/'))))
            ->mapWithKeys(function (Character $character) {
                $key = self::characterKey($character->directory);
                $background = $character->background_path;

                return [$key => [
                    'label' => $character->theme_name,
                    'hex' => $character->color_hex,
                    'character' => '/images/personajes/'.rawurlencode($character->directory).'/'.rawurlencode(basename($character->image_path)),
                    'background' => $background && is_file(public_path(ltrim($background, '/')))
                        ? '/images/personajes/'.rawurlencode($character->directory).'/'.rawurlencode(basename($background))
                        : null,
                    'name' => $character->character_name,
                ]];
            })
            ->all();
    }

    /** Clave canónica de un personaje (carpeta segura → nombre de tema). */
    public static function characterKey(string $directory): string
    {
        return trim(mb_strtolower($directory));
    }

    /** true si la tabla de personajes existe y es consultable. */
    private static function charactersTableReady(): bool
    {
        static $ready = null;

        if ($ready === null) {
            try {
                DB::getSchemaBuilder()->hasTable('characters');
                $ready = true;
            } catch (\Throwable) {
                $ready = false;
            }
        }

        return $ready;
    }

    /**
     * Temas de login que YA tienen imágenes (carpeta con fondo y personaje),
     * incluidos los personajes activos registrados en la BD.
     *
     * @return list<string>
     */
    public static function availableLoginThemes(): array
    {
        $static = array_keys(array_filter(
            self::LOGIN_FILES,
            fn (array $files, string $key) => is_file(public_path('images/login/'.$key.'/'.$files['bg']))
                && is_file(public_path('images/login/'.$key.'/'.$files['character'])),
            ARRAY_FILTER_USE_BOTH,
        ));

        // Personajes de la BD que tengan fondo (el login usa fondo + personaje)
        $characters = array_keys(array_filter(
            self::characters(),
            fn (array $character) => $character['background'] !== null,
        ));

        // Se agregan al final de la rotación, sin duplicar claves
        return array_values(array_unique([...$static, ...$characters]));
    }

    /**
     * Elige el SIGUIENTE tema de login en el orden:
     * rojo → morado → azul → aquamarine → rosado → azul oscuro → blanco → vinotinto.
     * Cada visita a /login o /register avanza una posición (guardada en sesión).
     * Los temas sin imágenes (carpeta vacía) se saltan.
     *
     * @return array{key: string, label: string, hex: string, bg: string, character: string, logo: string}
     */
    public static function loginTheme(\Illuminate\Http\Request $request): array
    {
        $available = array_values(array_intersect(self::LOGIN_ORDER, self::availableLoginThemes()));
        if ($available === []) {
            $available = ['rojo'];
        }

        $next = ((int) $request->session()->get('login_theme_idx', -1) + 1) % count($available);
        $request->session()->put('login_theme_idx', $next);
        $key = $available[$next];

        // ── Tema dinámico: personaje registrado desde el panel admin ──
        $character = self::characters()[$key] ?? null;
        if ($character !== null) {
            $colorLogo = 'logo-'.$key.'.png';
            $logo = is_file(public_path('images/logo/'.$colorLogo))
                ? '/images/logo/'.rawurlencode($colorLogo)
                : '/images/login/rojo/'.rawurlencode('logo.png');

            return [
                'key' => $key,
                'label' => $character['label'],
                'hex' => $character['hex'],
                'bg' => $character['background'],
                'character' => $character['character'],
                'logo' => $logo,
            ];
        }

        $files = self::LOGIN_FILES[$key] ?? self::LOGIN_FILES['rojo'];

        $url = fn (string $folder, ?string $file) => $file
            ? '/images/login/'.rawurlencode($folder).'/'.rawurlencode($file)
            : '/images/login/rojo/'.rawurlencode('logo.png');

        // Logo por color: public/images/logo/logo-<color>.png si existe
        $colorLogo = 'logo-'.$key.'.png';
        $logo = is_file(public_path('images/logo/'.$colorLogo))
            ? '/images/logo/'.rawurlencode($colorLogo)
            : $url($key, $files['logo']);

        return [
            'key' => $key,
            'label' => ucfirst($key),
            'hex' => self::COLORS[$key] ?? ($key === 'blanco' ? '#e2e8f0' : '#ff263c'),
            'bg' => $url($key, $files['bg']),
            'character' => $url($key, $files['character']),
            // Logo: de /images/logo/ si existe el del color, si no el de la carpeta del tema
            'logo' => $logo,
        ];
    }

    /**
     * Convierte un hex #rrggbb a "r,g,b" para usarlo dentro de rgba().
     */
    public static function rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        return sprintf(
            '%d,%d,%d',
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }
}
