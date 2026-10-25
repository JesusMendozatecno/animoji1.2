@extends('layouts.catalog')

@section('title', $config['label'].' · ANIMOJI')

@section('content')
@php
    $theme = match ($section) {
        'manga' => [
            'card' => 'bg-[#101010]',
            'borderHover' => 'hover:border-white/40',
            'glow' => 'hover:shadow-[0_0_22px_rgba(255,255,255,0.12)]',
            'highlight' => 'text-white',
            'badge' => 'bg-white text-black',
        ],
        'musica' => [
            'card' => 'bg-[#0e1226]',
            'borderHover' => 'hover:border-blue-500/60',
            'glow' => 'hover:shadow-[0_0_25px_rgba(37,99,235,0.35)]',
            'highlight' => 'text-blue-400',
            'badge' => 'bg-blue-600 text-white shadow-[0_0_14px_rgba(37,99,235,0.55)]',
        ],
        'juegos' => [
            'card' => 'bg-[#071614]',
            'borderHover' => 'hover:border-teal-500/60',
            'glow' => 'hover:shadow-[0_0_25px_rgba(20,184,166,0.35)]',
            'highlight' => 'text-teal-400',
            'badge' => 'bg-teal-600 text-white shadow-[0_0_14px_rgba(20,184,166,0.55)]',
        ],
        default => [
            'card' => 'bg-[#10101a]',
            'borderHover' => 'hover:border-red-500/60',
            'glow' => 'hover:shadow-[0_0_25px_rgba(255,38,60,0.35)]',
            'highlight' => 'text-red-500',
            'badge' => 'bg-red-500 text-white shadow-[0_0_14px_rgba(255,38,60,0.55)]',
        ],
    };
@endphp

<div class="grid gap-6 xl:grid-cols-[1fr_320px]">
    <div class="min-w-0">
        <section class="relative overflow-hidden rounded-2xl border border-white/10">
            <img src="/images/{{ $section }}/hero.jpg" alt="" class="absolute inset-0 h-full w-full object-cover {{ $section === 'manga' ? 'opacity-80' : '' }}">
            <div class="absolute inset-0 bg-gradient-to-r from-[#0d0a16] via-[#0d0a16]/80 to-transparent"></div>
            <div class="relative flex flex-col gap-4 p-8 sm:p-10">
                <span class="w-fit rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-gray-200">
                    {{ $config['heroBadge'] }}
                </span>
                <h1 class="max-w-xl font-display text-4xl font-bold italic leading-tight text-white sm:text-5xl">
                    {{ $config['heroTitle'] }}
                </h1>
                <p class="max-w-lg text-sm leading-relaxed text-gray-300">{{ $config['heroDescription'] }}</p>
                <div class="mt-2 flex flex-wrap gap-3">
                    <a href="{{ route($config['indexRoute']) }}" class="inline-flex items-center gap-2 rounded-full px-6 py-2.5 text-sm font-semibold transition hover:scale-105 {{ $theme['badge'] }}">
                        Explorar {{ $config['label'] }}
                    </a>
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="inline-flex items-center gap-2 rounded-full border border-white/25 px-6 py-2.5 text-sm font-semibold text-gray-200 transition hover:border-white/50 hover:bg-white/5">
                        Mi Biblioteca
                    </a>
                </div>
                <p class="pointer-events-none absolute bottom-8 right-8 hidden max-w-[11rem] text-right text-lg italic leading-snug text-gray-300 lg:block">
                    {{ $config['quote'] }}
                </p>
            </div>
        </section>

        <section class="mt-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $config['heading'] }}</h2>
                    <p class="text-xs text-gray-400">{{ $config['subheading'] }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2" aria-label="Filtros decorativos">
                    @foreach ($config['filters'] as $index => $filter)
                        <span @class([
                            'rounded-full px-4 py-1.5 text-sm font-medium',
                            $theme['badge'] => $index === 0,
                            'border border-white/15 text-gray-300' => $index !== 0,
                        ])>{{ $filter }}</span>
                    @endforeach
                </div>
            </div>

            @if ($items->isEmpty())
                <div class="mt-5 rounded-2xl border border-dashed border-white/15 bg-white/[0.02] px-6 py-14 text-center">
                    <h3 class="text-lg font-semibold text-white">Todavía no hay contenido</h3>
                    <p class="mt-1 text-sm text-gray-400">Los nuevos títulos de {{ $config['label'] }} aparecerán aquí.</p>
                </div>
            @else
                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6">
                    @foreach ($items as $index => $item)
                        @php
                            $cover = $item['cover'] ?: '/images/'.$section.'/cover-'.(($index % 8) + 1).'.jpg';
                            $status = $item['status'] ?? null;
                            $statusLabel = match ($status) {
                                'ongoing' => $section === 'manga' ? 'En publicación' : 'En emisión',
                                null, '' => null,
                                default => $section === 'manga' ? 'Finalizado' : 'Completado',
                            };
                            $typeLabel = match ($section) {
                                'anime' => 'Serie',
                                'manga' => 'Manga',
                                'musica' => 'Música',
                                'juegos' => 'Videojuego',
                            };
                            $secondaryBadge = match ($section) {
                                'anime', 'manga' => $statusLabel,
                                'musica' => $item['subtitle'] ?? null,
                                'juegos' => $item['platform'] ?? null,
                            };
                        @endphp
                        <article class="group relative isolate aspect-[2/3] overflow-hidden rounded-xl border border-white/10 {{ $theme['card'] }} transition duration-300 hover:-translate-y-1 {{ $theme['borderHover'] }} {{ $theme['glow'] }}">
                            <a href="{{ $item['url'] }}" class="absolute inset-0 block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-white">
                                <img src="{{ $cover }}" alt="{{ $item['title'] }}" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105 {{ $section === 'manga' ? 'grayscale group-hover:grayscale-0' : '' }}">
                                <span class="absolute inset-0 bg-gradient-to-t from-black via-black/35 to-black/10"></span>
                                <span class="absolute right-2 top-2 flex max-w-[calc(100%-1rem)] flex-wrap justify-end gap-1.5">
                                    <span class="rounded-full bg-blue-600 px-2.5 py-1 text-[10px] font-bold text-white shadow sm:text-xs">{{ $typeLabel }}</span>
                                    @if ($secondaryBadge)
                                        <span class="max-w-full truncate rounded-full bg-amber-500 px-2.5 py-1 text-[10px] font-bold text-white shadow sm:text-xs">{{ $secondaryBadge }}</span>
                                    @endif
                                </span>
                                @if ($item['rating'] !== null)
                                    <span class="absolute bottom-[4.5rem] right-2 rounded-full bg-black/75 px-2.5 py-1 text-xs font-bold text-amber-200">★ {{ number_format((float) $item['rating'], 1) }}</span>
                                @endif
                                <span class="absolute inset-x-0 bottom-0 block p-3 sm:p-4">
                                    <span class="block line-clamp-3 font-semibold leading-tight text-white drop-shadow sm:text-lg">{{ $item['title'] }}</span>
                                    <span class="mt-1 block truncate text-xs text-gray-200 drop-shadow">
                                        @if ($section === 'anime' || $section === 'manga')
                                            {{ $item['year'] ?? '—' }} · {{ $item['subtitle'] ?? ($section === 'manga' ? 'Autor pendiente' : 'Estudio pendiente') }}
                                        @elseif ($section === 'musica')
                                            {{ $item['subtitle'] ?? 'Artista pendiente' }}
                                        @else
                                            {{ $item['year'] ?? 'Próximamente' }} · {{ $item['platform'] ?? 'PC' }}
                                        @endif
                                    </span>
                                </span>
                            </a>
                            @if ($section === 'musica' && $item['audio_url'])
                                <button
                                    type="button"
                                    data-play-track="{{ $item['id'] }}"
                                    class="absolute bottom-3 left-3 z-10 grid h-9 w-9 place-items-center rounded-full bg-blue-600 text-sm font-semibold text-white shadow-lg transition hover:bg-blue-500 sm:bottom-4 sm:left-4"
                                    aria-label="Reproducir {{ $item['title'] }}"
                                >▶</button>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        @if (! empty($config['playlists']))
            <section class="mt-10">
                <h2 class="text-xl font-bold text-white">Playlists destacadas</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($config['playlists'] as $playlist)
                        <article class="overflow-hidden rounded-xl border border-white/10 bg-[#0e1226]">
                            <img src="{{ $playlist['image'] }}" alt="" loading="lazy" class="h-32 w-full object-cover">
                            <div class="p-4">
                                <h3 class="font-semibold text-white">{{ $playlist['name'] }}</h3>
                                <p class="mt-0.5 text-xs text-gray-400">{{ $playlist['genre'] }}</p>
                                <p class="mt-1 text-xs text-blue-300">♫ {{ $playlist['count'] }} canciones</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if (! empty($config['genres']))
            <section class="mt-10">
                <h2 class="text-xl font-bold text-white">Géneros</h2>
                <p class="text-xs text-gray-400">Elige tu estilo de juego favorito</p>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($config['genres'] as $genre)
                        <article class="overflow-hidden rounded-xl border border-white/10 bg-[#071614]">
                            <img src="{{ $genre['image'] }}" alt="{{ $genre['name'] }}" loading="lazy" class="h-32 w-full object-cover">
                            <div class="p-4">
                                <h3 class="font-semibold text-white">{{ $genre['name'] }}</h3>
                                <p class="mt-0.5 text-xs text-gray-400">{{ $genre['description'] }}</p>
                                <p class="mt-1 text-xs text-teal-300">🎮 {{ $genre['count'] }} juegos</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <aside class="space-y-6">
        <section class="rounded-2xl border border-white/10 {{ $theme['card'] }} p-5">
            <div class="flex items-center gap-2">
                <span class="text-xl text-amber-300">★</span>
                <h2 class="font-bold text-white">Recomendados para ti</h2>
            </div>
            <p class="mt-0.5 text-xs text-gray-400">{{ $config['recommended'] }}</p>
            <ul class="mt-4 flex flex-col gap-3">
                @foreach ($items->take(5) as $index => $item)
                    @php
                        $cover = $item['cover'] ?: '/images/'.$section.'/reco-'.(($index % 5) + 1).'.jpg';
                    @endphp
                    <li>
                        <a href="{{ $item['url'] }}" class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/[0.03] p-2.5 transition hover:border-white/25 hover:bg-white/5">
                            <img src="{{ $cover }}" alt="" loading="lazy" class="h-12 w-12 shrink-0 rounded-lg object-cover">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-white">{{ $item['title'] }}</span>
                                <span class="block truncate text-[11px] text-gray-400">{{ $item['subtitle'] ?? $item['platform'] ?? $config['label'] }}</span>
                                @if ($item['rating'] !== null)
                                    <span class="mt-0.5 block text-[11px] text-amber-300">★ {{ number_format((float) $item['rating'], 1) }}</span>
                                @endif
                            </span>
                            <span class="text-gray-500">›</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="grid grid-cols-3 gap-3 rounded-2xl border border-white/10 {{ $theme['card'] }} p-5 text-center">
            <div>
                <p class="text-sm font-bold text-white">+{{ number_format($stats['total']) }}</p>
                <p class="text-[11px] text-gray-400">{{ $config['totalLabel'] }}</p>
            </div>
            <div>
                <p class="text-sm font-bold text-white">+{{ number_format($stats['users']) }}</p>
                <p class="text-[11px] text-gray-400">Usuarios</p>
            </div>
            <div>
                <p class="text-sm font-bold text-white">{{ number_format($stats['avgRating'], 1) }}</p>
                <p class="text-[11px] text-gray-400">Promedio</p>
            </div>
        </section>
    </aside>
</div>
@endsection
