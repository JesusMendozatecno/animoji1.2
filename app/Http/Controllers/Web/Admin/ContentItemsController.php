<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Chapter;
use App\Models\Episode;
use App\Models\Manga;
use App\Models\MangaPage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 📚 GESTIÓN DE CONTENIDO RELACIONADO EN BIBLIOTECA
 *
 *  - Episodios de un anime (temporada, número, fuente de vídeo…).
 *  - Capítulos de un manga (volumen, número, estado).
 *  - Páginas de un capítulo (subida segura de imágenes o ruta de galería).
 *
 * Permisos: anime.manage / manga.manage en el SERVIDOR + rol admin de la ruta.
 * Nunca se duplica la obra madre: todo cuelga de su FK.
 */
class ContentItemsController extends Controller
{
    // ─────────────── EPISODIOS (ANIME) ───────────────

    public function episodes(Anime $anime): JsonResponse
    {
        $this->authorizePermission('anime.manage');

        return response()->json([
            'items' => $anime->episodes()
                ->orderBy('season_number')
                ->orderBy('episode_number')
                ->get()
                ->map(fn (Episode $e) => $this->episodeResource($e)),
        ]);
    }

    public function storeEpisode(Request $request, Anime $anime): JsonResponse
    {
        $this->authorizePermission('anime.manage');

        $validated = $this->validateEpisode($request);

        $episode = DB::transaction(function () use ($anime, $validated) {
            $this->ensureEpisodeNumberFree($anime, $validated);

            return $anime->episodes()->create($validated);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Episodio registrado correctamente.',
            'item' => $this->episodeResource($episode),
        ]);
    }

    public function updateEpisode(Request $request, Episode $episode): JsonResponse
    {
        $this->authorizePermission('anime.manage');

        $validated = $this->validateEpisode($request);

        DB::transaction(function () use ($episode, $validated) {
            if (isset($validated['season_number'], $validated['episode_number'])) {
                $this->ensureEpisodeNumberFree($episode->anime, $validated, $episode->id);
            }
            $episode->fill($validated)->save();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Episodio actualizado correctamente.',
            'item' => $this->episodeResource($episode->fresh()),
        ]);
    }

    public function destroyEpisode(Episode $episode): JsonResponse
    {
        $this->authorizePermission('anime.manage');

        // El progreso de los usuarios sobre este episodio se retira con él
        DB::transaction(function () use ($episode) {
            $episode->progress()->delete();
            $episode->delete();
        });

        return response()->json(['ok' => true, 'message' => 'Episodio eliminado correctamente.']);
    }

    // ─────────────── CAPÍTULOS (MANGA) ───────────────

    public function chapters(Manga $manga): JsonResponse
    {
        $this->authorizePermission('manga.manage');

        return response()->json([
            'items' => $manga->chapters()
                ->orderBy('chapter_number')
                ->get()
                ->map(fn (Chapter $c) => $this->chapterResource($c)),
        ]);
    }

    public function storeChapter(Request $request, Manga $manga): JsonResponse
    {
        $this->authorizePermission('manga.manage');

        $validated = $this->validateChapter($request);

        $chapter = DB::transaction(function () use ($manga, $validated) {
            $this->ensureChapterNumberFree($manga, $validated);

            return $manga->chapters()->create($validated);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Capítulo registrado correctamente.',
            'item' => $this->chapterResource($chapter),
        ]);
    }

    public function updateChapter(Request $request, Chapter $chapter): JsonResponse
    {
        $this->authorizePermission('manga.manage');

        $validated = $this->validateChapter($request);

        DB::transaction(function () use ($chapter, $validated) {
            if (isset($validated['chapter_number'])) {
                $this->ensureChapterNumberFree($chapter->manga, $validated, $chapter->id);
            }
            $chapter->fill($validated)->save();
        });

        return response()->json([
            'ok' => true,
            'message' => 'Capítulo actualizado correctamente.',
            'item' => $this->chapterResource($chapter->fresh()),
        ]);
    }

    public function destroyChapter(Chapter $chapter): JsonResponse
    {
        $this->authorizePermission('manga.manage');

        DB::transaction(function () use ($chapter) {
            $chapter->progress()->delete();
            // Las páginas caen en cascada por la FK
            $chapter->delete();
        });

        return response()->json(['ok' => true, 'message' => 'Capítulo eliminado correctamente.']);
    }

    // ─────────────── PÁGINAS (CAPÍTULO) ───────────────

    public function pages(Chapter $chapter): JsonResponse
    {
        $this->authorizePermission('manga.manage');

        return response()->json([
            'items' => $chapter->pages()
                ->orderBy('page_number')
                ->get()
                ->map(fn (MangaPage $page) => [
                    'id' => $page->id,
                    'page_number' => $page->page_number,
                    'image_path' => $page->image_path,
                    'url' => '/'.ltrim($page->image_path, '/'),
                ]),
        ]);
    }

    /**
     * Añade páginas: subida de archivo (validado) o ruta de la galería.
     * Acepta varias de una vez (files[]).
     */
    public function storePages(Request $request, Chapter $chapter): JsonResponse
    {
        $this->authorizePermission('manga.manage');

        $request->validate([
            'images' => ['nullable', 'array', 'max:30'],
            'images.*' => ['file', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:10240'],
            'paths' => ['nullable', 'array', 'max:30'],
            'paths.*' => ['string', 'max:255'],
        ], [
            'images.*.image' => 'Solo se admiten archivos de imagen.',
            'images.*.mimes' => 'Formato no admitido: usa JPG, PNG, WEBP o GIF.',
            'images.*.max' => 'Cada imagen no puede superar los 10 MB.',
        ]);

        $files = $request->file('images', []);
        $paths = $request->input('paths', []);

        if (count($files) === 0 && count($paths) === 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Adjunta al menos una imagen o indica una ruta de la galería.',
            ], 422);
        }

        $next = (int) ($chapter->pages()->max('page_number') ?? 0) + 1;
        $stored = [];

        DB::transaction(function () use ($chapter, $files, $paths, $next, &$stored) {
            foreach ($files as $file) {
                // Nombre controlado por el servidor (hash) en la carpeta del capítulo
                $dir = 'images/manga/capitulos/'.$chapter->manga_id.'/'.$chapter->id;
                $path = $file->storeAs($dir, Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), 'public');

                $stored[] = $chapter->pages()->create([
                    'page_number' => $next++,
                    'image_path' => $path,
                ]);
            }

            foreach ($paths as $path) {
                $clean = ltrim($path, '/');
                if ($clean === '' || str_contains($clean, '..')) {
                    continue; // anti traversal: se ignoran rutas peligrosas
                }
                $stored[] = $chapter->pages()->create([
                    'page_number' => $next++,
                    'image_path' => $clean,
                ]);
            }
        });

        return response()->json([
            'ok' => true,
            'message' => count($stored).' página(s) añadida(s) correctamente.',
            'items' => collect($stored)->map(fn (MangaPage $page) => [
                'id' => $page->id,
                'page_number' => $page->page_number,
                'image_path' => $page->image_path,
                'url' => '/'.ltrim($page->image_path, '/'),
            ]),
        ]);
    }

    public function destroyPage(MangaPage $page): JsonResponse
    {
        $this->authorizePermission('manga.manage');

        $chapter = $page->chapter;
        $deletedNumber = $page->page_number;

        DB::transaction(function () use ($page, $chapter, $deletedNumber) {
            $path = $page->image_path;
            $page->delete();

            // Renumerar el resto para mantener el orden consecutivo (sin huecos)
            $position = $deletedNumber - 1;
            $chapter->pages()
                ->where('page_number', '>', $deletedNumber)
                ->orderBy('page_number')
                ->get()
                ->each(function (MangaPage $other) use (&$position) {
                    $position++;
                    $other->update(['page_number' => $position]);
                });

            // Solo archivos propios de la carpeta de capítulos
            if (str_starts_with($path, 'images/manga/capitulos/')) {
                Storage::disk('public')->delete($path);
            }
        });

        return response()->json(['ok' => true, 'message' => 'Página eliminada correctamente.']);
    }

    // ─────────────── AUXILIARES ───────────────

    private function authorizePermission(string $permission): void
    {
        abort_unless(
            request()->user()?->can($permission) ?? false,
            403,
            'No tienes permisos para realizar esta acción.',
        );
    }

    /** @return array<string, mixed> */
    private function validateEpisode(Request $request): array
    {
        return $request->validate([
            'season_number' => ['required', 'integer', 'min:1', 'max:999'],
            'episode_number' => ['required', 'integer', 'min:1', 'max:9999'],
            'title' => ['nullable', 'string', 'max:255'],
            'synopsis' => ['nullable', 'string', 'max:5000'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:published,draft'],
        ], [
            'season_number.required' => 'La temporada es obligatoria.',
            'episode_number.required' => 'El número de episodio es obligatorio.',
            'episode_number.min' => 'El número de episodio debe ser 1 o superior.',
            'status.in' => 'El estado seleccionado no es válido.',
            'video_url.max' => 'La fuente de vídeo no puede superar los 500 caracteres.',
        ]);
    }

    /** @return array<string, mixed> */
    private function validateChapter(Request $request): array
    {
        return $request->validate([
            'chapter_number' => ['required', 'integer', 'min:1', 'max:9999'],
            'volume_number' => ['nullable', 'integer', 'min:1', 'max:999'],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:published,draft'],
        ], [
            'chapter_number.required' => 'El número de capítulo es obligatorio.',
            'chapter_number.min' => 'El número de capítulo debe ser 1 o superior.',
            'status.in' => 'El estado seleccionado no es válido.',
        ]);
    }

    /** Evita duplicar T·E dentro del mismo anime (mensaje amigable en español). */
    private function ensureEpisodeNumberFree(Anime $anime, array $validated, ?int $ignoreId = null): void
    {
        $exists = $anime->episodes()
            ->where('season_number', $validated['season_number'])
            ->where('episode_number', $validated['episode_number'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            abort(422, 'Ya existe el episodio T'.$validated['season_number'].' · E'.$validated['episode_number'].' de este anime.');
        }
    }

    private function ensureChapterNumberFree(Manga $manga, array $validated, ?int $ignoreId = null): void
    {
        $exists = $manga->chapters()
            ->where('chapter_number', $validated['chapter_number'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            abort(422, 'Ya existe el capítulo '.$validated['chapter_number'].' de este manga.');
        }
    }

    /** @return array<string, mixed> */
    private function episodeResource(Episode $e): array
    {
        return [
            'id' => $e->id,
            'season_number' => $e->season_number,
            'episode_number' => $e->episode_number,
            'label' => 'T'.$e->season_number.' · E'.$e->episode_number,
            'title' => $e->title,
            'synopsis' => $e->synopsis,
            'duration' => $e->duration,
            'thumbnail' => $e->thumbnail,
            'video_url' => $e->video_url,
            'status' => $e->status,
            'pages_count' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function chapterResource(Chapter $c): array
    {
        return [
            'id' => $c->id,
            'chapter_number' => $c->chapter_number,
            'volume_number' => $c->volume_number,
            'label' => ($c->volume_number ? 'Vol. '.$c->volume_number.' · ' : '').'Capítulo '.$c->chapter_number,
            'title' => $c->title,
            'status' => $c->status,
            'pages_count' => $c->pages()->count(),
        ];
    }
}
