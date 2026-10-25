<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Chapter;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaProgress;
use App\Models\MusicTrack;
use App\Models\User;
use App\Services\CatalogService;
use App\Support\Sections;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(private readonly CatalogService $catalog)
    {
    }

    public function anime(): View
    {
        return $this->index('anime');
    }

    public function animeShow(Anime $anime): View
    {
        return $this->show('anime', $anime);
    }

    public function manga(): View
    {
        return $this->index('manga');
    }

    public function mangaShow(Manga $manga): View
    {
        return $this->show('manga', $manga);
    }

    public function music(): View
    {
        return $this->index('music');
    }

    public function musicShow(MusicTrack $track): View
    {
        return $this->show('music', $track);
    }

    public function games(): View
    {
        return $this->index('game');
    }

    public function gameShow(Game $game): View
    {
        return $this->show('game', $game);
    }

    private function index(string $type): View
    {
        $user = request()->user();
        $config = $this->indexConfig($type);
        $items = collect($this->catalog->paginate($type, 8)->items())
            ->map(fn (Model $item) => $this->indexCard($item, $config['showRoute']))
            ->values();
        $musicPlayerTracks = collect();

        if ($type === 'music') {
            $audioTracks = $items->filter(fn (array $item) => filled($item['audio_url']));
            $progress = $user && $audioTracks->isNotEmpty()
                ? $user->progress()
                    ->whereIn('progressable_type', Sections::morphTypesForModel(MusicTrack::class))
                    ->whereIn('progressable_id', $audioTracks->pluck('id'))
                    ->get()
                    ->keyBy('progressable_id')
                : collect();

            $musicPlayerTracks = $audioTracks->map(fn (array $track) => [
                'id' => $track['id'],
                'title' => $track['title'],
                'artist' => $track['subtitle'],
                'cover' => $track['cover'],
                'url' => $track['audio_url'],
                'duration' => $track['duration'],
                'position' => (int) ($progress->get($track['id'])?->position ?? 0),
            ])->values();
        }

        return view('catalog.index', [
            'user' => $user,
            'section' => $config['section'],
            'currentPage' => 'inicio',
            'config' => $config,
            'items' => $items,
            'stats' => $this->stats($type),
            'musicPlayerTracks' => $musicPlayerTracks,
        ]);
    }

    /** @return array<string, mixed> */
    private function indexConfig(string $type): array
    {
        return match ($type) {
            'anime' => [
                'section' => 'anime',
                'label' => 'Anime',
                'indexRoute' => 'anime.index',
                'showRoute' => 'anime.show',
                'theme' => 'red',
                'quote' => '「好きなことで生きていく」',
                'heroBadge' => 'Anime',
                'heroTitle' => 'Descubre el mundo del anime',
                'heroDescription' => 'Explora miles de historias, desde los clásicos hasta los estrenos más recientes.',
                'heading' => 'Animes destacados',
                'subheading' => 'Los más populares y vistos por nuestra comunidad',
                'recommended' => 'Animes que podrían interesarte',
                'totalLabel' => 'Animes',
                'filters' => ['Todos', 'Acción', 'Aventura', 'Comedia', 'Drama', '+'],
            ],
            'manga' => [
                'section' => 'manga',
                'label' => 'Manga',
                'indexRoute' => 'manga.index',
                'showRoute' => 'manga.show',
                'theme' => 'mono',
                'quote' => 'El manga no es solo una historia, es un mundo por descubrir.',
                'heroBadge' => 'Manga',
                'heroTitle' => 'Explora el mundo del manga',
                'heroDescription' => 'Sumérgete en historias increíbles, descubre nuevos géneros y vive la emoción de cada capítulo.',
                'heading' => 'Mangas destacados',
                'subheading' => 'Los más populares y leídos por nuestra comunidad',
                'recommended' => 'Mangas que podrían interesarte',
                'totalLabel' => 'Mangas',
                'filters' => ['Todos', 'Acción', 'Aventura', 'Comedia', 'Drama', '+'],
            ],
            'music' => [
                'section' => 'musica',
                'label' => 'Música',
                'indexRoute' => 'music.index',
                'showRoute' => 'music.show',
                'theme' => 'blue',
                'quote' => 'La música es la forma más pura de sentir lo que no se puede decir.',
                'heroBadge' => 'Bienvenido',
                'heroTitle' => 'Que la música te acompañe',
                'heroDescription' => 'Descubre, reproduce y vive tus canciones favoritas en un solo lugar. Tu mundo, tu música.',
                'heading' => 'Tendencias',
                'subheading' => 'Las canciones más escuchadas del momento',
                'recommended' => 'Basado en lo que has escuchado',
                'totalLabel' => 'Canciones',
                'filters' => ['Todo', 'Anime', 'J-pop', 'Rock', 'Lo-fi', 'EDM', '›'],
                'playlists' => [
                    ['name' => 'Para Estudiar', 'genre' => 'Lo-fi · Anime', 'count' => 42, 'image' => '/images/musica/playlist-1.jpg'],
                    ['name' => 'Vibes Anime', 'genre' => 'J-pop · Anime', 'count' => 68, 'image' => '/images/musica/playlist-2.jpg'],
                    ['name' => 'Ritmo y Energía', 'genre' => 'Rock · Anime', 'count' => 56, 'image' => '/images/musica/playlist-3.jpg'],
                    ['name' => 'Tranquilidad', 'genre' => 'Lo-fi · Chill', 'count' => 47, 'image' => '/images/musica/playlist-4.jpg'],
                    ['name' => 'Opening & Ending', 'genre' => 'Anime · Variado', 'count' => 103, 'image' => '/images/musica/playlist-5.jpg'],
                ],
            ],
            'game' => [
                'section' => 'juegos',
                'label' => 'Videojuegos',
                'indexRoute' => 'games.index',
                'showRoute' => 'games.show',
                'theme' => 'teal',
                'quote' => 'Los videojuegos no son solo juego: son aventura, reto y amistad.',
                'heroBadge' => 'Player 1',
                'heroTitle' => '¿Listos para jugar?',
                'heroDescription' => 'Descarga, juega y vive tus títulos favoritos en un solo lugar. Tu mundo, tus partidas.',
                'heading' => 'Destacados',
                'subheading' => 'Los juegos más jugados del momento',
                'recommended' => 'Basado en lo que has jugado',
                'totalLabel' => 'Juegos',
                'filters' => ['Todo', 'RPG', 'Acción', 'Aventura', 'Shooter', 'Indie', '›'],
                'genres' => [
                    ['name' => 'RPG', 'description' => 'Mundos e historias épicas', 'count' => 128, 'image' => '/images/juegos/genre-1.png'],
                    ['name' => 'Acción', 'description' => 'Adrenalina pura', 'count' => 96, 'image' => '/images/juegos/genre-2.png'],
                    ['name' => 'Aventura', 'description' => 'Explora sin límites', 'count' => 74, 'image' => '/images/juegos/genre-3.png'],
                    ['name' => 'Shooter', 'description' => 'Puntería y equipo', 'count' => 63, 'image' => '/images/juegos/genre-4.png'],
                    ['name' => 'Indie', 'description' => 'Creatividad sin filtros', 'count' => 51, 'image' => '/images/juegos/genre-5.png'],
                ],
            ],
        };
    }

    /** @return array<string, mixed> */
    private function indexCard(Model $item, string $showRoute): array
    {
        $card = [
            'id' => $item->id,
            'title' => $item->title,
            'slug' => $item->slug,
            'cover' => $item->cover,
            'url' => route($showRoute, $item->slug),
            'rating' => $item->rating ?? null,
        ];

        return array_merge($card, [
            'year' => $item->year ?? $item->release_year ?? null,
            'subtitle' => $item->studio ?? $item->author ?? $item->artist ?? null,
            'album' => $item->album ?? null,
            'status' => $item->status ?? null,
            'duration' => $item->duration ?? null,
            'audio_url' => $item->audio_url ?? null,
            'platform' => $item->platforms[0] ?? null,
        ]);
    }

    private function show(string $type, Model $item): View
    {
        $section = match ($type) {
            'anime' => 'anime',
            'manga' => 'manga',
            'music' => 'musica',
            default => 'juegos',
        };

        // 📺 Anime: temporadas + episodios publicados (orden narrativo)
        if ($type === 'anime' && $item instanceof Anime) {
            $seasons = $this->seasonsFor($item);
        } else {
            $seasons = [];
        }

        // 📖 Manga: capítulos publicados (orden numérico)
        if ($type === 'manga' && $item instanceof Manga) {
            $chapters = $this->chaptersFor($item);
        } else {
            $chapters = [];
        }

        // 🎮 Videojuego: registra la visita como actividad reciente
        if ($type === 'game' && $item instanceof Game) {
            $this->recordGameVisit(request(), $item);
        }

        $user = request()->user();

        return view('catalog.show', [
            'user' => $user,
            'type' => $type,
            'section' => $section,
            'item' => $item,
            'seasons' => $seasons,
            'chapters' => $chapters,
            'isFavorite' => $user !== null && $user->favorites()
                ->whereIn('favoritable_type', Sections::morphTypesForSection($section))
                ->where('favoritable_id', $item->id)
                ->exists(),
            'isPending' => $user !== null && $user->pendingItems()
                ->where('section', $section)
                ->whereIn('pendingable_type', Sections::morphTypesForSection($section))
                ->where('pendingable_id', $item->id)
                ->exists(),
        ]);
    }

    /**
     * Temporadas de un anime con sus episodios publicados y el progreso
     * del usuario autenticado en cada uno (null si es invitado).
     *
     * @return list<array{number: int, episodes: list<array<string, mixed>>}>
     */
    private function seasonsFor(Anime $anime): array
    {
        $episodes = $anime->episodes()
            ->where('status', 'published')
            ->get();

        $progress = [];

        if (auth()->check()) {
            $progress = MediaProgress::query()
                ->where('user_id', auth()->id())
                ->whereIn('progressable_type', Sections::morphTypesForModel(Episode::class))
                ->whereIn('progressable_id', $episodes->pluck('id'))
                ->get()
                ->keyBy('progressable_id');
        }

        return $episodes
            ->groupBy('season_number')
            ->sortKeys()
            ->map(fn ($seasonEpisodes, $number) => [
                'number' => (int) $number,
                'episodes' => $seasonEpisodes->map(fn (Episode $episode) => [
                    'id' => $episode->id,
                    'season_number' => $episode->season_number,
                    'episode_number' => $episode->episode_number,
                    'label' => 'T'.$episode->season_number.' · E'.$episode->episode_number,
                    'title' => $episode->title,
                    'duration' => $episode->duration,
                    'thumbnail' => $episode->thumbnail,
                    'has_source' => $episode->video_url !== null && $episode->video_url !== '',
                    'percentage' => isset($progress[$episode->id])
                        ? (float) $progress[$episode->id]->percentage
                        : 0.0,
                    'completed' => isset($progress[$episode->id])
                        && $progress[$episode->id]->completed_at !== null,
                    'url' => route('anime.watch', [$anime->slug, $episode->id]),
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Capítulos publicados de un manga con el progreso del usuario.
     *
     * @return list<array<string, mixed>>
     */
    private function chaptersFor(Manga $manga): array
    {
        $chapters = $manga->chapters()->where('status', 'published')->get();

        $progress = [];

        if (auth()->check()) {
            $progress = MediaProgress::query()
                ->where('user_id', auth()->id())
                ->whereIn('progressable_type', Sections::morphTypesForModel(Chapter::class))
                ->whereIn('progressable_id', $chapters->pluck('id'))
                ->get()
                ->keyBy('progressable_id');
        }

        return $chapters->map(fn (Chapter $chapter) => [
            'id' => $chapter->id,
            'chapter_number' => $chapter->chapter_number,
            'volume_number' => $chapter->volume_number,
            'label' => ($chapter->volume_number ? 'Vol. '.$chapter->volume_number.' · ' : '')
                .'Capítulo '.$chapter->chapter_number,
            'title' => $chapter->title,
            'pages_count' => $chapter->pages()->count(),
            'percentage' => isset($progress[$chapter->id])
                ? (float) $progress[$chapter->id]->percentage
                : 0.0,
            'completed' => isset($progress[$chapter->id])
                && $progress[$chapter->id]->completed_at !== null,
            'url' => route('manga.read', [$manga->slug, $chapter->id]),
        ])->values()->all();
    }

    /** Registra la visita a un videojuego como actividad reciente (histórico). */
    private function recordGameVisit(Request $request, Game $game): void
    {
        if (! $request->user()) {
            return;
        }

        // No infla el porcentaje: solo marca «consultado» (0–2%)
        $request->user()->progress()->updateOrCreate(
            [
                'progressable_type' => $game->getMorphClass(),
                'progressable_id' => $game->id,
            ],
            [
                'position' => 0,
                'percentage' => 0,
            ],
        );
    }

    /**
     * Estadísticas del apartado para la columna derecha.
     *
     * @return array{total: int, users: int, avgRating: float}
     */
    private function stats(string $type): array
    {
        $model = match ($type) {
            'anime' => Anime::class,
            'manga' => Manga::class,
            'music' => MusicTrack::class,
            default => Game::class,
        };

        // Solo los catálogos con campo rating (music_tracks no lo tiene)
        $avg = in_array($type, ['anime', 'manga', 'game'], true)
            ? $model::query()->avg('rating')
            : 0;

        return [
            'total' => (int) $model::query()->count(),
            'users' => (int) User::query()->count(),
            'avgRating' => round((float) ($avg ?? 0), 1),
        ];
    }
}
