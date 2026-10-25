<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Chapter;
use App\Models\Episode;
use App\Models\Manga;
use App\Models\MediaProgress;
use App\Models\User;
use App\Support\Sections;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;

/**
 * 📺📺 REPRODUCTOR DE ANIME Y 📖 LECTOR DE MANGA
 *
 * - watch(): página de reproducción de un episodio con su fuente real.
 * - read(): lector de capítulos con sus páginas reales ordenadas.
 * Ambos son públicos (como el catálogo); el progreso requiere sesión.
 * El vídeo vive SOLO en su página: al salir, el navegador guarda el progreso.
 */
class WatchController extends Controller
{
    public function watch(Anime $anime, int $episode): View
    {
        $record = $anime->episodes()
            ->where('id', $episode)
            ->where('status', 'published')
            ->firstOrFail();

        $siblings = $anime->episodes()
            ->where('status', 'published')
            ->get();

        $progress = $this->progressFor($record);

        // Vecinos inmediatos (orden narrativo ya garantizado por la relación)
        $index = $siblings->search(fn (Episode $e) => $e->id === $record->id);
        $prev = $index !== false && $index > 0 ? $siblings[$index - 1] : null;
        $next = $index !== false && $index < $siblings->count() - 1 ? $siblings[$index + 1] : null;

        return view('watch.anime', [
            'user' => auth()->user(),
            'anime' => [
                'id' => $anime->id,
                'title' => $anime->title,
                'slug' => $anime->slug,
                'cover' => $anime->cover,
                'url' => route('anime.show', $anime->slug),
            ],
            'episode' => [
                'id' => $record->id,
                'label' => 'T'.$record->season_number.' · E'.$record->episode_number,
                'season_number' => $record->season_number,
                'episode_number' => $record->episode_number,
                'title' => $record->title,
                'synopsis' => $record->synopsis,
                'duration' => $record->duration,
                'thumbnail' => $record->thumbnail,
                // Fuente real; null → la interfaz muestra «no disponible» (sin simular)
                'video_url' => $record->video_url,
            ],
            'episodes' => $siblings->map(fn (Episode $e) => [
                'id' => $e->id,
                'label' => 'T'.$e->season_number.' · E'.$e->episode_number,
                'title' => $e->title,
                'current' => $e->id === $record->id,
                'url' => route('anime.watch', [$anime->slug, $e->id]),
            ])->values()->all(),
            'prevUrl' => $prev ? route('anime.watch', [$anime->slug, $prev->id]) : null,
            'nextUrl' => $next ? route('anime.watch', [$anime->slug, $next->id]) : null,
            'userProgress' => $progress,
        ]);
    }

    public function read(Manga $manga, int $chapter): View
    {
        $record = $manga->chapters()
            ->where('id', $chapter)
            ->where('status', 'published')
            ->firstOrFail();

        $siblings = $manga->chapters()->where('status', 'published')->get();

        $pages = $record->pages()->get()->map(fn ($page) => [
            'id' => $page->id,
            'number' => $page->page_number,
            'url' => '/'.ltrim($page->image_path, '/'),
        ])->values();

        $progress = $this->progressFor($record);

        $index = $siblings->search(fn (Chapter $c) => $c->id === $record->id);
        $prev = $index !== false && $index > 0 ? $siblings[$index - 1] : null;
        $next = $index !== false && $index < $siblings->count() - 1 ? $siblings[$index + 1] : null;

        return view('watch.manga', [
            'user' => auth()->user(),
            'manga' => [
                'id' => $manga->id,
                'title' => $manga->title,
                'slug' => $manga->slug,
                'cover' => $manga->cover,
                'url' => route('manga.show', $manga->slug),
            ],
            'chapter' => [
                'id' => $record->id,
                'number' => $record->chapter_number,
                'volume_number' => $record->volume_number,
                'label' => ($record->volume_number ? 'Vol. '.$record->volume_number.' · ' : '')
                    .'Capítulo '.$record->chapter_number,
                'title' => $record->title,
            ],
            'pages' => $pages,
            'chapters' => $siblings->map(fn (Chapter $c) => [
                'id' => $c->id,
                'number' => $c->chapter_number,
                'label' => 'Cap. '.$c->chapter_number,
                'current' => $c->id === $record->id,
                'url' => route('manga.read', [$manga->slug, $c->id]),
            ])->values()->all(),
            'prevUrl' => $prev ? route('manga.read', [$manga->slug, $prev->id]) : null,
            'nextUrl' => $next ? route('manga.read', [$manga->slug, $next->id]) : null,
            'userProgress' => $progress,
        ]);
    }

    /**
     * Progreso del usuario autenticado para la entidad (null en invitados).
     *
     * @return array{position: int, percentage: float, completed: bool}|null
     */
    private function progressFor(Model $entity): ?array
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $progress = MediaProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('progressable_type', Sections::morphTypesForModel($entity::class))
            ->where('progressable_id', $entity->id)
            ->first();

        if (! $progress) {
            return null;
        }

        return [
            'position' => $progress->position,
            'percentage' => (float) $progress->percentage,
            'completed' => $progress->completed_at !== null,
        ];
    }
}
