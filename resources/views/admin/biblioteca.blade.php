@extends('layouts.admin')

@section('title', $currentSection['label'] ?? 'Biblioteca · ANIMOJI')

@section('content')
@php
    $singular = $section === 'juegos' ? 'videojuego' : ($section === 'musica' ? 'canción' : $section);
    $fieldSets = [
        'anime' => [
            ['name' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true],
            ['name' => 'synopsis', 'label' => 'Sinopsis', 'type' => 'textarea'],
            ['name' => 'cover', 'label' => 'Portada (ruta o URL)', 'type' => 'text', 'hint' => 'Ej.: /images/galeria/Naruto.jpg'],
            ['name' => 'studio', 'label' => 'Estudio', 'type' => 'text'],
            ['name' => 'year', 'label' => 'Año', 'type' => 'number', 'min' => 1900, 'max' => 2100],
            ['name' => 'status', 'label' => 'Estado', 'type' => 'select', 'options' => ['ongoing' => 'En emisión / En curso', 'finished' => 'Finalizado', 'upcoming' => 'Próximamente']],
            ['name' => 'rating', 'label' => 'Valoración (0-10)', 'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 0.1],
        ],
        'manga' => [
            ['name' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true],
            ['name' => 'synopsis', 'label' => 'Sinopsis', 'type' => 'textarea'],
            ['name' => 'cover', 'label' => 'Portada (ruta o URL)', 'type' => 'text', 'hint' => 'Ej.: /images/galeria/Berserk.jpg'],
            ['name' => 'author', 'label' => 'Autor', 'type' => 'text'],
            ['name' => 'artist', 'label' => 'Artista', 'type' => 'text'],
            ['name' => 'year', 'label' => 'Año', 'type' => 'number', 'min' => 1900, 'max' => 2100],
            ['name' => 'status', 'label' => 'Estado', 'type' => 'select', 'options' => ['ongoing' => 'En emisión / En curso', 'finished' => 'Finalizado', 'upcoming' => 'Próximamente']],
            ['name' => 'rating', 'label' => 'Valoración (0-10)', 'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 0.1],
        ],
        'juegos' => [
            ['name' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true],
            ['name' => 'description', 'label' => 'Descripción', 'type' => 'textarea'],
            ['name' => 'cover', 'label' => 'Portada (ruta o URL)', 'type' => 'text', 'hint' => 'Ej.: /images/galeria/elden.jpg'],
            ['name' => 'platforms', 'label' => 'Plataformas', 'type' => 'text', 'hint' => 'Separadas por coma: PC, PS5, Xbox'],
            ['name' => 'release_year', 'label' => 'Año de lanzamiento', 'type' => 'number', 'min' => 1970, 'max' => 2100],
            ['name' => 'rating', 'label' => 'Valoración (0-5)', 'type' => 'number', 'min' => 0, 'max' => 5, 'step' => 0.1],
            ['name' => 'download_url', 'label' => 'URL de descarga', 'type' => 'url'],
            ['name' => 'requirements', 'label' => 'Requisitos', 'type' => 'textarea'],
        ],
        'musica' => [
            ['name' => 'title', 'label' => 'Título', 'type' => 'text', 'required' => true],
            ['name' => 'artist', 'label' => 'Artista o banda', 'type' => 'text', 'required' => true],
            ['name' => 'album', 'label' => 'Álbum', 'type' => 'text'],
            ['name' => 'cover', 'label' => 'Portada (ruta o URL)', 'type' => 'text', 'hint' => 'Ej.: /images/galeria/portada.jpg'],
            ['name' => 'audio_url', 'label' => 'URL del audio', 'type' => 'url'],
            ['name' => 'audio_file', 'label' => 'Archivo de audio (máx. 50 MB)', 'type' => 'file', 'accept' => '.mp3,.ogg,.wav,.m4a,.aac,.flac'],
            ['name' => 'cover_file', 'label' => 'Imagen de la canción (JPG, PNG, WEBP · máx. 4 MB)', 'type' => 'file', 'accept' => 'image/*'],
            ['name' => 'duration', 'label' => 'Duración (segundos)', 'type' => 'number', 'min' => 0, 'max' => 86400],
            ['name' => 'release_year', 'label' => 'Año', 'type' => 'number', 'min' => 1900, 'max' => 2100],
        ],
    ];
    $fields = $fieldSets[$section];
    $detailFields = array_values(array_filter(
        $fields,
        fn (array $field): bool => ! in_array($field['name'], ['title', 'cover', 'audio_file', 'cover_file'], true),
    ));
    $oldRecordId = old('_record_id');
    $formAction = $oldRecordId
        ? route('admin.biblioteca.update', [$section, $oldRecordId])
        : route('admin.biblioteca.store', $section);
@endphp

<header class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-blue-400/80">Administración</p>
        <h1 class="mt-1 font-display text-3xl italic font-bold text-white sm:text-4xl">Biblioteca</h1>
        <p class="mt-1 text-sm text-gray-400">Gestiona el catálogo completo: crear, consultar, editar y eliminar registros.</p>
    </div>
    <button type="button" data-open-create class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-[0_0_18px_rgba(37,99,235,0.45)] transition hover:bg-blue-500">
        ➕ Nuevo {{ $singular }}
    </button>
</header>

@if (session('success'))
    <div role="status" class="mt-4 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-[220px_1fr]">
    <nav class="flex gap-2 overflow-x-auto lg:flex-col lg:overflow-visible" aria-label="Secciones de la biblioteca">
        @foreach ($sections as $availableSection)
            <a href="{{ route('admin.biblioteca', ['section' => $availableSection['key']]) }}" @class([
                'flex shrink-0 items-center justify-between gap-3 rounded-xl border px-4 py-2.5 text-sm font-medium transition',
                'border-blue-400/60 bg-blue-500/15 text-white shadow-[0_0_16px_rgba(59,130,246,0.35)]' => $availableSection['key'] === $section,
                'border-white/10 text-gray-300 hover:border-white/25 hover:bg-white/5 hover:text-white' => $availableSection['key'] !== $section,
            ]) @if ($availableSection['key'] === $section) aria-current="page" @endif>
                <span>{{ $availableSection['label'] }}</span>
                <span @class([
                    'rounded-md px-1.5 py-0.5 text-[10px] font-bold',
                    'bg-blue-500/30 text-blue-200' => $availableSection['key'] === $section,
                    'bg-white/10 text-gray-400' => $availableSection['key'] !== $section,
                ])>{{ $availableSection['count'] }}</span>
            </a>
        @endforeach
    </nav>

    <section class="min-w-0 rounded-2xl border border-white/10 bg-[#0a1220] p-5">
        <form method="GET" action="{{ route('admin.biblioteca', ['section' => $section]) }}" class="flex flex-wrap items-center gap-3">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Buscar en {{ $currentSection['label'] }} por título..." aria-label="Buscar en {{ $currentSection['label'] }}" class="w-64 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-gray-200 placeholder-gray-500 outline-none transition focus:border-blue-400/50">
            <button type="submit" class="rounded-xl border border-white/15 px-4 py-2 text-sm text-gray-300 transition hover:border-white/40 hover:text-white">Buscar</button>
            <span class="text-xs text-gray-500 sm:ml-auto">Orden: A → Z</span>
            <span class="text-xs text-gray-500">{{ $items->total() }} registro(s)</span>
        </form>

        @if ($items->count())
            <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                @foreach ($items as $row)
                    @php
                        $subtitle = match ($section) {
                            'anime' => $row->studio,
                            'manga' => $row->author,
                            'musica' => $row->artist,
                            'juegos' => is_array($row->platforms) ? implode(', ', $row->platforms) : $row->platforms,
                        };
                        $year = $row->year ?? $row->release_year;
                        $cover = $row->cover ?: '/images/'.$section.'/cover-1.jpg';
                        $typeLabel = match ($section) {
                            'anime' => 'Serie',
                            'manga' => 'Manga',
                            'musica' => 'Música',
                            'juegos' => 'Videojuego',
                        };
                        $statusLabel = match ($row->status ?? null) {
                            'ongoing' => $section === 'manga' ? 'En publicación' : 'En emisión',
                            'upcoming' => 'Próximamente',
                            'finished' => 'Finalizado',
                            default => null,
                        };
                    @endphp
                    <button
                        type="button"
                        data-open-detail
                        data-record="{{ base64_encode(json_encode($row->toArray(), JSON_THROW_ON_ERROR)) }}"
                        data-update-url="{{ route('admin.biblioteca.update', [$section, $row->id]) }}"
                        data-destroy-url="{{ route('admin.biblioteca.destroy', [$section, $row->id]) }}"
                        data-cover="{{ $cover }}"
                        class="group relative isolate flex aspect-[2/3] w-full flex-col justify-end overflow-hidden rounded-xl border border-white/10 bg-[#0d0d1a] text-left transition duration-300 hover:-translate-y-1 hover:border-blue-400/60 hover:shadow-[0_0_22px_rgba(56,189,248,0.25)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
                        aria-label="Ver detalles de {{ $row->title }}"
                    >
                        <img src="{{ $cover }}" alt="" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        <span class="absolute inset-0 -z-10 bg-gradient-to-t from-black via-black/45 to-black/10"></span>
                        <span class="absolute right-2 top-2 flex max-w-[calc(100%-1rem)] flex-wrap justify-end gap-1.5">
                            <span class="rounded-full bg-blue-600 px-2.5 py-1 text-[10px] font-bold text-white shadow sm:text-xs">{{ $typeLabel }}</span>
                            @if ($statusLabel)
                                <span class="rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-bold text-white shadow sm:text-xs">{{ $statusLabel }}</span>
                            @elseif ($row->rating !== null)
                                <span class="rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-bold text-white shadow sm:text-xs">★ {{ $row->rating }}</span>
                            @elseif ($year)
                                <span class="rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-bold text-white shadow sm:text-xs">{{ $year }}</span>
                            @endif
                        </span>
                        <span class="block line-clamp-3 px-3 text-left text-base font-semibold leading-tight text-white drop-shadow sm:px-4 sm:text-lg">{{ $row->title }}</span>
                        <span class="block truncate px-3 pb-3 pt-1 text-xs text-gray-100 drop-shadow sm:px-4 sm:pb-4 sm:text-sm">
                            {{ $subtitle ?: $currentSection['label'] }}@if ($year) · {{ $year }}@endif
                        </span>
                    </button>
                @endforeach
            </div>
        @else
            <p class="mt-10 rounded-2xl border border-dashed border-white/15 p-10 text-center text-sm text-gray-400">
                @if ($filters['q'] ?? '')
                    No se encontraron resultados para «{{ $filters['q'] }}» en {{ $currentSection['label'] }}.
                @else
                    Aún no hay registros en {{ $currentSection['label'] }}. Pulsa «Nuevo {{ $singular }}» para crear el primero.
                @endif
            </p>
        @endif

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    </section>
</div>

<dialog id="library-detail-dialog" class="w-[min(760px,94vw)] max-h-[90vh] overflow-hidden rounded-2xl border border-white/15 bg-[#0b1220] p-0 text-gray-100 shadow-[0_0_60px_rgba(0,0,0,0.7)] backdrop:bg-black/75">
    <div class="flex max-h-[90vh] flex-col">
        <div class="flex items-center justify-between border-b border-white/10 px-5 py-3.5">
            <h2 id="library-detail-title" class="truncate pr-4 font-display text-2xl italic font-bold text-white"></h2>
            <button type="button" data-close-detail class="shrink-0 text-gray-400 transition hover:text-white" aria-label="Cerrar">✕</button>
        </div>
        <div class="grid gap-5 overflow-y-auto p-5 sm:grid-cols-[220px_1fr]">
            <img id="library-detail-cover" src="" alt="" class="aspect-[3/4] w-full rounded-xl border border-white/10 bg-black/30 object-cover">
            <dl id="library-detail-fields" class="grid content-start gap-3"></dl>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-white/10 px-5 py-4">
            <form id="library-detail-delete" method="POST" class="confirm-delete" data-confirm="">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl border border-red-500/30 px-4 py-2 text-sm text-red-300 transition hover:border-red-400 hover:bg-red-500/10">🗑 Eliminar</button>
            </form>
            <button id="library-detail-edit" type="button" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">✏ Editar</button>
        </div>
    </div>
</dialog>

<dialog id="library-form-dialog" class="w-[min(640px,94vw)] max-h-[90vh] overflow-hidden rounded-2xl border border-white/15 bg-[#0b1220] p-0 text-gray-100 shadow-[0_0_60px_rgba(0,0,0,0.7)] backdrop:bg-black/75">
    <form id="library-form" method="POST" action="{{ $formAction }}" enctype="multipart/form-data" class="flex max-h-[90vh] flex-col">
        @csrf
        <input id="library-method" type="hidden" name="_method" value="{{ old('_method', 'POST') }}">
        <input id="library-record-id" type="hidden" name="_record_id" value="{{ old('_record_id', '') }}">
        <div class="flex items-center justify-between border-b border-white/10 px-5 py-3.5">
            <h2 id="library-form-title" class="font-display text-xl italic font-bold text-white">Nuevo {{ $singular }}</h2>
            <button type="button" data-close-dialog class="text-gray-400 transition hover:text-white" aria-label="Cerrar">✕</button>
        </div>
        <div class="flex-1 space-y-4 overflow-y-auto p-5">
            @foreach ($fields as $field)
                <div class="{{ $field['type'] === 'textarea' ? '' : 'grid gap-4 sm:grid-cols-2' }}">
                    <div class="{{ $field['type'] === 'textarea' ? '' : 'contents' }}">
                        <div>
                            <label for="library-{{ $field['name'] }}" class="mb-1.5 block text-sm font-semibold text-gray-300">
                                {{ $field['label'] }}
                                @if ($field['required'] ?? false)<span class="text-red-400" aria-hidden="true">*</span>@endif
                            </label>
                            @if ($field['type'] === 'textarea')
                                <textarea id="library-{{ $field['name'] }}" name="{{ $field['name'] }}" rows="3" @required($field['required'] ?? false) aria-invalid="{{ $errors->has($field['name']) ? 'true' : 'false' }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 outline-none transition focus:border-blue-400/50">{{ old($field['name']) }}</textarea>
                            @elseif ($field['type'] === 'select')
                                <select id="library-{{ $field['name'] }}" name="{{ $field['name'] }}" class="w-full rounded-xl border border-white/10 bg-[#0b1220] px-4 py-2.5 text-sm text-white outline-none transition focus:border-blue-400/50">
                                    <option value="">— Sin especificar —</option>
                                    @foreach ($field['options'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old($field['name']) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @elseif ($field['type'] === 'file')
                                <input id="library-{{ $field['name'] }}" name="{{ $field['name'] }}" type="file" accept="{{ $field['accept'] ?? '.mp3,.ogg,.wav,.m4a,.aac,.flac' }}" aria-invalid="{{ $errors->has($field['name']) ? 'true' : 'false' }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-gray-200 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-600 file:px-3 file:py-1.5 file:text-white">
                            @else
                                <input id="library-{{ $field['name'] }}" name="{{ $field['name'] }}" type="{{ $field['type'] }}" value="{{ old($field['name']) }}" @if (isset($field['min'])) min="{{ $field['min'] }}" @endif @if (isset($field['max'])) max="{{ $field['max'] }}" @endif @if (isset($field['step'])) step="{{ $field['step'] }}" @endif @required($field['required'] ?? false) placeholder="{{ $field['hint'] ?? '' }}" aria-invalid="{{ $errors->has($field['name']) ? 'true' : 'false' }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition focus:border-blue-400/50">
                            @endif
                            @if ($field['hint'] ?? false)<p class="mt-1 text-[11px] text-gray-500">{{ $field['hint'] }}</p>@endif
                            @error($field['name'])<p class="mt-1 text-xs font-semibold text-red-400">{{ $message }}</p>@enderror
                        </div>
                        @if ($field['name'] === 'cover')
                            <div class="hidden sm:block">
                                <p class="mb-1.5 text-sm font-semibold text-gray-300">Vista previa</p>
                                <img id="library-cover-preview" src="{{ old('cover') }}" alt="Vista previa de la portada" class="h-24 w-36 rounded-lg border border-white/15 object-cover" @if (! old('cover')) hidden @endif>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-white/10 px-5 py-4">
            <button type="button" data-close-dialog class="rounded-xl border border-white/15 px-4 py-2 text-sm text-gray-300 transition hover:bg-white/5">Cancelar</button>
            <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-500">Guardar</button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const dialog = document.getElementById('library-form-dialog');
        const detailDialog = document.getElementById('library-detail-dialog');
        const detailTitle = document.getElementById('library-detail-title');
        const detailCover = document.getElementById('library-detail-cover');
        const detailFields = document.getElementById('library-detail-fields');
        const detailDelete = document.getElementById('library-detail-delete');
        const detailEdit = document.getElementById('library-detail-edit');
        const form = document.getElementById('library-form');
        const method = document.getElementById('library-method');
        const recordId = document.getElementById('library-record-id');
        const title = document.getElementById('library-form-title');
        const storeUrl = @js(route('admin.biblioteca.store', $section));
        const singular = @js($singular);
        const section = @js($section);
        const fields = @js($detailFields);

        const openDetail = (button) => {
            const row = JSON.parse(atob(button.dataset.record));
            detailTitle.textContent = row.title;
            detailCover.src = button.dataset.cover;
            detailCover.alt = row.title;
            detailFields.replaceChildren();

            for (const field of fields) {
                const wrapper = document.createElement('div');
                const label = document.createElement('dt');
                const value = document.createElement('dd');
                const fieldValue = row[field.name];

                label.className = 'text-[11px] font-bold uppercase tracking-wider text-blue-300/80';
                label.textContent = field.label;
                value.className = 'mt-1 whitespace-pre-line break-words text-sm text-gray-200';
                value.textContent = fieldValue === null || fieldValue === undefined || fieldValue === ''
                    ? '—'
                    : (Array.isArray(fieldValue) ? fieldValue.join(', ') : String(fieldValue));

                if (field.type === 'select' && Object.hasOwn(field.options, fieldValue)) {
                    value.textContent = field.options[fieldValue];
                }

                wrapper.append(label, value);
                detailFields.append(wrapper);
            }

            detailDelete.action = button.dataset.destroyUrl;
            detailDelete.dataset.confirm = `¿Eliminar «${row.title}»? Esta acción no se puede deshacer.`;
            detailEdit.dataset.record = button.dataset.record;
            detailEdit.dataset.updateUrl = button.dataset.updateUrl;
            detailDialog.showModal();
        };

        const openCreate = () => {
            form.reset();
            form.action = storeUrl;
            method.value = 'POST';
            recordId.value = '';
            title.textContent = `Nuevo ${singular}`;
            const preview = document.getElementById('library-cover-preview');
            if (preview) {
                preview.src = '';
                preview.hidden = true;
            }
            dialog.showModal();
        };

        const openEdit = (button) => {
            const row = JSON.parse(atob(button.dataset.record));
            form.reset();
            form.action = button.dataset.updateUrl;
            method.value = 'PUT';
            recordId.value = row.id;
            title.textContent = `Editar ${singular}`;

            for (const [name, value] of Object.entries(row)) {
                const input = form.elements.namedItem(name);
                if (!input || input.type === 'file') continue;
                input.value = name === 'platforms' && Array.isArray(value)
                    ? value.join(', ')
                    : (value ?? '');
            }

            const preview = document.getElementById('library-cover-preview');
            if (preview) {
                preview.src = row.cover ?? '';
                preview.hidden = !row.cover;
            }

            dialog.showModal();
        };

        document.querySelector('[data-open-create]')?.addEventListener('click', openCreate);
        document.querySelectorAll('[data-open-detail]').forEach((button) => {
            button.addEventListener('click', () => openDetail(button));
        });
        document.querySelectorAll('[data-open-edit]').forEach((button) => {
            button.addEventListener('click', () => openEdit(button));
        });
        detailEdit.addEventListener('click', () => {
            const editButton = {
                dataset: {
                    record: detailEdit.dataset.record,
                    updateUrl: detailEdit.dataset.updateUrl,
                },
            };
            detailDialog.close();
            openEdit(editButton);
        });
        document.querySelector('[data-close-detail]')?.addEventListener('click', () => detailDialog.close());
        document.querySelectorAll('[data-close-dialog]').forEach((button) => {
            button.addEventListener('click', () => dialog.close());
        });
        document.querySelectorAll('.confirm-delete').forEach((form) => {
            form.addEventListener('submit', (event) => {
                if (!confirm(form.dataset.confirm)) event.preventDefault();
            });
        });
        document.getElementById('library-cover')?.addEventListener('input', (event) => {
            const preview = document.getElementById('library-cover-preview');
            if (!preview) return;
            preview.src = event.target.value;
            preview.hidden = !event.target.value;
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
        detailDialog.addEventListener('click', (event) => {
            if (event.target === detailDialog) detailDialog.close();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && dialog.open) dialog.close();
            if (event.key === 'Escape' && detailDialog.open) detailDialog.close();
        });

        @if ($errors->any())
            @if ($oldRecordId)
                document.getElementById('library-form-title').textContent = `Editar ${singular}`;
            @else
                document.getElementById('library-form-title').textContent = `Nuevo ${singular}`;
            @endif
            dialog.showModal();
        @endif
    })();
</script>
@endsection
