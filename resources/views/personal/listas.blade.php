@extends('layouts.catalog')

@section('title', $config['list'].' · ANIMOJI')

@section('content')
<header class="mb-6">
    <h1 class="font-display text-3xl font-bold italic text-white">{{ $config['list'] }}</h1>
    <p class="mt-1 text-sm text-gray-400">
        Crea listas con el nombre que quieras y añade contenidos de {{ $config['label'] }}.
    </p>
</header>

@if (session('success'))
    <p role="status" class="mb-4 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</p>
@endif
@if (session('error'))
    <p role="alert" class="mb-4 rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</p>
@endif
@if (session('warning'))
    <p role="status" class="mb-4 rounded-xl border border-amber-400/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">{{ session('warning') }}</p>
@endif

<form method="POST" action="{{ route($section.'.listas.store') }}" class="mb-6 flex flex-wrap items-start gap-3">
    @csrf
    <div class="min-w-64 flex-1">
        <label for="new-list-name" class="sr-only">Nombre de la nueva lista</label>
        <input
            id="new-list-name"
            name="name"
            type="text"
            value="{{ old('name') }}"
            maxlength="60"
            required
            placeholder="Nombre de la nueva lista (p. ej. «Animes que ya vi»)"
            class="block w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:border-white/30 focus:outline-none focus:ring-0"
        >
        @error('name')
            <p class="mt-2 text-sm text-red-300">{{ $message }}</p>
        @enderror
    </div>
    <button type="submit" class="rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-gray-950 transition hover:bg-gray-200">
        + Crear lista
    </button>
</form>

@if ($lists->isEmpty())
    <div class="rounded-2xl border border-dashed border-white/15 bg-white/[0.02] px-6 py-14 text-center">
        <p class="text-4xl">📋</p>
        <h2 class="mt-3 text-lg font-semibold text-white">Crea tu primera lista</h2>
        <p class="mx-auto mt-1 max-w-md text-sm text-gray-400">
            Tus listas de {{ $config['label'] }} aparecerán aquí. Son privadas: solo tú las ves.
        </p>
    </div>
@else
    <div class="grid gap-3 lg:grid-cols-2">
        @foreach ($lists as $list)
            @php
                $isSelected = $selectedId === $list['id'];
            @endphp
            <article @class([
                'rounded-2xl border bg-white/[0.03] p-4 transition',
                'border-white/40 bg-white/[0.07]' => $isSelected,
                'border-white/10 hover:border-white/25' => ! $isSelected,
            ])>
                <div class="flex items-center gap-3">
                    <a
                        href="{{ route($section.'.listas', ['list' => $isSelected ? 0 : $list['id']]) }}"
                        class="min-w-0 flex-1 text-left"
                        @if ($isSelected) aria-current="true" @endif
                    >
                        <h2 class="truncate font-semibold text-white">{{ $list['name'] }}</h2>
                        <p class="text-xs text-gray-400">
                            {{ $list['count'] }} elemento{{ $list['count'] === 1 ? '' : 's' }}
                            · {{ $isSelected ? 'abierta' : 'abrir' }}
                        </p>
                    </a>
                    <details class="relative">
                        <summary class="cursor-pointer list-none rounded-full border border-white/15 px-3 py-2 text-xs text-gray-300 transition hover:border-white/40 hover:text-white">
                            Renombrar
                        </summary>
                        <form method="POST" action="{{ route('listas.update', $list['id']) }}" class="absolute right-0 z-10 mt-2 flex w-72 gap-2 rounded-xl border border-white/10 bg-[#10101a] p-3 shadow-xl">
                            @csrf
                            @method('PUT')
                            <label for="list-name-{{ $list['id'] }}" class="sr-only">Nuevo nombre para {{ $list['name'] }}</label>
                            <input
                                id="list-name-{{ $list['id'] }}"
                                name="name"
                                type="text"
                                value="{{ $list['name'] }}"
                                maxlength="60"
                                required
                                class="min-w-0 flex-1 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:border-white/30 focus:outline-none focus:ring-0"
                            >
                            <button type="submit" class="rounded-lg bg-white px-3 py-2 text-xs font-semibold text-gray-950 hover:bg-gray-200">Guardar</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('listas.destroy', $list['id']) }}" onsubmit="return confirm('¿Eliminar esta lista y todo su contenido?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Eliminar lista {{ $list['name'] }}" class="rounded-full border border-white/15 p-2 text-gray-400 transition hover:border-red-400/60 hover:text-red-300">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m-9 0 1 14h8l1-14"></path></svg>
                        </button>
                    </form>
                </div>

                @if ($isSelected)
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <form method="GET" action="{{ route($section.'.listas') }}" class="mb-4 flex gap-2">
                            <input type="hidden" name="list" value="{{ $list['id'] }}">
                            <label for="catalog-search" class="sr-only">Buscar contenido para añadir</label>
                            <input
                                id="catalog-search"
                                name="q"
                                type="search"
                                value="{{ $searchQuery }}"
                                maxlength="80"
                                minlength="2"
                                placeholder="Buscar en {{ $config['label'] }} para añadir…"
                                class="block min-w-0 w-full rounded-lg border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:border-white/30 focus:outline-none focus:ring-0"
                            >
                            <button type="submit" class="shrink-0 rounded-full border border-white/20 px-4 text-sm font-semibold text-gray-300 transition hover:border-white/50 hover:text-white">
                                Buscar
                            </button>
                        </form>

                        @if (mb_strlen($searchQuery) >= 2)
                            @if ($searchResults->isNotEmpty())
                                <div class="mb-4 flex flex-col gap-2">
                                    @foreach ($searchResults as $result)
                                        @php
                                            $resultCover = $result['cover'] ?? null;
                                            $resultCover = $resultCover
                                                ? (str_starts_with($resultCover, '/') || str_starts_with($resultCover, 'http') ? $resultCover : '/'.$resultCover)
                                                : '/images/'.$section.'/cover-1.jpg';
                                        @endphp
                                        <form method="POST" action="{{ route('listas.items.store', $list['id']) }}" class="flex items-center gap-3 rounded-xl border border-white/10 bg-black/30 p-2">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $result['id'] }}">
                                            <img src="{{ $resultCover }}" alt="" loading="lazy" class="h-10 w-10 rounded-lg object-cover">
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-medium text-white">{{ $result['title'] }}</span>
                                                <span class="block truncate text-xs text-gray-400">{{ $result['subtitle'] ?? '—' }}</span>
                                            </span>
                                            <button type="submit" class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-bold text-gray-950 hover:bg-gray-200">+ Añadir</button>
                                        </form>
                                    @endforeach
                                </div>
                            @else
                                <p class="mb-4 text-sm text-gray-400">Sin resultados para «{{ $searchQuery }}».</p>
                            @endif
                        @endif

                        @if (empty($list['items']))
                            <p class="text-sm text-gray-400">Esta lista está vacía. Busca contenido arriba para añadirlo.</p>
                        @else
                            <div class="flex flex-col gap-2">
                                @foreach ($list['items'] as $item)
                                    @php
                                        $cover = $item['cover'] ?? null;
                                        $cover = $cover
                                            ? (str_starts_with($cover, '/') || str_starts_with($cover, 'http') ? $cover : '/'.$cover)
                                            : '/images/'.$section.'/cover-1.jpg';
                                    @endphp
                                    <article class="group flex items-center gap-3 rounded-xl border border-white/10 bg-white/[0.02] p-2">
                                        @if ($item['url'])
                                            <a href="{{ $item['url'] }}" class="h-14 w-12 shrink-0 overflow-hidden rounded-lg bg-black/40">
                                                <img src="{{ $cover }}" alt="{{ $item['title'] }}" loading="lazy" class="h-full w-full object-cover">
                                            </a>
                                        @else
                                            <img src="{{ $cover }}" alt="{{ $item['title'] }}" loading="lazy" class="h-14 w-12 shrink-0 rounded-lg object-cover">
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            @if ($item['url'])
                                                <a href="{{ $item['url'] }}" class="block truncate text-sm font-medium text-white hover:text-gray-300">{{ $item['title'] }}</a>
                                            @else
                                                <p class="truncate text-sm font-medium text-white">{{ $item['title'] }}</p>
                                            @endif
                                            <p class="truncate text-xs text-gray-400">{{ $item['context'] ?? $item['subtitle'] ?? '—' }}</p>
                                        </div>
                                        <form method="POST" action="{{ route('listas.items.destroy', [$list['id'], $item['list_item_id']]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" aria-label="Quitar {{ $item['title'] }} de la lista" class="rounded-full border border-white/15 p-2 text-gray-400 transition hover:border-red-400/60 hover:text-red-300">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"></path></svg>
                                            </button>
                                        </form>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </article>
        @endforeach
    </div>
@endif
@endsection
