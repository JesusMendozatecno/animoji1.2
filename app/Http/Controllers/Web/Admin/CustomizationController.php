<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\RouteOverride;
use App\Models\SystemImage;
use App\Support\Navigation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomizationController extends Controller
{
    /**
     * Zonas de imagen personalizables del sistema (lista oficial).
     * Cualquier zona guardada debe pertenecer a esta lista.
     *
     * @return array<string, array{label: string, preview: string}>
     */
    public const ZONES = [
        'admin.sidebar' => ['label' => 'Menú lateral del panel administrativo', 'preview' => '/images/admin/sidebar.jpg'],
        'usuario.sidebar' => ['label' => 'Menú lateral del panel de usuario', 'preview' => '/images/usuario/sidebar.jpg'],
        'cuenta.sidebar' => ['label' => 'Menú lateral de la cuenta de usuario', 'preview' => '/images/cuenta/sidebar.jpg'],
        'home.hero' => ['label' => 'Banner principal del index (personaje del hero)', 'preview' => '/images/index/hero-bg1.jpg'],
    ];

    /**
     * Página "Personalización de sistema" del panel administrativo.
     */
    public function index(Request $request): View
    {
        $zones = self::ZONES;

        return view('admin.personalizacion', [
            'user' => $request->user(),
            'navigationOverrides' => Navigation::map(),
            'customizing' => (bool) $request->user()->customizing,
            'zones' => $zones,
            'customizations' => $this->map(),
            // Pestaña "Direcciones": botones del sistema y destinos posibles
            'navSections' => Navigation::sections(),
            'navItems' => collect(Navigation::items())->groupBy('section')->map(fn ($g) => $g->values()->all()),
            'destinations' => Navigation::destinations(),
            'navOverrides' => RouteOverride::query()->pluck('path', 'key')->all(),
        ]);
    }

    /**
     * Direcciones: guarda (o restaura) el destino de un botón/sección del sistema.
     */
    public function updateRoute(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'path' => [
                'present',
                'nullable',
                'string',
                'max:255',
                Rule::in([...array_keys(Navigation::destinations()), Navigation::NO_DESTINATION]),
            ],
        ]);

        $valid = collect(Navigation::items())->pluck('key')->contains($key);
        abort_unless($valid, 404);

        if ($validated['path'] === null) {
            // Restaurar el destino por defecto
            RouteOverride::query()->where('key', $key)->delete();

            return back()->with('success', 'Se restauró el destino original del botón.');
        }

        RouteOverride::query()->updateOrCreate(
            ['key' => $key],
            ['path' => $validated['path'] === Navigation::NO_DESTINATION ? Navigation::NO_DESTINATION : $validated['path']],
        );

        if ($validated['path'] === Navigation::NO_DESTINATION) {
            return back()->with('success', 'El botón se guardó sin dirección.');
        }

        return back()->with('success', 'La dirección se guardó correctamente.');
    }

    /**
     * Activa o desactiva el modo personalización para el admin actual.
     * Con el modo activo, todas las zonas de imágenes muestran el botón "+ Agregar imagen".
     */
    public function toggle(Request $request): RedirectResponse
    {
        $request->user()->update(['customizing' => ! $request->user()->customizing]);

        $activado = (bool) $request->user()->customizing;

        return back()->with(
            'success',
            $activado
                ? 'Modo personalización ACTIVADO: verás el botón "+ Agregar imagen" en cada zona del sistema.'
                : 'Modo personalización desactivado.',
        );
    }

    /**
     * Lista de imágenes de la galería del sistema (public/images/**) para el selector.
     */
    public function gallery(): JsonResponse
    {
        $base = public_path('images');
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        $images = collect(File::allFiles($base))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), $extensions, true))
            ->map(function ($file) use ($base) {
                $full = str_replace('\\', '/', $file->getPathname());
                $baseNorm = str_replace('\\', '/', $base);
                $rel = str_starts_with($full, $baseNorm) ? substr($full, strlen($baseNorm) + 1) : $full;

                return [
                    'path' => '/images/'.$rel,
                    'folder' => str_contains($rel, '/') ? explode('/', $rel)[0] : 'general',
                ];
            })
            ->values();

        return response()->json([
            'images' => $images->take(300)->values(),
            'folders' => $images->pluck('folder')->unique()->sort()->values(),
        ]);
    }

    /**
     * Guarda (o elimina) la imagen personalizada de una zona.
     */
    public function update(Request $request, string $zone): RedirectResponse
    {
        abort_unless(array_key_exists($zone, self::ZONES), 404, 'Zona de personalización no válida.');

        $request->validate([
            'image_path' => ['nullable', 'string', 'max:255', 'regex:/^(images\/[A-Za-z0-9_ .\/-]+\.(jpe?g|png|webp|gif))$/i'],
            'pos_x' => ['integer', 'min:0', 'max:100'],
            'pos_y' => ['integer', 'min:0', 'max:100'],
            'scale' => ['numeric', 'min:0.5', 'max:3'],
        ]);

        // Quitar imagen de la zona → se restaura la imagen por defecto
        if (! $request->filled('image_path')) {
            SystemImage::query()->where('zone', $zone)->delete();

            return back()->with('info', 'Se restauró la imagen predeterminada de la zona.');
        }

        // Sanear la ruta: sin traversal (..), solo dentro de public/images/
        $imagePath = str_replace('\\', '/', (string) $request->input('image_path'));
        abort_if(
            str_contains($imagePath, '..') || ! str_starts_with($imagePath, 'images/'),
            422,
            'La ruta de la imagen no es válida.',
        );

        // La imagen debe existir realmente en public/
        abort_unless(is_file(public_path($imagePath)), 422, 'La imagen seleccionada no existe.');

        SystemImage::query()->updateOrCreate(
            ['zone' => $zone],
            [
                'image_path' => $imagePath,
                'pos_x' => $request->input('pos_x', 50),
                'pos_y' => $request->input('pos_y', 50),
                'scale' => $request->input('scale', 1),
            ],
        );

        return back()->with('success', 'La imagen de la zona se guardó correctamente.');
    }

    /**
     * Zonas personalizadas → se comparten con las vistas que las necesitan.
     *
     * @return array<string, array{image: string, x: int, y: int, scale: float}>
     */
    public static function map(): array
    {
        return SystemImage::query()
            ->get()
            ->mapWithKeys(fn (SystemImage $s) => [
                $s->zone => [
                    'image' => '/'.$s->image_path,
                    'x' => $s->pos_x,
                    'y' => $s->pos_y,
                    'scale' => $s->scale,
                ],
            ])
            ->all();
    }
}
