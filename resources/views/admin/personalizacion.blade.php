@extends('layouts.admin')

@section('title', 'Personalización de sistema · Admin')

@section('content')
@php
    $tab = request()->query('tab', 'imagenes') === 'direcciones' ? 'direcciones' : 'imagenes';
    $customizing = (bool) $user->customizing;
@endphp

<section @class([
    'overflow-hidden rounded-2xl border p-6 transition',
    'border-sky-400/60 bg-sky-500/10 shadow-[0_0_30px_rgba(56,189,248,0.25)]' => $customizing,
    'border-white/10 bg-[#0a1220]' => ! $customizing,
])>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
        <div class="flex-1">
            <h1 class="font-display text-3xl italic font-bold text-white">Personalización de sistema</h1>
            <p class="mt-1 text-sm text-gray-400">Imágenes de las zonas del sistema y direcciones de todos los botones del sitio.</p>
        </div>
        <form method="POST" action="{{ route('admin.customizations.toggle') }}">
            @csrf
            @method('PATCH')
            <button type="submit" @class([
                'inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold text-white transition hover:scale-105',
                'bg-gradient-to-r from-red-600 to-rose-500 shadow-[0_0_22px_rgba(255,38,60,0.5)]' => $customizing,
                'bg-gradient-to-r from-sky-600 to-blue-500 shadow-[0_0_22px_rgba(56,189,248,0.5)]' => ! $customizing,
            ])>
                {{ $customizing ? '■ Desactivar modo imágenes' : '🖼️ Personalizar imágenes de sistema' }}
            </button>
        </form>
    </div>

    <nav class="mt-5 flex flex-wrap gap-2" aria-label="Opciones de personalización">
        <a href="{{ route('admin.customizations.index', ['tab' => 'imagenes']) }}" @class([
            'rounded-lg px-4 py-2 text-sm font-semibold transition',
            'bg-white/15 text-white' => $tab === 'imagenes',
            'text-gray-400 hover:text-white' => $tab !== 'imagenes',
        ]) @if ($tab === 'imagenes') aria-current="page" @endif>🖼️ Imágenes del sistema</a>
        <a href="{{ route('admin.customizations.index', ['tab' => 'direcciones']) }}" @class([
            'rounded-lg px-4 py-2 text-sm font-semibold transition',
            'bg-white/15 text-white' => $tab === 'direcciones',
            'text-gray-400 hover:text-white' => $tab !== 'direcciones',
        ]) @if ($tab === 'direcciones') aria-current="page" @endif>🧭 Direcciones</a>
    </nav>

    @if (session('success'))
        <div role="status" class="mt-4 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
    @endif
    @if (session('info'))
        <div role="status" class="mt-4 rounded-xl border border-sky-400/30 bg-sky-500/10 px-4 py-3 text-sm text-sky-200">{{ session('info') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="mt-4 rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    @if ($customizing && $tab === 'imagenes')
        <div class="mt-4 rounded-xl border border-sky-400/40 bg-black/30 px-4 py-3 text-sm text-sky-200">
            ✅ <strong>Modo imágenes ACTIVO.</strong> Ve a cualquier página y pulsa <strong>“➕ Agregar imagen”</strong> sobre cada zona para elegir una imagen de la galería, ajustarla y guardarla.
        </div>
    @endif
</section>

@if ($tab === 'imagenes')
    <p class="mt-8 text-[11px] font-bold uppercase tracking-[0.3em] text-gray-500">Zonas de imagen del sistema</p>
    <div class="mt-4 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($zones as $zone => $info)
            @php
                $customization = $customizations[$zone] ?? null;
            @endphp
            <article @class([
                'overflow-hidden rounded-2xl border bg-[#0a1220] transition hover:border-sky-400/40',
                'border-sky-400/50' => $customization,
                'border-white/10' => ! $customization,
            ])>
                <div class="relative h-40 overflow-hidden">
                    <img
                        src="{{ $customization['image'] ?? $info['preview'] }}"
                        alt="{{ $info['label'] }}"
                        class="h-full w-full object-cover"
                        @if ($customization)
                            style="object-position: {{ $customization['x'] }}% {{ $customization['y'] }}%; transform: scale({{ $customization['scale'] }})"
                        @endif
                    >
                    <span @class([
                        'absolute left-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider backdrop-blur',
                        'bg-sky-500/80 text-white' => $customization,
                        'bg-black/60 text-gray-300' => ! $customization,
                    ])>{{ $customization ? '✨ Personalizada' : 'Imagen por defecto' }}</span>
                </div>
                <div class="p-4">
                    <h2 class="text-sm font-semibold text-white">{{ $info['label'] }}</h2>
                    <p class="mt-1 font-mono text-[11px] text-gray-500">{{ $zone }}</p>
                    <p class="mt-2 text-xs text-gray-400">Activa “Personalizar imágenes de sistema” y busca el botón “➕ Agregar imagen” sobre esta zona.</p>
                </div>
            </article>
        @endforeach
    </div>
@else
    <div class="mt-6 rounded-xl border border-white/10 bg-[#0a1220] px-4 py-3 text-sm text-gray-400">
        🧭 <strong>Direcciones:</strong> Cada botón o sección muestra adónde lleva ahora. Pulsa <strong>Editar</strong>, selecciona un nuevo destino y guarda. Los cambios se aplican en todo el sistema.
    </div>

    @foreach ($navSections as $sectionKey => $sectionLabel)
        <section class="mt-8">
            <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-sky-400">{{ $sectionLabel }}</p>
            <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($navItems->get($sectionKey, collect()) as $item)
                    @php
                        $isCustom = array_key_exists($item['key'], $navOverrides);
                        $currentPath = $isCustom ? $navOverrides[$item['key']] : $item['default'];
                        $hasDestination = $currentPath !== \App\Support\Navigation::NO_DESTINATION;
                        $isEditing = old('_editing_key') === $item['key'];
                    @endphp
                    <article @class([
                        'rounded-xl border bg-[#0a1220] p-4 transition',
                        'border-sky-400/60 shadow-[0_0_16px_rgba(56,189,248,0.2)]' => $isCustom,
                        'border-white/10' => ! $isCustom,
                    ])>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="truncate text-sm font-semibold text-white">{{ $item['label'] }}</h2>
                                <p class="mt-1 truncate text-xs">
                                    <span class="text-gray-500">Lleva a:</span>
                                    <span @class(['font-semibold', 'text-sky-300' => $isCustom, 'text-gray-300' => ! $isCustom])>{{ $hasDestination ? ($destinations[$currentPath] ?? $currentPath) : 'Ninguna dirección' }}</span>
                                </p>
                                @if ($hasDestination)
                                    <p class="mt-0.5 font-mono text-[10px] text-gray-600">{{ $currentPath }}</p>
                                @endif
                            </div>
                            @unless ($isEditing)
                                <button type="button" data-edit-route="{{ $item['key'] }}" class="shrink-0 rounded-lg border border-white/15 px-3 py-1.5 text-xs font-semibold text-gray-300 transition hover:border-sky-400/60 hover:text-white">✏️ Editar</button>
                            @endunless
                        </div>

                        <form method="POST" action="{{ route('admin.direcciones.update', $item['key']) }}" @class(['mt-3 border-t border-white/10 pt-3', 'hidden' => ! $isEditing]) data-route-form="{{ $item['key'] }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_editing_key" value="{{ $item['key'] }}">
                            <label for="destination-{{ str_replace('.', '-', $item['key']) }}" class="text-[11px] uppercase tracking-wider text-gray-500">Nueva dirección</label>
                            <select id="destination-{{ str_replace('.', '-', $item['key']) }}" name="path" class="mt-1 w-full rounded-lg border border-white/15 bg-[#080d18] px-3 py-2 text-sm text-gray-100 outline-none focus:border-sky-400/60">
                                <option value="{{ \App\Support\Navigation::NO_DESTINATION }}" @selected($currentPath === \App\Support\Navigation::NO_DESTINATION)>Ninguna dirección — deja el elemento sin enlace</option>
                                @foreach ($destinations as $path => $name)
                                    <option value="{{ $path }}" @selected($currentPath === $path)>{{ $name }} — {{ $path }}</option>
                                @endforeach
                            </select>
                            @error('path')
                                @if ($isEditing)
                                    <p class="mt-1 text-xs font-semibold text-red-400">{{ $message }}</p>
                                @endif
                            @enderror
                            <div class="mt-3 flex flex-wrap gap-2">
                                <button type="submit" class="rounded-lg bg-gradient-to-r from-sky-600 to-blue-500 px-4 py-1.5 text-xs font-bold text-white shadow-[0_0_16px_rgba(56,189,248,0.4)] transition hover:scale-105">💾 Guardar</button>
                                @if ($isCustom)
                                    <button type="submit" form="restore-{{ str_replace('.', '-', $item['key']) }}" class="rounded-lg border border-red-500/40 px-4 py-1.5 text-xs font-semibold text-red-400 transition hover:bg-red-500/10">↩ Restaurar por defecto</button>
                                @endif
                                <button type="button" data-cancel-route="{{ $item['key'] }}" class="rounded-lg border border-white/15 px-4 py-1.5 text-xs font-semibold text-gray-400 transition hover:text-white">Cancelar</button>
                            </div>
                        </form>
                        @if ($isCustom)
                            <form id="restore-{{ str_replace('.', '-', $item['key']) }}" method="POST" action="{{ route('admin.direcciones.update', $item['key']) }}" class="hidden">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="path" value="">
                            </form>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endforeach
@endif

<script>
    (() => {
        document.querySelectorAll('[data-edit-route]').forEach((button) => {
            button.addEventListener('click', () => {
                const key = button.dataset.editRoute;
                document.querySelector(`[data-route-form="${CSS.escape(key)}"]`)?.classList.remove('hidden');
                button.classList.add('hidden');
            });
        });
        document.querySelectorAll('[data-cancel-route]').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelector(`[data-route-form="${CSS.escape(button.dataset.cancelRoute)}"]`)?.classList.add('hidden');
                document.querySelector(`[data-edit-route="${CSS.escape(button.dataset.cancelRoute)}"]`)?.classList.remove('hidden');
            });
        });

        @if ($tab === 'direcciones' && old('_editing_key'))
            document.querySelector(`[data-route-form="${CSS.escape(@js(old('_editing_key')))}"]`)?.classList.remove('hidden');
        @endif
    })();
</script>
@endsection
