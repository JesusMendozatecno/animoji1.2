@extends('layouts.admin')

@section('title', 'Galería de imágenes · ANIMOJI')

@section('content')
@php
    $extensions = [
        'jpg' => ['chip' => 'bg-pink-500/15 text-pink-300 ring-pink-400/40', 'icon' => '🖼'],
        'jpeg' => ['chip' => 'bg-pink-500/15 text-pink-300 ring-pink-400/40', 'icon' => '🖼'],
        'png' => ['chip' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/40', 'icon' => '🎞'],
        'webp' => ['chip' => 'bg-violet-500/15 text-violet-300 ring-violet-400/40', 'icon' => '🎞'],
        'gif' => ['chip' => 'bg-amber-500/15 text-amber-300 ring-amber-400/40', 'icon' => '✨'],
    ];
    $query = $filters['q'] ?? '';
@endphp

<section class="relative overflow-hidden rounded-2xl border border-white/10">
    <img src="/images/galeria/banner.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-[#07070f] via-[#07070f]/60 to-transparent"></div>
    <div class="relative flex flex-wrap items-center justify-between gap-6 p-7 sm:p-9">
        <div>
            <div class="flex items-center gap-3">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-sky-500/20 text-2xl ring-1 ring-sky-400/50">📷</span>
                <h1 class="font-display text-4xl italic tracking-wide text-white sm:text-5xl" style="transform: skewX(-6deg)">
                    GALERÍA DE <span class="text-sky-400 [text-shadow:0_0_18px_rgba(56,189,248,0.7)]">IMÁGENES</span>
                </h1>
            </div>
            <p class="mt-3 max-w-md text-sm text-gray-300">
                Un espacio para guardar lo que me inspira.<br>
                Anime, música, manga, videojuegos... y todo lo que me hace feliz.
            </p>
            <div class="mt-3 flex gap-1.5" aria-hidden="true">
                @for ($index = 0; $index < 6; $index++)
                    <span class="h-1 w-4 -skew-x-[20deg] bg-red-500"></span>
                @endfor
            </div>
        </div>
        <p class="hidden max-w-[10rem] text-right text-base italic leading-snug text-gray-200 lg:block">
            "La pasión<br>también<br>se ve..."
        </p>
    </div>
</section>

@if (session('success'))
    <div role="status" class="mt-4 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div role="alert" class="mt-4 rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
@endif

<div class="mt-6 flex flex-wrap items-center gap-3">
    <form method="GET" action="{{ route('admin.galeria') }}" class="flex flex-wrap items-center gap-3">
        <input
            name="q"
            type="search"
            value="{{ $query }}"
            placeholder="Buscar imágenes por nombre..."
            aria-label="Buscar imágenes por nombre"
            class="w-64 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-gray-200 placeholder-gray-500 outline-none transition focus:border-sky-400/50"
        >
        <button type="submit" class="rounded-xl border border-white/15 px-4 py-2 text-sm text-gray-300 transition hover:border-white/40 hover:text-white">Buscar</button>
    </form>

    <span class="text-xs text-gray-500 sm:ml-auto">Orden: A → Z (ignora mayúsculas)</span>
    <button type="button" data-gallery-upload-open class="rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-[0_0_18px_rgba(14,165,233,0.4)] transition hover:bg-sky-500">
        ➕ Nueva imagen
    </button>
</div>

@if ($items->count())
    <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
        @foreach ($items as $item)
            @php
                $style = $extensions[$item['ext']] ?? $extensions['jpg'];
                $fileName = rawurldecode(basename(parse_url($item['image'], PHP_URL_PATH)));
                $sizeLabel = $item['size'] < 1024
                    ? $item['size'].' B'
                    : ($item['size'] < 1024 * 1024
                        ? number_format($item['size'] / 1024, 1).' KB'
                        : number_format($item['size'] / (1024 * 1024), 1).' MB');
            @endphp
            <article class="group relative isolate aspect-[2/3] overflow-hidden rounded-xl border border-white/10 bg-[#0d0d1a] transition hover:-translate-y-1 hover:border-sky-400/50 hover:shadow-[0_0_22px_rgba(56,189,248,0.25)]">
                <button type="button" data-gallery-image-open data-image="{{ $item['image'] }}" data-name="{{ $item['name'] }}" data-rename-url="{{ route('admin.galeria.rename', ['file' => $fileName]) }}" data-delete-url="{{ route('admin.galeria.destroy', ['file' => $fileName]) }}" class="absolute inset-0 h-full w-full cursor-zoom-in focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-sky-400" aria-label="Ver y editar {{ $item['name'] }}">
                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    <span class="absolute inset-0 bg-gradient-to-t from-black via-black/35 to-black/10"></span>
                    <span class="absolute inset-x-0 bottom-0 block p-3 text-left sm:p-4">
                        <span class="block line-clamp-3 text-base font-semibold leading-tight text-white drop-shadow sm:text-lg">{{ $item['name'] }}</span>
                        <span class="mt-1 block truncate text-[11px] text-gray-200 drop-shadow">{{ $item['date'] }} · {{ $sizeLabel }}</span>
                    </span>
                </button>
                <span class="pointer-events-none absolute right-2 top-2 rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 sm:text-xs {{ $style['chip'] }}">{{ strtoupper($item['ext']) }}</span>
                <button
                    type="button"
                    data-gallery-delete-open
                    data-delete-url="{{ route('admin.galeria.destroy', ['file' => $fileName]) }}"
                    data-delete-name="{{ $item['name'] }}"
                    class="absolute left-2 top-2 z-10 grid h-8 w-8 place-items-center rounded-full bg-black/70 text-red-400 opacity-100 transition hover:bg-red-600 hover:text-white sm:opacity-0 sm:group-hover:opacity-100 sm:focus:opacity-100"
                    aria-label="Eliminar {{ $item['name'] }}"
                >🗑</button>
            </article>
        @endforeach
    </div>
@else
    <p class="mt-10 rounded-2xl border border-dashed border-white/15 p-10 text-center text-sm text-gray-400">
        @if ($query !== '')
            No se encontraron imágenes con «{{ $query }}».
        @else
            No hay imágenes en la galería todavía. Pulsa «Nueva imagen» para agregar la primera.
        @endif
    </p>
@endif

<div class="mt-6">
    {{ $items->links() }}
</div>

<div id="gallery-upload-modal" class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 z-[95] grid place-items-center bg-black/75 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Subir nueva imagen" data-gallery-modal>
    <form method="POST" action="{{ route('admin.galeria.store', array_filter(['q' => $query ?: null])) }}" enctype="multipart/form-data" class="flex max-h-[88vh] w-[min(560px,94vw)] flex-col overflow-hidden rounded-2xl border border-white/15 bg-[#0b1220] shadow-[0_0_60px_rgba(0,0,0,0.7)]">
        @csrf
        <div class="flex items-center justify-between border-b border-white/10 px-5 py-3.5">
            <h2 class="font-display text-xl italic font-bold text-white">Nueva imagen</h2>
            <button type="button" data-gallery-modal-close class="text-gray-400 transition hover:text-white" aria-label="Cerrar">✕</button>
        </div>
        <div class="flex-1 space-y-4 overflow-y-auto p-5">
            <div class="grid aspect-[16/10] place-items-center overflow-hidden rounded-xl border-2 border-dashed border-white/20 bg-black/30" data-gallery-preview-container>
                <img src="" alt="Vista previa" class="hidden h-full w-full object-contain" data-gallery-preview>
                <div class="text-center" data-gallery-preview-empty>
                    <p class="text-3xl">🖼</p>
                    <p class="mt-2 text-sm text-gray-400">Ningún archivo seleccionado</p>
                </div>
            </div>
            <div>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" aria-label="Seleccionar archivo de imagen" class="block w-full text-sm text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-sky-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-sky-500" data-gallery-file>
                <p class="mt-1.5 text-[11px] text-gray-500">Formatos: JPG, PNG, WebP o GIF · Tamaño máximo: 5 MB</p>
                @error('image')
                    <p role="alert" class="mt-1 text-xs font-semibold text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap gap-2 text-[11px] text-gray-300" data-gallery-file-info></div>
            <div>
                <label for="gallery-image-name" class="mb-1.5 block text-sm font-semibold text-gray-300">Nombre de la imagen <span class="text-red-400">*</span></label>
                <input id="gallery-image-name" name="name" type="text" maxlength="80" value="{{ old('name') }}" placeholder="Ej.: Portada Naruto" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 outline-none transition focus:border-sky-400/50" @if ($errors->has('name')) aria-invalid="true" @endif>
                @error('name')
                    <p role="alert" class="mt-1 text-xs font-semibold text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-white/10 px-5 py-4">
            <button type="button" data-gallery-modal-close class="rounded-xl border border-white/15 px-4 py-2.5 text-sm text-gray-300 transition hover:border-white/40 hover:text-white">Cancelar</button>
            <button type="submit" class="rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500">Guardar imagen</button>
        </div>
    </form>
</div>

<div id="gallery-image-modal" class="hidden fixed inset-0 z-[95] place-items-center bg-black/80 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="gallery-image-modal-title" data-gallery-modal>
    <div class="flex max-h-[92vh] w-[min(900px,96vw)] flex-col overflow-hidden rounded-2xl border border-white/15 bg-[#0b1220] shadow-[0_0_60px_rgba(0,0,0,0.7)]">
        <div class="flex items-center justify-between gap-4 border-b border-white/10 px-5 py-3.5">
            <h2 id="gallery-image-modal-title" class="font-display text-xl italic font-bold text-white">Imagen de la galería</h2>
            <button type="button" data-gallery-modal-close class="shrink-0 text-gray-400 transition hover:text-white" aria-label="Cerrar">✕</button>
        </div>
        <div class="grid min-h-0 gap-5 overflow-y-auto p-5 md:grid-cols-[minmax(0,1fr)_260px]">
            <div class="grid min-h-64 place-items-center overflow-hidden rounded-xl bg-black/40 md:min-h-[min(65vh,640px)]">
                <img src="" alt="" data-gallery-image-preview class="max-h-[65vh] max-w-full object-contain">
            </div>
            <div class="space-y-3">
                <label for="gallery-rename-input" class="block text-sm font-semibold text-gray-200">Nombre de la imagen</label>
                <input id="gallery-rename-input" type="text" maxlength="80" autocomplete="off" readonly data-gallery-rename-input class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition focus:border-sky-400/60 read-only:cursor-default read-only:opacity-80" aria-describedby="gallery-rename-help gallery-rename-status">
                <p id="gallery-rename-help" class="text-xs leading-relaxed text-gray-500">Pulsa Editar para cambiar el nombre y Guardar para confirmar. Se conservará la extensión del archivo.</p>
                <p id="gallery-rename-status" class="min-h-5 text-xs text-gray-400" role="status" aria-live="polite" data-gallery-rename-status></p>
            </div>
        </div>
        <div class="flex justify-end border-t border-white/10 px-5 py-4">
            <button type="button" data-gallery-rename-submit class="rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-500 disabled:cursor-wait disabled:opacity-60">Editar</button>
        </div>
    </div>
</div>

<div id="gallery-delete-modal" class="hidden fixed inset-0 z-[95] grid place-items-center bg-black/75 p-4 backdrop-blur-sm" role="alertdialog" aria-modal="true" aria-label="Confirmar eliminación de imagen" data-gallery-modal>
    <div class="w-[min(440px,94vw)] rounded-2xl border border-white/15 bg-[#0b1220] p-6 shadow-[0_0_60px_rgba(0,0,0,0.7)]">
        <div class="flex items-center gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-red-500/15 text-xl ring-1 ring-red-400/40">🗑</span>
            <h2 class="text-lg font-bold text-white">Eliminar imagen</h2>
        </div>
        <p class="mt-4 text-sm leading-relaxed text-gray-300">Vas a eliminar <strong class="text-white" data-gallery-delete-name></strong> de la galería. Esta acción no se puede deshacer.</p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" data-gallery-modal-close class="rounded-xl border border-white/15 px-4 py-2.5 text-sm text-gray-300 transition hover:border-white/40 hover:text-white">Cancelar</button>
            <form method="POST" action="" data-gallery-delete-form>
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">Sí, eliminar</button>
            </form>
        </div>
    </div>
</div>

<script>
    (() => {
        let previewUrl = '';
        const uploadModal = document.getElementById('gallery-upload-modal');
        const deleteModal = document.getElementById('gallery-delete-modal');
        const imageModal = document.getElementById('gallery-image-modal');
        const renameInput = document.querySelector('[data-gallery-rename-input]');
        const renameStatus = document.querySelector('[data-gallery-rename-status]');
        const renameButton = document.querySelector('[data-gallery-rename-submit]');
        const imagePreview = document.querySelector('[data-gallery-image-preview]');
        const fileInput = document.querySelector('[data-gallery-file]');
        const preview = document.querySelector('[data-gallery-preview]');
        const previewEmpty = document.querySelector('[data-gallery-preview-empty]');
        const fileInfo = document.querySelector('[data-gallery-file-info]');
        let activeImageButton = null;
        let isEditingImageName = false;

        const closeModal = (modal) => {
            if (modal === imageModal) {
                isEditingImageName = false;
                renameInput.readOnly = true;
                renameButton.textContent = 'Editar';
                renameButton.disabled = false;
            }
            modal?.classList.add('hidden');
            if (modal === uploadModal && previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = '';
                preview.src = '';
                preview.classList.add('hidden');
                previewEmpty.classList.remove('hidden');
            }
        };

        document.querySelector('[data-gallery-upload-open]')?.addEventListener('click', () => uploadModal.classList.remove('hidden'));
        document.querySelectorAll('[data-gallery-image-open]').forEach((button) => {
            button.addEventListener('click', () => {
                activeImageButton = button;
                imagePreview.src = button.dataset.image;
                imagePreview.alt = button.dataset.name;
                renameInput.value = button.dataset.name;
                renameInput.readOnly = true;
                renameButton.textContent = 'Editar';
                renameButton.disabled = false;
                isEditingImageName = false;
                renameStatus.textContent = '';
                imageModal.classList.remove('hidden');
                imageModal.classList.add('grid');
                renameButton.focus();
            });
        });
        document.querySelectorAll('[data-gallery-modal-close]').forEach((button) => {
            button.addEventListener('click', () => closeModal(button.closest('[data-gallery-modal]')));
        });
        document.querySelectorAll('[data-gallery-modal]').forEach((modal) => {
            modal.addEventListener('click', (event) => {
                if (event.target === modal) closeModal(modal);
            });
        });
        document.querySelectorAll('[data-gallery-delete-open]').forEach((button) => {
            button.addEventListener('click', () => {
                deleteModal.querySelector('[data-gallery-delete-name]').textContent = `«${button.dataset.deleteName}»`;
                deleteModal.querySelector('[data-gallery-delete-form]').action = button.dataset.deleteUrl;
                deleteModal.classList.remove('hidden');
            });
        });
        fileInput?.addEventListener('change', () => {
            const file = fileInput.files?.[0];
            if (!file) return;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            preview.src = previewUrl;
            preview.classList.remove('hidden');
            previewEmpty.classList.add('hidden');
            fileInfo.innerHTML = '';
            [file.name, file.type.split('/').pop()?.toUpperCase() ?? '—', file.size < 1024 * 1024 ? `${(file.size / 1024).toFixed(1)} KB` : `${(file.size / (1024 * 1024)).toFixed(1)} MB`]
                .forEach((value) => {
                    const badge = document.createElement('span');
                    badge.className = 'rounded-md bg-white/5 px-2 py-1 text-gray-300';
                    badge.textContent = value;
                    fileInfo.append(badge);
                });

            const nameInput = document.getElementById('gallery-image-name');
            if (!nameInput.value.trim()) nameInput.value = file.name.replace(/\.[^.]+$/, '').slice(0, 80);
        });
        const saveImageName = async () => {
            if (!activeImageButton) return;
            const imageButton = activeImageButton;
            const requestedName = renameInput.value.trim().replace(/\s+/g, ' ');
            if (!requestedName) {
                renameStatus.textContent = 'El nombre no puede estar vacío.';
                return;
            }

            renameStatus.textContent = 'Guardando nombre...';
            renameButton.disabled = true;
            renameInput.readOnly = true;
            try {
                const response = await fetch(imageButton.dataset.renameUrl, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ name: requestedName }),
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result.errors?.name?.[0] ?? result.message ?? 'No se pudo guardar el nombre.');
                }
                if (typeof result.list_url !== 'string') {
                    throw new Error('No se pudo encontrar la posición de la imagen en la galería.');
                }

                renameStatus.textContent = 'Nombre guardado. Buscando la imagen en la galería...';
                window.location.assign(result.list_url);
            } catch (error) {
                renameStatus.textContent = error instanceof Error ? error.message : 'No se pudo guardar el nombre.';
                renameButton.disabled = false;
                renameInput.readOnly = false;
            }
        };
        renameButton?.addEventListener('click', () => {
            if (!isEditingImageName) {
                isEditingImageName = true;
                renameInput.readOnly = false;
                renameButton.textContent = 'Guardar';
                renameInput.focus();
                renameInput.select();
                return;
            }

            saveImageName();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            if (!deleteModal.classList.contains('hidden')) closeModal(deleteModal);
            else if (!uploadModal.classList.contains('hidden')) closeModal(uploadModal);
            else if (!imageModal.classList.contains('hidden')) closeModal(imageModal);
        });
    })();
</script>
@endsection
