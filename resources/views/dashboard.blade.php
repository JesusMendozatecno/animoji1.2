@extends('layouts.user')

@section('title', 'Presentación · ANIMOJI')

@section('content')
@php
    $tiles = [
        ['key' => 'anime', 'title' => 'Anime', 'sub' => 'Series y películas', 'img' => '/images/usuario/tile-anime.jpg', 'route' => 'anime.index', 'icon' => 'M4 4h16v12H4zm2 14h12v2H6z', 'accent' => 'border-pink-500/70 shadow-[0_0_18px_rgba(236,72,153,0.4)]'],
        ['key' => 'musica', 'title' => 'Música', 'sub' => 'Canciones y artistas', 'img' => '/images/usuario/tile-musica.jpg', 'route' => 'music.index', 'icon' => 'M12 3v10.55A4 4 0 1 0 14 17V7h4V3z', 'accent' => 'border-violet-500/70 shadow-[0_0_18px_rgba(139,92,246,0.4)]'],
        ['key' => 'manga', 'title' => 'Manga', 'sub' => 'Historias en viñetas', 'img' => '/images/usuario/tile-manga.jpg', 'route' => 'manga.index', 'icon' => 'M4 4h7v16H4zm9 0h7v16h-7z', 'accent' => 'border-blue-500/70 shadow-[0_0_18px_rgba(59,130,246,0.4)]'],
        ['key' => 'juegos', 'title' => 'Videojuegos', 'sub' => 'Juega y disfruta', 'img' => '/images/usuario/tile-juegos.jpg', 'route' => 'games.index', 'icon' => 'M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h6l2 2h2l2-2h6a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z', 'accent' => 'border-emerald-500/70 shadow-[0_0_18px_rgba(34,197,94,0.4)]'],
    ];
    $fallbackCover = fn (int $index) => '/images/usuario/cover-'.(($index % 5) + 1).'.jpg';
@endphp

<header>
    <h1 class="text-4xl font-bold text-white">Presentación</h1>
    <p class="mt-2 text-sm text-gray-400">Explora, disfruta y vive la pasión del anime, la música, el manga y los videojuegos.</p>
</header>

<div class="mt-6 grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">
    @foreach ($tiles as $tile)
        <a href="{{ route($tile['route']) }}" class="group relative isolate flex items-center justify-between gap-3 overflow-hidden rounded-2xl border-2 bg-[#0d0d1a] p-4 transition hover:-translate-y-1 {{ $tile['accent'] }}">
            <span class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 text-white ring-1 ring-white/20">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $tile['icon'] }}"></path></svg>
                </span>
                <span>
                    <span class="block text-base font-bold text-white">{{ $tile['title'] }}</span>
                    <span class="block text-xs text-gray-500">{{ $tile['sub'] }}</span>
                </span>
            </span>
            <img src="{{ $tile['img'] }}" alt="{{ $tile['title'] }}" class="h-14 w-20 rounded-lg object-cover opacity-80 transition duration-500 group-hover:scale-105 group-hover:opacity-100">
        </a>
    @endforeach
</div>

<section class="mt-8 rounded-3xl border border-white/10 bg-[#0d0d1a] p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-violet-500/20 text-violet-300 ring-1 ring-violet-400/40" aria-hidden="true">📺</span>
            <div>
                <h2 class="text-2xl font-bold text-white">Anime</h2>
                <p class="text-xs text-gray-500">Tus series favoritas, siempre a tu alcance.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="rounded-xl border border-white/15 px-4 py-2 text-sm text-gray-300 transition hover:border-white/40">⚙ Filtrar</button>
            <button type="button" aria-label="Vista de cuadrícula" class="grid h-9 w-9 place-items-center rounded-xl bg-violet-600 text-white">▦</button>
            <button type="button" aria-label="Vista de lista" class="grid h-9 w-9 place-items-center rounded-xl border border-white/15 text-gray-400">☰</button>
        </div>
    </div>

    <div class="relative mt-5">
        <button type="button" data-carousel-scroll="-1" aria-label="Anterior" class="absolute -left-3 top-[40%] z-10 grid h-10 w-10 place-items-center rounded-full border border-white/20 bg-[#0d0d1a] text-gray-300 transition hover:border-violet-400/60 hover:text-white">‹</button>
        <div id="anime-carousel" class="no-scrollbar flex gap-5 overflow-x-auto scroll-smooth px-1 py-1">
            @forelse (array_slice($items, 0, 8) as $index => $item)
                <article class="w-[240px] shrink-0 overflow-hidden rounded-2xl border border-white/10 bg-black/40 transition hover:-translate-y-1 hover:border-violet-400/50 hover:shadow-[0_0_20px_rgba(139,92,246,0.3)]">
                    <a href="{{ route('anime.show', $item['slug']) }}">
                        <div class="relative aspect-[3/4] overflow-hidden">
                            <img src="{{ $item['cover'] ?? $fallbackCover($index) }}" alt="{{ $item['title'] }}" class="h-full w-full object-cover transition duration-500 hover:scale-105">
                            <span class="absolute left-2 top-2 rounded-md bg-violet-600 px-2 py-0.5 text-[11px] font-bold text-white">TV</span>
                        </div>
                        <div class="p-3.5">
                            <p class="truncate font-semibold text-white">{{ $item['title'] }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $item['year'] ?? '—' }} · {{ $item['status'] === 'ongoing' ? 'En emisión' : 'Completado' }}</p>
                            <div class="mt-2 flex items-center justify-between">
                                <span class="flex items-center gap-1 text-sm font-bold text-amber-400">★ {{ number_format((float) ($item['rating'] ?? 0), 1) }}</span>
                                <span class="text-gray-600" aria-hidden="true">⋮</span>
                            </div>
                        </div>
                    </a>
                </article>
            @empty
                <p class="w-full py-16 text-center text-sm text-gray-500">No hay títulos en el catálogo todavía.</p>
            @endforelse
        </div>
        <button type="button" data-carousel-scroll="1" aria-label="Siguiente" class="absolute -right-3 top-[40%] z-10 grid h-10 w-10 place-items-center rounded-full border border-white/20 bg-[#0d0d1a] text-gray-300 transition hover:border-violet-400/60 hover:text-white">›</button>
    </div>

    <div class="mt-5 flex justify-center gap-2" aria-hidden="true">
        <span class="h-2 w-8 rounded-full bg-violet-500"></span>
        <span class="h-2 w-2 rounded-full bg-gray-600"></span>
        <span class="h-2 w-2 rounded-full bg-gray-600"></span>
        <span class="h-2 w-2 rounded-full bg-gray-600"></span>
    </div>
</section>

@push('scripts')
    <script>
        document.querySelectorAll('[data-carousel-scroll]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('anime-carousel')?.scrollBy({
                    left: Number(button.dataset.carouselScroll) * 340,
                    behavior: 'smooth',
                });
            });
        });
    </script>
@endpush
@endsection
