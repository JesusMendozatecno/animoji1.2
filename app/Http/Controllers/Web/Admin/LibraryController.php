<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Models\Comment;
use App\Models\Favorite;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MediaProgress;
use App\Models\MusicTrack;
use App\Models\Report;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * 📚 BIBLIOTECA DEL PANEL ADMINISTRATIVO
 *
 * Gestión CRUD real de los cuatro catálogos (anime, manga, juegos, música)
 * sobre los modelos existentes y MariaDB. Paginación server-side de 12.
 *
 * Permisos: cada operación se comprueba en el SERVIDOR con Spatie
 * (anime.manage, manga.manage, games.manage, music.manage) además del
 * middleware role:admin|superadmin de la ruta.
 */
class LibraryController extends Controller
{
    public const PER_PAGE = 12;

    /** Secciones admitidas → configuración. */
    public const SECTIONS = [
        'anime' => [
            'label' => 'Anime',
            'model' => Anime::class,
            'permission' => 'anime.manage',
            'label_singular' => 'anime',
        ],
        'manga' => [
            'label' => 'Manga',
            'model' => Manga::class,
            'permission' => 'manga.manage',
            'label_singular' => 'manga',
        ],
        'juegos' => [
            'label' => 'Videojuegos',
            'model' => Game::class,
            'permission' => 'games.manage',
            'label_singular' => 'videojuego',
        ],
        'musica' => [
            'label' => 'Música',
            'model' => MusicTrack::class,
            'permission' => 'music.manage',
            'label_singular' => 'canción',
        ],
    ];

    /**
     * Listado de una sección con paginación, búsqueda y filtros.
     */
    public function index(Request $request, ?string $section = null): View
    {
        // Ruta sin sección → primera sección (anime)
        $section = $section ?: 'anime';

        $config = $this->config($section);
        $this->authorizeSection($config);

        $query = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        /** @var class-string<Model> $model */
        $model = $config['model'];

        $builder = $model::query();

        if ($query !== '') {
            $builder->where(function ($q) use ($query, $model) {
                $q->where('title', 'like', '%'.$query.'%');

                // Búsqueda en campos propios de cada entidad
                if (in_array($model, [Anime::class], true)) {
                    $q->orWhere('studio', 'like', '%'.$query.'%');
                } elseif (in_array($model, [Manga::class], true)) {
                    $q->orWhere('author', 'like', '%'.$query.'%')
                        ->orWhere('artist', 'like', '%'.$query.'%');
                } elseif (in_array($model, [MusicTrack::class], true)) {
                    $q->orWhere('artist', 'like', '%'.$query.'%')
                        ->orWhere('album', 'like', '%'.$query.'%');
                }
            });
        }

        $paginator = $builder
            ->orderByRaw('LOWER(title)')
            ->orderBy('title')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        return view('admin.biblioteca', [
            'section' => $section,
            'currentSection' => [
                'key' => $section,
                'label' => $config['label'],
            ],
            'sections' => collect(self::SECTIONS)
                ->map(fn ($s, $key) => [
                    'key' => $key,
                    'label' => $s['label'],
                    'count' => $s['model']::query()->count(),
                ])
                ->values()
                ->all(),
            'items' => $paginator,
            'filters' => ['q' => $query],
        ]);
    }

    /**
     * Crea un registro en la sección.
     */
    public function store(Request $request, string $section): RedirectResponse
    {
        $config = $this->config($section);
        $this->authorizeSection($config);

        $validated = $request->validate($this->rules($section), $this->messages($section));

        $data = $this->prepare($section, $validated);

        // 🎵 Subida real de archivo de audio (si se adjunta en vez de URL)
        $this->handleAudioUpload($request, $data);

        DB::transaction(function () use ($config, $data) {
            /** @var class-string<Model> $model */
            $model = $config['model'];

            // Slug único (autogenerado y desambiguado)
            $data['slug'] = $this->uniqueSlug($model, $data['slug'] ?? Str::slug($data['title']));

            $model::query()->create($data);
        });

        return redirect()
            ->route('admin.biblioteca', ['section' => $section])
            ->with('success', 'El registro se creó correctamente.');
    }

    /**
     * Actualiza un registro existente.
     */
    public function update(Request $request, string $section, int $id): RedirectResponse
    {
        $config = $this->config($section);
        $this->authorizeSection($config);

        /** @var class-string<Model> $model */
        $model = $config['model'];
        $record = $model::query()->findOrFail($id);

        $validated = $request->validate($this->rules($section, $id), $this->messages($section));

        $data = $this->prepare($section, $validated);

        // 🎵 Sustituir/añadir archivo de audio (si se adjunta en vez de URL)
        $this->handleAudioUpload($request, $data);

        DB::transaction(function () use ($record, $data) {
            // El slug solo cambia si el título cambió y no está vacío
            if (isset($data['slug']) && $data['slug'] !== '' && $data['slug'] !== $record->slug) {
                $data['slug'] = $this->uniqueSlug($record::class, $data['slug'], $record->id);
            } elseif (isset($data['slug'])) {
                unset($data['slug']);
            }

            $record->fill($data)->save();
        });

        return redirect()
            ->route('admin.biblioteca', ['section' => $section])
            ->with('success', 'La información se actualizó correctamente.');
    }

    /**
     * Elimina un registro tras revisar sus dependencias
     * (favoritos, progreso, comentarios y reportes del catálogo).
     */
    public function destroy(string $section, int $id): RedirectResponse
    {
        $config = $this->config($section);
        $this->authorizeSection($config);

        /** @var class-string<Model> $model */
        $model = $config['model'];
        $record = $model::query()->findOrFail($id);

        $type = $this->morphType($model);

        DB::transaction(function () use ($record, $type) {
            // Dependencias morph: se retiran junto al registro para no dejar
            // referencias rotas (favoritos/progreso/comentarios/reportes).
            Favorite::query()->where('favoritable_type', $type)->where('favoritable_id', $record->id)->delete();
            MediaProgress::query()->where('progressable_type', $type)->where('progressable_id', $record->id)->delete();
            Comment::query()->where('commentable_type', $type)->where('commentable_id', $record->id)->delete();
            Report::query()->where('reportable_type', $type)->where('reportable_id', $record->id)->delete();

            $record->delete();
        });

        return redirect()
            ->route('admin.biblioteca', ['section' => $section])
            ->with('success', 'El elemento se eliminó correctamente.');
    }

    // ─────────────────────────────────────────────────────────────
    //  Auxiliares
    // ─────────────────────────────────────────────────────────────

    /** @return array{label: string, model: class-string<Model>, permission: string, label_singular: string} */
    private function config(string $section): array
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404, 'Sección no válida.');

        return self::SECTIONS[$section];
    }

    /** Comprobación de permiso EN EL SERVIDOR (no basta con ocultar botones). */
    private function authorizeSection(array $config): void
    {
        abort_unless(
            request()->user()?->can($config['permission']) ?? false,
            403,
            'No tienes permisos para realizar esta acción.',
        );
    }

    /** Reglas de validación por sección (basadas en el esquema real). */
    private function rules(string $section, ?int $ignoreId = null): array
    {
        $ratingMax = $this->ratingMaxFor($section);

        $base = [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'cover' => ['nullable', 'string', 'max:255'],
        ];

        $specific = match ($section) {
            'anime' => [
                'synopsis' => ['nullable', 'string', 'max:5000'],
                'studio' => ['nullable', 'string', 'max:255'],
                'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
                'status' => ['nullable', 'string', 'in:ongoing,finished,upcoming'],
                'rating' => ['nullable', 'numeric', 'min:0', 'max:'.$ratingMax],
            ],
            'manga' => [
                'synopsis' => ['nullable', 'string', 'max:5000'],
                'author' => ['nullable', 'string', 'max:255'],
                'artist' => ['nullable', 'string', 'max:255'],
                'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
                'status' => ['nullable', 'string', 'in:ongoing,finished,upcoming'],
                'rating' => ['nullable', 'numeric', 'min:0', 'max:'.$ratingMax],
            ],
            'juegos' => [
                'description' => ['nullable', 'string', 'max:5000'],
                'platforms' => ['nullable', 'string', 'max:255'],
                'requirements' => ['nullable', 'string', 'max:3000'],
                'download_url' => ['nullable', 'url', 'max:255'],
                'release_year' => ['nullable', 'integer', 'min:1970', 'max:2100'],
                'rating' => ['nullable', 'numeric', 'min:0', 'max:'.$ratingMax],
            ],
            'musica' => [
                'artist' => ['required', 'string', 'max:255'],
                'album' => ['nullable', 'string', 'max:255'],
                'genre' => ['nullable', 'string', 'max:60'],
                'audio_url' => ['nullable', 'url', 'max:255'],
                'audio_file' => ['nullable', 'file', 'mimes:mp3,ogg,wav,m4a,aac,flac', 'max:51200'],
                'duration' => ['nullable', 'integer', 'min:0', 'max:86400'],
                'release_year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
                'status' => ['nullable', 'string', 'in:published,draft'],
            ],
            default => [],
        };

        return array_merge($base, $specific);
    }

    /** Mensajes de validación en español natural. */
    private function messages(string $section): array
    {
        $m = [
            'title.required' => 'El título es obligatorio.',
            'title.min' => 'El título debe tener al menos 2 caracteres.',
            'title.max' => 'El título no puede superar los 255 caracteres.',
            'cover.max' => 'La ruta de la portada no puede superar los 255 caracteres.',
            'synopsis.max' => 'La sinopsis no puede superar los 5000 caracteres.',
            'description.max' => 'La descripción no puede superar los 5000 caracteres.',
            'year.integer' => 'El año debe ser un número.',
            'year.min' => 'El año debe estar entre 1900 y 2100.',
            'year.max' => 'El año debe estar entre 1900 y 2100.',
            'release_year.min' => 'El año debe estar entre 1970 y 2100.',
            'release_year.max' => 'El año debe estar entre 1970 y 2100.',
            'status.in' => 'El estado seleccionado no es válido.',
            'rating.numeric' => 'La valoración debe ser un número.',
            'rating.min' => 'La valoración debe estar entre 0 y '.$this->ratingMaxFor($section).'.',
            'rating.max' => 'La valoración debe estar entre 0 y '.$this->ratingMaxFor($section).'.',
            'artist.required' => 'El artista o banda es obligatorio.',
            'artist.max' => 'El artista no puede superar los 255 caracteres.',
            'album.max' => 'El álbum no puede superar los 255 caracteres.',
            'audio_url.url' => 'La URL del audio no es válida.',
            'audio_file.mimes' => 'Formato de audio no admitido: usa MP3, OGG, WAV, M4A, AAC o FLAC.',
            'audio_file.max' => 'El archivo de audio no puede superar los 50 MB.',
            'genre.max' => 'El género no puede superar los 60 caracteres.',
            'download_url.url' => 'La URL de descarga no es válida.',
            'duration.integer' => 'La duración debe expresarse en segundos (número).',
            'duration.min' => 'La duración no puede ser negativa.',
            'platforms.max' => 'La lista de plataformas no puede superar los 255 caracteres.',
        ];

        if ($section === 'juegos') {
            $m['release_year.min'] = 'El año debe estar entre 1970 y 2100.';
            $m['release_year.max'] = 'El año debe estar entre 1970 y 2100.';
        }

        return $m;
    }

    private function ratingMaxFor(string $section): int
    {
        return in_array($section, ['anime', 'manga'], true) ? 10 : 5;
    }

    /** Normaliza los datos validados al formato del modelo. */
    private function prepare(string $section, array $validated): array
    {
        // Slug derivado del título (se desambigua en store/update)
        $validated['slug'] = Str::slug($validated['title']);

        // Numéricos opcionales → null (no 0 fantasma)
        foreach (['year', 'release_year', 'rating', 'duration'] as $num) {
            if (array_key_exists($num, $validated) && ($validated[$num] === '' || $validated[$num] === null)) {
                $validated[$num] = null;
            }
        }

        // "status" y "rating" son NOT NULL en la BD: si llegan vacíos los quitamos
        // para que aplique su valor por defecto ("ongoing" / 0.0) al crear, y para
        // conservar el valor actual al actualizar. (title/slug/artist siempre llegan.)
        foreach (['status', 'rating'] as $required) {
            if (array_key_exists($required, $validated) && ($validated[$required] === '' || $validated[$required] === null)) {
                unset($validated[$required]);
            }
        }

        if ($section === 'juegos') {
            // "PC, PS5" → array JSON (columna json de la BD)
            if (! empty($validated['platforms']) && is_string($validated['platforms'])) {
                $validated['platforms'] = array_values(array_filter(array_map('trim', explode(',', $validated['platforms']))));
            } elseif (empty($validated['platforms'])) {
                $validated['platforms'] = null;
            }
        }

        return $validated;
    }

    /** Slug único dentro de la tabla. */
    private function uniqueSlug(string $model, string $slug, ?int $ignoreId = null): string
    {
        $slug = Str::slug($slug);
        if ($slug === '') {
            $slug = 'registro-'.now()->format('YmdHis');
        }

        $base = $slug;
        $n = 1;
        while (
            $model::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }

    /** Tipo morph de un modelo (para consultar las dependencias). */
    private function morphType(string $model): string
    {
        return (new $model)->getMorphClass();
    }

    /**
     * Sube un archivo de audio adjunto de forma segura (nombre controlado
     * por el servidor, validación de tipo y tamaño) y asigna su ruta relativa
     * a $data['audio_url'] para que el reproductor la use como fuente.
     */
    private function handleAudioUpload(Request $request, array &$data): void
    {
        if (! $request->hasFile('audio_file')) {
            return;
        }

        // Validación explícita además de la de rules() (mensaje en español)
        $request->validate([
            'audio_file' => ['file', 'mimes:mp3,ogg,wav,m4a,aac,flac', 'max:51200'],
        ], [
            'audio_file.mimes' => 'Formato de audio no admitido: usa MP3, OGG, WAV, M4A, AAC o FLAC.',
            'audio_file.max' => 'El archivo de audio no puede superar los 50 MB.',
        ]);

        $file = $request->file('audio_file');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'mp3');
        $path = $file->storeAs(
            'media/audios',
            now()->format('Ymd_His').'_'.bin2hex(random_bytes(5)).'.'.$ext,
            'public',
        );

        // Ruta relativa con barra inicial → el reproductor la usa tal cual
        $data['audio_url'] = '/'.ltrim($path, '/');
    }
}
