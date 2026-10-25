<?php

namespace App\Support;

use App\Models\Anime;
use App\Models\Chapter;
use App\Models\Episode;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;

/**
 * 🧭 Mapa de secciones de contenido de ANIMOJI.
 *
 * Garantiza que cada operación personal (favoritos, listas, pendientes,
 * progreso e historial) trabaje SIEMPRE con el modelo correcto de la
 * sección: impide mezclar identificadores de entidades distintas.
 */
class Sections
{
    public const KEYS = ['anime', 'manga', 'musica', 'juegos'];

    /** sección → modelo del catálogo. */
    private const MODELS = [
        'anime' => Anime::class,
        'manga' => Manga::class,
        'musica' => MusicTrack::class,
        'juegos' => Game::class,
    ];

    /** modelo del catálogo → sección. */
    private const BY_MODEL = [
        Anime::class => 'anime',
        Manga::class => 'manga',
        MusicTrack::class => 'musica',
        Game::class => 'juegos',
    ];

    /** Ruta de detalle de cada sección. */
    private const SHOW_ROUTES = [
        'anime' => 'anime.show',
        'manga' => 'manga.show',
        'musica' => 'music.show',
        'juegos' => 'games.show',
    ];

    /** Tipos que cuentan como "historial reciente" de cada sección. */
    private const HISTORY_TYPES = [
        'anime' => [Anime::class, Episode::class],
        'manga' => [Manga::class, Chapter::class],
        'musica' => [MusicTrack::class],
        'juegos' => [Game::class],
    ];

    public static function isValid(string $section): bool
    {
        return isset(self::MODELS[$section]);
    }

    /** @return class-string<Model> */
    public static function model(string $section): string
    {
        if (! self::isValid($section)) {
            throw new InvalidArgumentException("Sección no soportada: {$section}");
        }

        return self::MODELS[$section];
    }

    /** Sección (anime|manga|musica|juegos) a la que pertenece un modelo, o null. */
    public static function sectionOf(Model $model): ?string
    {
        return self::BY_MODEL[$model::class] ?? null;
    }

    /** Sección a la que pertenece un tipo morfo (App\Models\Anime → 'anime'). */
    public static function sectionOfMorph(string $type): ?string
    {
        $model = Relation::getMorphedModel($type) ?? $type;

        return self::BY_MODEL[$model] ?? null;
    }

    /** Alias y clases históricas válidas para el tipo morfo de una sección. */
    public static function morphTypesForSection(string $section): array
    {
        return self::morphTypesForModel(self::model($section));
    }

    /** Alias y clase completa histórica aceptados para un tipo polimórfico. */
    public static function morphTypesForModel(string $model): array
    {
        return array_values(array_unique([(new $model)->getMorphClass(), $model]));
    }

    /** Alias y clases históricas de los tipos morfo del catálogo. */
    public static function morphTypes(): array
    {
        $types = [];

        foreach (self::KEYS as $section) {
            $types = array_merge($types, self::morphTypesForSection($section));
        }

        return array_values(array_unique($types));
    }

    /** ¿Pertenece esta entidad exacta a esta sección? (anti manipulación de IDs) */
    public static function matches(Model $model, string $section): bool
    {
        return self::sectionOf($model) === $section;
    }

    /**
     * Carga una entidad verificando que exista y pertenezca a la sección.
     * Devuelve null si no corresponde (el caller responde 404/422).
     */
    public static function find(string $section, int|string $id): ?Model
    {
        if (! self::isValid($section)) {
            return null;
        }

        /** @var class-string<Model> $model */
        $model = self::MODELS[$section];

        return $model::query()->find($id);
    }

    /**
     * @return list<string> tipos de modelo que alimentan el historial de la sección
     */
    public static function historyTypes(string $section): array
    {
        $types = [];

        foreach (self::HISTORY_TYPES[$section] ?? [] as $model) {
            $types = array_merge($types, self::morphTypesForModel($model));
        }

        return array_values(array_unique($types));
    }

    /**
     * Tarjeta común para el frontend (portada, título, enlace de detalle).
     *
     * @return array<string, mixed>
     */
    public static function card(Model $item, ?string $section = null): array
    {
        $section ??= self::sectionOf($item) ?? 'anime';

        $subtitle = match ($section) {
            'anime' => $item->studio,
            'manga' => $item->author,
            'musica' => $item->artist,
            'juegos' => $item->release_year ? ($item->release_year.'') : null,
            default => null,
        };

        // Episodios y capítulos se muestran a través de su padre
        if ($item instanceof Episode) {
            $parent = $item->anime;
            $card = self::card($parent, 'anime');
            $card['title'] = $parent->title;
            $card['context'] = 'T'.$item->season_number.' · E'.$item->episode_number
                .($item->title ? ' — '.$item->title : '');

            return $card;
        }

        if ($item instanceof Chapter) {
            $parent = $item->manga;
            $card = self::card($parent, 'manga');
            $card['context'] = 'Capítulo '.$item->chapter_number
                .($item->title ? ' — '.$item->title : '');

            return $card;
        }

        return [
            'id' => $item->id,
            'section' => $section,
            'title' => $item->title ?? 'Sin título',
            'cover' => $item->cover ?? null,
            'slug' => $item->slug ?? null,
            'subtitle' => $subtitle,
            'context' => null,
            'url' => $item->slug
                ? route(self::SHOW_ROUTES[$section], $item->slug)
                : null,
        ];
    }
}
