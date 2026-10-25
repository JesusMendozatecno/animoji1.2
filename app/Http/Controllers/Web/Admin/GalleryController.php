<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Models\SystemImage;
use App\Support\Navigation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * 📷 GALERÍA DE IMÁGENES DEL SISTEMA
 *
 * Trabaja SIEMPRE sobre los archivos reales de public/images/galeria/.
 * - Orden alfabético ascendente ignorando mayúsculas/minúsculas (server-side).
 * - Paginación real de 10 elementos por página (no es una división visual).
 * - Subida con validación real (extensión, MIME, tamaño, dimensiones, nombre seguro).
 * - Borrado con comprobación de dependencias (zonas de personalización, portadas).
 */
class GalleryController extends Controller
{
    /** Máximo de elementos por página (requisito: 10). */
    public const PER_PAGE = 10;

    /** Extensiones permitidas. */
    public const ALLOWED = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /** Tamaño máximo: 5 MB. */
    public const MAX_KB = 5120;

    /**
     * Listado paginado de las imágenes reales de la galería.
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $items = $this->files($query);

        $paginator = new LengthAwarePaginator(
            array_slice($items, ($page - 1) * self::PER_PAGE, self::PER_PAGE),
            count($items),
            self::PER_PAGE,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => array_filter(['q' => $query ?: null, 'page' => null]),
            ],
        );
        $paginator->withQueryString();

        return view('admin.galeria', [
            'user' => $request->user(),
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $request->user()?->customizing,
            'items' => $paginator,
            'filters' => ['q' => $query],
        ]);
    }

    /**
     * Sube una imagen nueva a la galería.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'extensions:jpg,jpeg,png,webp,gif',
                'max:'.self::MAX_KB,
                'dimensions:max_width=8000,max_height=8000',
            ],
            'name' => ['required', 'string', 'min:2', 'max:80'],
        ], [
            'image.required' => 'Selecciona una imagen para subir.',
            'image.file' => 'El archivo seleccionado no es válido.',
            'image.image' => 'La imagen supera el tamaño máximo permitido o no es una imagen válida.',
            'image.mimes' => 'La imagen supera el formato permitido. Usa JPG, PNG, WebP o GIF.',
            'image.extensions' => 'La extensión del archivo debe ser JPG, PNG, WebP o GIF.',
            'image.max' => 'La imagen supera el tamaño máximo permitido (5 MB).',
            'image.dimensions' => 'Las dimensiones de la imagen son demasiado grandes.',
            'name.required' => 'El nombre de la imagen es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no puede superar los 80 caracteres.',
        ]);

        $file = $request->file('image');

        // MIME real del contenido (no confiar solo en la extensión)
        $mime = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (! in_array($mime, $allowedMimes, true)) {
            return back()->withErrors([
                'image' => 'El archivo no es una imagen válida (el contenido no coincide con un formato permitido).',
            ]);
        }

        // Nombre seguro: solo letras (con acentos), números y espacios; sin traversal
        $safe = trim(preg_replace('/[^\p{L}\p{N} \-]+/u', '', $validated['name']) ?? '');
        $safe = trim(preg_replace('/\s+/u', ' ', $safe) ?? '');
        if ($safe === '' || $safe === '.' || $safe === '..') {
            $safe = 'Imagen '.now()->format('Ymd-His');
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = $safe.'.'.$ext;

        // Evitar sobrescribir: si el nombre existe, se añade un sufijo incremental
        $dir = public_path('images/galeria');
        $final = $filename;
        $n = 1;
        while (is_file($dir.DIRECTORY_SEPARATOR.$final)) {
            $final = $safe.'-'.$n.'.'.$ext;
            $n++;
        }

        // Mover con nombre controlado (el nombre temporal del upload no se conserva)
        $file->move($dir, $final);

        return redirect()
            ->route('admin.galeria', array_filter(['q' => $request->query('q')]))
            ->with('success', 'La imagen se guardó correctamente en la galería.');
    }

    /**
     * Cambia el nombre del archivo y conserva sus referencias en el sistema.
     */
    public function rename(Request $request, string $file): JsonResponse
    {
        $base = basename(str_replace('\\', '/', $file));
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));

        abort_if(
            $base !== $file
            || str_contains($base, '..')
            || ! in_array($ext, self::ALLOWED, true),
            422,
            'Nombre de archivo no válido.',
        );

        $currentPath = public_path('images/galeria/'.$base);
        abort_unless(is_file($currentPath), 404, 'La imagen ya no existe.');

        $request->merge([
            'name' => trim(preg_replace('/\s+/u', ' ', (string) $request->input('name')) ?? ''),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[\pL\pN][\pL\pN \-]*$/u'],
        ], [
            'name.required' => 'El nombre de la imagen es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no puede superar los 80 caracteres.',
            'name.regex' => 'Usa solo letras, números, espacios o guiones en el nombre.',
        ]);

        $newFilename = $validated['name'].'.'.$ext;
        $newPath = public_path('images/galeria/'.$newFilename);

        if (strcasecmp($base, $newFilename) === 0) {
            return response()->json([
                'name' => pathinfo($base, PATHINFO_FILENAME),
                'filename' => $base,
                'image' => '/images/galeria/'.rawurlencode($base),
                'delete_url' => route('admin.galeria.destroy', ['file' => $base]),
                'rename_url' => route('admin.galeria.rename', ['file' => $base]),
                'list_url' => route('admin.galeria', ['page' => $this->pageForGalleryFilename($base)]),
            ]);
        }

        if (is_file($newPath)) {
            return response()->json([
                'message' => 'Ya existe una imagen con ese nombre.',
            ], 422);
        }

        if (! rename($currentPath, $newPath)) {
            return response()->json([
                'message' => 'No se pudo cambiar el nombre de la imagen. Inténtalo nuevamente.',
            ], 500);
        }

        $currentRelativePath = 'images/galeria/'.$base;
        $newRelativePath = 'images/galeria/'.$newFilename;

        try {
            DB::transaction(function () use ($currentRelativePath, $newRelativePath): void {
                SystemImage::query()
                    ->where('image_path', $currentRelativePath)
                    ->update(['image_path' => $newRelativePath]);
                SystemImage::query()
                    ->where('image_path', '/'.$currentRelativePath)
                    ->update(['image_path' => '/'.$newRelativePath]);

                foreach ([Anime::class, Manga::class, Game::class, MusicTrack::class] as $model) {
                    $model::query()
                        ->where('cover', $currentRelativePath)
                        ->update(['cover' => $newRelativePath]);
                    $model::query()
                        ->where('cover', '/'.$currentRelativePath)
                        ->update(['cover' => '/'.$newRelativePath]);
                }
            });
        } catch (Throwable $exception) {
            if (! rename($newPath, $currentPath)) {
                report(new \RuntimeException(
                    "Gallery rename rollback failed for [{$newFilename}].",
                    previous: $exception,
                ));
            }

            throw $exception;
        }

        return response()->json([
            'name' => $validated['name'],
            'filename' => $newFilename,
            'image' => '/images/galeria/'.rawurlencode($newFilename),
            'delete_url' => route('admin.galeria.destroy', ['file' => $newFilename]),
            'rename_url' => route('admin.galeria.rename', ['file' => $newFilename]),
            'list_url' => route('admin.galeria', ['page' => $this->pageForGalleryFilename($newFilename)]),
        ]);
    }

    /**
     * Elimina una imagen de la galería (comprobando dependencias antes).
     */
    public function destroy(Request $request, string $file): RedirectResponse
    {
        // Validar el nombre: sin traversal, extensión permitida, dentro de galeria/
        $file = str_replace('\\', '/', $file);
        $base = basename($file);
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));

        abort_if(
            $base !== $file
            || str_contains($base, '..')
            || ! in_array($ext, self::ALLOWED, true),
            422,
            'Nombre de archivo no válido.',
        );

        $full = public_path('images/galeria/'.$base);
        abort_unless(is_file($full), 404, 'La imagen ya no existe.');

        $relPath = 'images/galeria/'.$base;

        // 1) ¿La usa alguna zona de personalización?
        $enZona = SystemImage::query()->where('image_path', $relPath)->count();

        // 2) ¿Es portada de algún registro del catálogo?
        $portada = $this->usedAsCover($relPath) || $this->usedAsCover('/'.$relPath);

        if ($enZona > 0 || $portada) {
            $motivos = [];
            if ($enZona > 0) {
                $motivos[] = "está asignada a {$enZona} zona(s) de personalización";
            }
            if ($portada) {
                $motivos[] = 'se usa como portada de un registro del catálogo';
            }

            return back()->with(
                'error',
                'No se pudo eliminar «'.$base.'» porque '.implode(' y ', $motivos).'. Quita ese uso primero.',
            );
        }

        unlink($full);

        return back()->with('success', 'La imagen se eliminó correctamente.');
    }

    /**
     * Archivos reales de la carpeta, filtrados y ordenados alfabéticamente
     * (case-insensitive, orden natural estable) + metadatos.
     *
     * @return list<array{name: string, image: string, ext: string, size: int, date: string}>
     */
    private function files(string $query = ''): array
    {
        $dir = public_path('images/galeria');
        $allowed = self::ALLOWED;

        $files = collect(File::isDirectory($dir) ? File::files($dir) : [])
            ->filter(fn ($f) => in_array(strtolower($f->getExtension()), $allowed, true))
            ->values();

        $items = $files->map(function ($f) {
            $name = $f->getFilename();

            return [
                'name' => pathinfo($name, PATHINFO_FILENAME),
                'image' => '/images/galeria/'.rawurlencode($name),
                'ext' => strtolower($f->getExtension()),
                'size' => (int) $f->getSize(),
                'date' => date('Y-m-d H:i', $f->getMTime()),
            ];
        });

        if ($query !== '') {
            $q = Str::lower($query);
            $items = $items->filter(fn ($i) => str_contains(Str::lower($i['name']), $q));
        }

        // Orden alfabético ascendente ignorando mayúsculas (server-side, estable)
        return $items
            ->sortBy(fn ($i) => Str::lower($i['name']), SORT_STRING)
            ->values()
            ->all();
    }

    private function pageForGalleryFilename(string $filename): int
    {
        $images = $this->files();
        $position = array_search(
            '/images/galeria/'.rawurlencode($filename),
            array_column($images, 'image'),
            true,
        );

        abort_if($position === false, 500, 'No se pudo encontrar la imagen en la galería.');

        return intdiv($position, self::PER_PAGE) + 1;
    }

    /**
     * ¿Algún anime/manga/juego/canción usa esta ruta como portada?
     */
    private function usedAsCover(string $path): bool
    {
        foreach ([Anime::class, Manga::class, Game::class, MusicTrack::class] as $model) {
            if ($model::query()->where('cover', $path)->exists()) {
                return true;
            }
        }

        return false;
    }
}
