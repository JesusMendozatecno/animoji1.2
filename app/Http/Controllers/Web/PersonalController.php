<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Episode;
use App\Models\MediaProgress;
use App\Models\PendingItem;
use App\Models\UserList;
use App\Http\Controllers\Web\Admin\CustomizationController;
use App\Support\Navigation;
use App\Support\Sections;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * 🗂️ ORGANIZACIÓN PERSONAL DEL USUARIO
 *
 * Favoritos, listas, pendientes, historial ("seguir viendo") y progreso.
 * Todo persiste en MariaDB, pertenece al usuario autenticado y valida en
 * el SERVIDOR que cada entidad pertenezca a la sección declarada.
 */
class PersonalController extends Controller
{
    /** Límite de pendientes por usuario y sección. */
    public const PENDING_LIMIT = 10;

    /** Elementos del historial ("seguir viendo"). */
    public const HISTORY_LIMIT = 10;

    // ══════════════════════ FAVORITOS ══════════════════════

    public function favorites(Request $request, string $section): View
    {
        abort_unless(Sections::isValid($section), 404);

        $page = max(1, (int) $request->query('page', 1));
        $user = $request->user();

        $paginator = $user->favorites()
            ->whereIn('favoritable_type', Sections::morphTypesForSection($section))
            ->with('favoritable')
            ->orderByDesc('updated_at')
            ->paginate(10, ['*'], 'page', $page)
            ->withQueryString();

        $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn ($favorite) => $favorite->favoritable
                    ? array_merge(
                        Sections::card($favorite->favoritable, $section),
                        ['favorite_id' => $favorite->id],
                    )
                    : null)
                ->filter()
                ->values(),
        );

        $config = $this->sectionConfig($section);

        return view('personal.favoritos', [
            'user' => $user,
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $user->customizing,
            'section' => $section,
            'config' => $config,
            'currentPage' => 'favoritos',
            'items' => $paginator,
        ]);
    }

    /** Marca/desmarca un favorito (idempotente, sin duplicados). */
    public function toggleFavorite(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string'],
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $section = $validated['section'];
        abort_unless(Sections::isValid($section), 422, 'Sección no válida.');

        $item = Sections::find($section, $validated['id']);

        if (! $item) {
            return back()->with('error', 'El contenido solicitado no existe.');
        }

        $user = $request->user();
        $type = $item->getMorphClass();

        $existing = $user->favorites()
            ->whereIn('favoritable_type', Sections::morphTypesForSection($section))
            ->where('favoritable_id', $item->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('success', 'Se retiró de tus favoritos.');
        }

        // firstOrCreate evita duplicados ante peticiones simultáneas
        $user->favorites()->firstOrCreate([
            'favoritable_type' => $type,
            'favoritable_id' => $item->id,
        ]);

        return back()->with('success', 'Añadido a tus favoritos ❤️');
    }

    /**
     * Búsqueda JSON del catálogo de una sección (para añadir a listas).
     * Autenticación obligatoria; solo devuelve tarjetas comunes.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string'],
            'q' => ['required', 'string', 'min:2', 'max:80'],
        ], [
            'q.min' => 'Escribe al menos 2 caracteres para buscar.',
        ]);

        $section = $validated['section'];
        abort_unless(Sections::isValid($section), 422, 'Sección no válida.');

        $items = $this->searchCatalogItems($section, trim($validated['q']));

        return response()->json(['items' => $items]);
    }

    // ══════════════════════ LISTAS ══════════════════════

    public function lists(Request $request, string $section): View
    {
        abort_unless(Sections::isValid($section), 404);

        $filters = $request->validate([
            'list' => ['nullable', 'integer', 'min:0'],
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        $user = $request->user();
        $userLists = $user
            ->lists()
            ->where('section', $section)
            ->with(['items.itemable'])
            ->orderBy('name')
            ->get();

        $lists = $userLists->map(fn (UserList $list) => [
                'id' => $list->id,
                'name' => $list->name,
                'count' => $list->items->count(),
                'items' => $list->items
                    ->map(fn ($item) => $item->itemable
                        ? array_merge(
                            Sections::card($item->itemable, $section),
                            ['list_item_id' => $item->id],
                        )
                        : null)
                    ->filter()
                    ->values(),
            ]);

        $selectedList = array_key_exists('list', $filters)
            ? $userLists->firstWhere('id', (int) $filters['list'])
            : $userLists->first();
        $selectedId = $selectedList?->id;
        $searchQuery = trim($filters['q'] ?? '');
        $searchResults = $selectedList && mb_strlen($searchQuery) >= 2
            ? $this->searchCatalogItems($section, $searchQuery)
            : collect();
        $config = $this->sectionConfig($section);
        $config['quote'] = 'Organiza tu contenido a tu manera.';

        return view('personal.listas', [
            'user' => $user,
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $user->customizing,
            'section' => $section,
            'config' => $config,
            'currentPage' => 'listas',
            'sidebarHint' => 'Listas privadas: solo tú puedes verlas.',
            'lists' => $lists,
            'selectedId' => $selectedId,
            'searchQuery' => $searchQuery,
            'searchResults' => $searchResults,
        ]);
    }

    public function storeList(Request $request, string $section): RedirectResponse
    {
        abort_unless(Sections::isValid($section), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
        ], [
            'name.required' => 'Escribe un nombre para la lista.',
            'name.max' => 'El nombre no puede superar los 60 caracteres.',
        ]);

        $user = $request->user();

        $duplicate = $user->lists()
            ->where('section', $section)
            ->where('name', $validated['name'])
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'Ya tienes una lista con ese nombre en esta sección.');
        }

        $user->lists()->create([
            'section' => $section,
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Lista creada correctamente.');
    }

    public function updateList(Request $request, UserList $list): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403, 'No tienes permisos para realizar esta acción.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:60'],
        ], [
            'name.required' => 'Escribe un nombre para la lista.',
            'name.max' => 'El nombre no puede superar los 60 caracteres.',
        ]);

        $duplicate = $request->user()
            ->lists()
            ->where('section', $list->section)
            ->where('name', $validated['name'])
            ->where('id', '!=', $list->id)
            ->exists();

        if ($duplicate) {
            return back()->with('error', 'Ya tienes una lista con ese nombre en esta sección.');
        }

        $list->update(['name' => $validated['name']]);

        return back()->with('success', 'La lista se renombró correctamente.');
    }

    public function destroyList(Request $request, UserList $list): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403, 'No tienes permisos para realizar esta acción.');

        $list->delete();

        return back()->with('success', 'La lista se eliminó correctamente.');
    }

    public function addListItem(Request $request, UserList $list): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403, 'No tienes permisos para realizar esta acción.');

        $validated = $request->validate([
            'id' => ['required', 'integer', 'min:1'],
        ]);

        // La entidad debe pertenecer a la sección de la lista
        $item = Sections::find($list->section, $validated['id']);

        if (! $item) {
            return back()->with('error', 'El contenido solicitado no existe en esta sección.');
        }

        $type = $item->getMorphClass();
        $exists = $list->items()
            ->whereIn('itemable_type', Sections::morphTypesForSection($list->section))
            ->where('itemable_id', $item->id)
            ->exists();

        if ($exists) {
            return back()->with('warning', 'Ese contenido ya está en la lista.');
        }

        $last = $list->items()->max('position');

        $list->items()->create([
            'itemable_type' => $type,
            'itemable_id' => $item->id,
            'position' => ($last ?? 0) + 1,
        ]);

        return back()->with('success', 'Añadido a la lista.');
    }

    public function removeListItem(Request $request, UserList $list, int $item): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403, 'No tienes permisos para realizar esta acción.');

        $list->items()->where('id', $item)->delete();

        return back()->with('success', 'Elemento retirado de la lista.');
    }

    // ══════════════════════ PENDIENTES ══════════════════════

    public function pendings(Request $request, string $section): View
    {
        abort_unless(Sections::isValid($section), 404);

        $items = $request->user()
            ->pendingItems()
            ->where('section', $section)
            ->whereIn('pendingable_type', Sections::morphTypesForSection($section))
            ->with('pendingable')
            ->orderByDesc('updated_at')
            ->limit(self::PENDING_LIMIT)
            ->get()
            ->map(fn ($pending) => $pending->pendingable
                ? array_merge(
                    Sections::card($pending->pendingable, $section),
                    ['pending_id' => $pending->id],
                )
                : null)
            ->filter()
            ->values();

        $user = $request->user();
        $config = $this->sectionConfig($section);
        $config['quote'] = 'Lo que quieres ver, leer o escuchar más adelante.';

        return view('personal.pendientes', [
            'user' => $user,
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $user->customizing,
            'section' => $section,
            'config' => $config,
            'currentPage' => 'pendientes',
            'sidebarHint' => 'Máximo '.self::PENDING_LIMIT.' por apartado.',
            'items' => $items,
            'limit' => self::PENDING_LIMIT,
        ]);
    }

    /**
     * Añade/quit un pendiente. El límite de 10 se comprueba en el SERVIDOR
     * dentro de una transacción con bloqueo (peticiones simultáneas seguras).
     */
    public function togglePending(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string'],
            'id' => ['required', 'integer', 'min:1'],
        ]);

        $section = $validated['section'];
        abort_unless(Sections::isValid($section), 422, 'Sección no válida.');

        $item = Sections::find($section, $validated['id']);

        if (! $item) {
            return back()->with('error', 'El contenido solicitado no existe.');
        }

        $user = $request->user();
        $type = $item->getMorphClass();

        try {
            DB::transaction(function () use ($user, $section, $type, $item) {
                $existing = $user->pendingItems()
                    ->where('section', $section)
                    ->whereIn('pendingable_type', Sections::morphTypesForSection($section))
                    ->where('pendingable_id', $item->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    $existing->delete();

                    return;
                }

                $count = $user->pendingItems()
                    ->where('section', $section)
                    ->lockForUpdate()
                    ->count();

                if ($count >= self::PENDING_LIMIT) {
                    throw ValidationException::withMessages([
                        'limit' => 'Has alcanzado el límite de '.$this->pendingLimitLabel($section)
                            .'. Retira otro elemento antes de añadir uno nuevo.',
                    ]);
                }

                $user->pendingItems()->create([
                    'section' => $section,
                    'pendingable_type' => $type,
                    'pendingable_id' => $item->id,
                ]);
            });
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        $stillExists = $user->pendingItems()
            ->where('section', $section)
            ->whereIn('pendingable_type', Sections::morphTypesForSection($section))
            ->where('pendingable_id', $item->id)
            ->exists();

        return $stillExists
            ? back()->with('success', 'Añadido a tus pendientes ⏳')
            : back()->with('success', 'Retirado de tus pendientes.');
    }

    // ══════════════════════ SEGUIR VIENDO (HISTORIAL) ══════════════════════

    public function history(Request $request, string $section): View
    {
        abort_unless(Sections::isValid($section), 404);

        $types = Sections::historyTypes($section);

        $items = $request->user()
            ->progress()
            ->whereIn('progressable_type', $types)
            ->with(['progressable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                Episode::class => ['anime'],
                Chapter::class => ['manga'],
            ])])
            ->orderByDesc('updated_at')
            ->limit(self::HISTORY_LIMIT)
            ->get()
            ->map(function (MediaProgress $progress) use ($section) {
                $entity = $progress->progressable;

                if (! $entity) {
                    return null;
                }

                $card = Sections::card($entity, $section);
                $continueUrl = match (true) {
                    $entity instanceof Episode && $entity->anime => route('anime.watch', [$entity->anime->slug, $entity->id]),
                    $entity instanceof Chapter && $entity->manga => route('manga.read', [$entity->manga->slug, $entity->id]),
                    default => $card['url'],
                };

                return array_merge($card, [
                    'position' => $progress->position,
                    'percentage' => (float) $progress->percentage,
                    'completed' => $progress->completed_at !== null,
                    'last_seen' => $progress->updated_at->toIso8601String(),
                    'continue_url' => $continueUrl,
                ]);
            })
            ->filter()
            ->values();

        $user = $request->user();
        $config = $this->sectionConfig($section);
        $config['quote'] = 'Retoma donde lo dejaste.';

        return view('personal.seguir', [
            'user' => $user,
            'navigationOverrides' => Navigation::map(),
            'customizations' => CustomizationController::map(),
            'customizing' => (bool) $user->customizing,
            'section' => $section,
            'config' => $config,
            'currentPage' => 'seguir',
            'sidebarHint' => 'Tu actividad reciente en este apartado.',
            'items' => $items,
        ]);
    }

    // ══════════════════════ PROGRESO ══════════════════════

    /**
     * Guarda/reutiliza el progreso del usuario (updateOrCreate → nunca
     * duplica entradas por cada actualización).
     */
    public function storeProgress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section' => ['required', 'string'],
            'id' => ['required', 'integer', 'min:1'],
            'target' => ['nullable', 'string'], // episodio/capítulo: entidad distinta del catálogo
            'target_id' => ['nullable', 'integer', 'min:1'],
            'position' => ['nullable', 'integer', 'min:0'],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'completed' => ['nullable', 'boolean'],
        ]);

        $section = $validated['section'];
        abort_unless(Sections::isValid($section), 422, 'Sección no válida.');

        $user = $request->user();

        // Entidad sobre la que recae el progreso: episodio/capítulo o el propio catálogo
        $target = $this->resolveProgressTarget($section, $validated);

        if (! $target) {
            return back()->with('error', 'El contenido solicitado no existe.');
        }

        $user->progress()->updateOrCreate(
            [
                'progressable_type' => $target->getMorphClass(),
                'progressable_id' => $target->id,
            ],
            [
                'position' => (int) ($validated['position'] ?? 0),
                'percentage' => min(100, max(0, (float) ($validated['percentage'] ?? 0))),
                'completed_at' => ($validated['completed'] ?? false) ? now() : null,
            ],
        );

        return back();
    }

    /**
     * Resuelve la entidad real del progreso (acepta episodio/capítulo
     * verificando que pertenezca a la sección/obra correcta).
     */
    private function resolveProgressTarget(string $section, array $validated): ?Model
    {
        $targetType = $validated['target'] ?? null;

        if ($targetType === 'episode' && $section === 'anime') {
            $episode = Episode::query()->find($validated['target_id'] ?? 0);

            return $episode?->anime_id ? $episode : null;
        }

        if ($targetType === 'chapter' && $section === 'manga') {
            $chapter = Chapter::query()->find($validated['target_id'] ?? 0);

            return $chapter?->manga_id ? $chapter : null;
        }

        return Sections::find($section, $validated['id']);
    }

    /** Etiqueta del límite por sección para el mensaje de error. */
    private function pendingLimitLabel(string $section): string
    {
        return match ($section) {
            'anime' => '10 animes pendientes',
            'manga' => '10 mangas pendientes',
            'musica' => '10 canciones pendientes',
            'juegos' => '10 videojuegos pendientes',
            default => '10 elementos pendientes',
        };
    }

    /**
     * Busca tarjetas del catálogo, compartidas por el endpoint JSON y las listas Blade.
     */
    private function searchCatalogItems(string $section, string $query): Collection
    {
        /** @var class-string<Model> $model */
        $model = Sections::model($section);

        return $model::query()
            ->where(function ($builder) use ($query, $model) {
                $builder->where('title', 'like', '%'.$query.'%');

                if ($model === \App\Models\Anime::class) {
                    $builder->orWhere('studio', 'like', '%'.$query.'%');
                } elseif ($model === \App\Models\Manga::class) {
                    $builder->orWhere('author', 'like', '%'.$query.'%')
                        ->orWhere('artist', 'like', '%'.$query.'%');
                } elseif ($model === \App\Models\MusicTrack::class) {
                    $builder->orWhere('artist', 'like', '%'.$query.'%')
                        ->orWhere('album', 'like', '%'.$query.'%')
                        ->orWhere('genre', 'like', '%'.$query.'%');
                }
            })
            ->orderBy('title')
            ->limit(8)
            ->get()
            ->map(fn (Model $item) => Sections::card($item, $section));
    }

    /** @return array<string, string> */
    private function sectionConfig(string $section): array
    {
        return match ($section) {
            'anime' => [
                'label' => 'Anime',
                'favorites' => 'Mis favoritos de anime',
                'list' => 'Mis listas de anime',
                'pending' => 'Pendientes de anime',
                'continue' => 'Seguir viendo anime',
                'indexRoute' => 'anime.index',
                'theme' => 'red',
                'quote' => 'Tus animes favoritos en un solo lugar.',
            ],
            'manga' => [
                'label' => 'Manga',
                'favorites' => 'Mis mangas favoritos',
                'list' => 'Mis listas de manga',
                'pending' => 'Pendientes de manga',
                'continue' => 'Seguir leyendo manga',
                'indexRoute' => 'manga.index',
                'theme' => 'purple',
                'quote' => 'Tus mangas favoritos en un solo lugar.',
            ],
            'musica' => [
                'label' => 'Música',
                'favorites' => 'Mis canciones favoritas',
                'list' => 'Mis listas de música',
                'pending' => 'Música pendiente',
                'continue' => 'Seguir escuchando música',
                'indexRoute' => 'music.index',
                'theme' => 'blue',
                'quote' => 'Tus canciones favoritas en un solo lugar.',
            ],
            'juegos' => [
                'label' => 'Videojuegos',
                'favorites' => 'Mis videojuegos favoritos',
                'list' => 'Mis listas de videojuegos',
                'pending' => 'Videojuegos pendientes',
                'continue' => 'Seguir jugando',
                'indexRoute' => 'games.index',
                'theme' => 'teal',
                'quote' => 'Tus videojuegos favoritos en un solo lugar.',
            ],
        };
    }
}
