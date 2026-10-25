@extends('layouts.public')

@section('title', 'ANIMOJI · Tu mundo del anime, en un solo lugar')

@section('content')
@php
    $navigationOverrides = $navigationOverrides ?? [];
    $navUrl = fn (string $key, string $fallback): ?string => ($navigationOverrides[$key] ?? null) === \App\Support\Navigation::NO_DESTINATION
        ? null
        : ($navigationOverrides[$key] ?? $fallback);
    $indexTheme = $user?->index_theme;
    $imageBase = $indexTheme
        ? '/images/index/'.rawurlencode($indexTheme).'/'
        : '/images/index/';
    $customHero = $customizations['home.hero'] ?? null;
    $heroImage = $customHero['image'] ?? $imageBase.'hero-bg1.jpg';
    $categories = [
        ['title' => 'ANIME', 'description' => 'Explora los mejores animes, desde clásicos hasta lo más reciente.', 'route' => 'anime.index', 'key' => 'index.categoria_anime', 'image' => $imageBase.'anime.jpg', 'border' => 'border-sky-400/80', 'glow' => 'shadow-[0_0_20px_rgba(34,211,238,0.35)] hover:shadow-[0_0_35px_rgba(34,211,238,0.75)]', 'title_color' => 'text-sky-300', 'icon_bg' => 'from-sky-400 to-cyan-600', 'button' => 'border-sky-400/80 text-sky-300 hover:bg-sky-400/15', 'stripe' => 'bg-sky-400', 'icon' => 'M8 5v14l11-7z'],
        ['title' => 'MANGA', 'description' => 'Lee tus mangas favoritos y descubre nuevas historias.', 'route' => 'manga.index', 'key' => 'index.categoria_manga', 'image' => $imageBase.'manga.jpg', 'border' => 'border-red-500/80', 'glow' => 'shadow-[0_0_20px_rgba(255,38,60,0.35)] hover:shadow-[0_0_35px_rgba(255,38,60,0.75)]', 'title_color' => 'text-red-400', 'icon_bg' => 'from-red-500 to-rose-700', 'button' => 'border-red-500/80 text-red-300 hover:bg-red-500/15', 'stripe' => 'bg-red-500', 'icon' => 'M4 4h16v2H4zm0 4h16v12H4zm2 2v8h12v-8z'],
        ['title' => 'MÚSICA', 'description' => 'Disfruta de tus canciones y listas de reproducción favoritas.', 'route' => 'music.index', 'key' => 'index.categoria_musica', 'image' => $imageBase.'musica.jpg', 'border' => 'border-purple-500/80', 'glow' => 'shadow-[0_0_20px_rgba(168,85,247,0.35)] hover:shadow-[0_0_35px_rgba(168,85,247,0.75)]', 'title_color' => 'text-purple-400', 'icon_bg' => 'from-purple-500 to-fuchsia-700', 'button' => 'border-purple-500/80 text-purple-300 hover:bg-purple-500/15', 'stripe' => 'bg-purple-400', 'icon' => 'M12 3v10.55A4 4 0 1 0 14 17V7h4V3z'],
        ['title' => 'VIDEOJUEGOS', 'description' => 'Descubre, juega y vive la experiencia de tus videojuegos favoritos.', 'route' => 'games.index', 'key' => 'index.categoria_juegos', 'image' => $imageBase.'juegos.jpg', 'border' => 'border-teal-400/80', 'glow' => 'shadow-[0_0_20px_rgba(45,212,191,0.35)] hover:shadow-[0_0_35px_rgba(45,212,191,0.75)]', 'title_color' => 'text-teal-300', 'icon_bg' => 'from-teal-400 to-emerald-600', 'button' => 'border-teal-400/80 text-teal-300 hover:bg-teal-400/15', 'stripe' => 'bg-teal-400', 'icon' => 'M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h6l2 2h2l2-2h6a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z'],
    ];
    $featured = [
        ['ring' => 'border-red-500/80', 'shadow' => 'shadow-[0_0_18px_rgba(255,38,60,0.45)]'],
        ['ring' => 'border-sky-400/80', 'shadow' => 'shadow-[0_0_18px_rgba(56,189,248,0.45)]'],
        ['ring' => 'border-red-400/80', 'shadow' => 'shadow-[0_0_18px_rgba(248,113,113,0.45)]'],
        ['ring' => 'border-cyan-400/80', 'shadow' => 'shadow-[0_0_18px_rgba(34,211,238,0.45)]'],
        ['ring' => 'border-purple-400/80', 'shadow' => 'shadow-[0_0_18px_rgba(192,132,252,0.45)]'],
        ['ring' => 'border-pink-400/80', 'shadow' => 'shadow-[0_0_18px_rgba(244,114,182,0.45)]'],
        ['ring' => 'border-orange-400/80', 'shadow' => 'shadow-[0_0_18px_rgba(251,146,60,0.45)]'],
    ];
@endphp

<div class="relative isolate">
    <div class="fixed inset-0 -z-10 bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('{{ $imageBase }}hero-bg.jpg')"></div>
    <div class="fixed inset-0 -z-10 bg-black/65"></div>

    <section class="relative isolate min-h-[570px] overflow-hidden lg:min-h-[640px]">
        @if ($customHero)
            <div class="absolute inset-0 -z-20 overflow-hidden">
                <img src="{{ $heroImage }}" alt="" class="h-full w-full object-cover" style="object-position: {{ $customHero['x'] }}% {{ $customHero['y'] }}%; transform: scale({{ $customHero['scale'] }})">
            </div>
        @else
            <div class="absolute inset-0 -z-20 bg-cover bg-center" style="background-image: url('{{ $heroImage }}')"></div>
        @endif
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-black via-black/70 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 -z-10 h-40 bg-gradient-to-t from-gray-950 to-transparent"></div>

        @if ($customizing && $user?->hasRole('superadmin'))
            <a href="{{ route('admin.customizations.index') }}" class="absolute right-4 top-4 z-20 rounded-full border border-dashed border-white/70 bg-black/60 px-3 py-2 text-xs font-bold text-white">Ajustar imagen del hero</a>
        @endif

        <span class="particle left-[12%] top-[30%]"></span>
        <span class="particle left-[38%] top-[20%]" style="animation-delay:1.2s"></span>
        <span class="particle left-[64%] top-[55%]" style="animation-delay:2.4s"></span>
        <span class="particle left-[85%] top-[25%]" style="animation-delay:.6s"></span>

        <div class="relative mx-auto max-w-7xl px-4 pb-32 pt-16 sm:px-6 lg:px-8 lg:pb-40 lg:pt-24">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-red-400 [text-shadow:0_0_14px_rgba(255,40,60,0.8)]">Bienvenido a</p>
            <h1 class="mt-2 select-none font-display text-6xl italic leading-none tracking-wide text-white sm:text-7xl lg:text-8xl" style="transform: skewX(-8deg)">
                ANIMO<span class="text-red-500 [text-shadow:0_0_28px_rgba(255,38,60,0.9)]">JI</span>
            </h1>
            <p class="mt-4 max-w-xl text-lg text-gray-300 sm:text-xl">Tu mundo del anime, en un solo lugar.</p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                @if ($canLogin)
                    <a @if (($url = $navUrl('index.iniciar_sesion', route('login'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="group inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-red-600 to-red-500 px-7 py-3 font-semibold text-white shadow-[0_0_25px_rgba(255,38,60,0.55)] transition hover:scale-105 hover:shadow-[0_0_40px_rgba(255,38,60,0.85)]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"></path></svg>Inicio de Sección<span class="transition group-hover:translate-x-1">→</span>
                    </a>
                @endif
                <a @if (($url = $navUrl('index.mi_lista', route($user ? 'dashboard' : 'login'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="inline-flex items-center gap-2 rounded-full border border-sky-400/70 bg-sky-500/10 px-6 py-3 font-medium text-sky-300 transition hover:scale-105 hover:bg-sky-500/20 hover:shadow-[0_0_20px_rgba(56,189,248,0.5)]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"></path></svg>Mi Lista
                </a>
                <a @if (($url = $navUrl('index.recientes', route('anime.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="inline-flex items-center gap-2 rounded-full border border-sky-400/70 bg-sky-500/10 px-6 py-3 font-medium text-sky-300 transition hover:scale-105 hover:bg-sky-500/20 hover:shadow-[0_0_20px_rgba(56,189,248,0.5)]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm4.2 14.2L11 13V7h1.5v5.2l4.5 2.7z"></path></svg>Recientes
                </a>
            </div>
        </div>
    </section>

    <section class="relative z-10 mx-auto -mt-16 max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $category)
                @php($categoryUrl = $navUrl($category['key'], route($category['route'])))
                <a @if ($categoryUrl !== null) href="{{ $categoryUrl }}" @else aria-disabled="true" @endif class="clip-tech group relative isolate flex h-64 flex-col justify-between overflow-hidden border-2 bg-gray-950 p-5 transition duration-300 hover:-translate-y-2 {{ $category['border'] }} {{ $category['glow'] }}">
                    <div class="absolute inset-0 -z-10 bg-cover bg-center opacity-60 transition duration-500 group-hover:scale-105 group-hover:opacity-75" style="background-image: url('{{ $category['image'] }}')"></div>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-gray-950/95 via-gray-950/70 to-transparent"></div>
                    <div>
                        <span class="grid h-12 w-12 place-items-center rounded-full bg-gradient-to-br {{ $category['icon_bg'] }} text-white shadow-lg">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $category['icon'] }}"></path></svg>
                        </span>
                        <h2 class="mt-4 font-display text-3xl italic tracking-wide {{ $category['title_color'] }}" style="transform: skewX(-8deg)">{{ $category['title'] }}</h2>
                        <p class="mt-1 max-w-[16rem] text-sm leading-snug text-gray-300">{{ $category['description'] }}</p>
                    </div>
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm font-medium transition {{ $category['button'] }}">Ver más <span class="transition group-hover:translate-x-1">→</span></span>
                    <span class="absolute bottom-4 right-4 flex gap-1.5 opacity-80" aria-hidden="true">
                        <span class="h-5 w-1.5 -skew-x-[20deg] {{ $category['stripe'] }}"></span><span class="h-5 w-1.5 -skew-x-[20deg] {{ $category['stripe'] }}"></span><span class="h-5 w-1.5 -skew-x-[20deg] {{ $category['stripe'] }}"></span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h2 class="text-center font-display text-3xl italic font-bold tracking-wide text-white [text-shadow:0_0_18px_rgba(255,38,60,0.7)]" style="transform: skewX(-6deg)">7 CAPITALES</h2>
        <div class="relative mt-6">
            <button type="button" data-carousel-step="-1" aria-label="Anterior" class="absolute -left-2 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-red-500/80 bg-gray-950/90 text-red-400 shadow-[0_0_15px_rgba(255,38,60,0.45)] transition hover:bg-red-500/20 hover:text-white sm:-left-4">‹</button>
            <div id="featured-track" class="no-scrollbar flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth px-2 py-2">
                @foreach ($featured as $index => $item)
                    <a href="{{ route('anime.index') }}" class="clip-card group relative aspect-[16/23] w-[46%] shrink-0 snap-start overflow-hidden border-2 bg-gray-950 transition duration-300 hover:-translate-y-1.5 sm:w-[30%] md:w-[22%] lg:w-[calc((100%-5rem)/7)] {{ $item['ring'] }} {{ $item['shadow'] }}">
                        <div class="absolute inset-0 bg-cover bg-center transition duration-500 group-hover:scale-110" style="background-image: url('{{ $imageBase }}destacado-{{ $index + 1 }}.jpg')"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-transparent to-transparent"></div>
                        <span class="absolute bottom-2 left-1/2 grid h-8 w-8 -translate-x-1/2 place-items-center rounded-full border border-white/30 bg-black/70 text-sm font-bold text-white">{{ $index + 1 }}</span>
                    </a>
                @endforeach
            </div>
            <button type="button" data-carousel-step="1" aria-label="Siguiente" class="absolute -right-2 top-1/2 z-10 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full border border-red-500/80 bg-gray-950/90 text-red-400 shadow-[0_0_15px_rgba(255,38,60,0.45)] transition hover:bg-red-500/20 hover:text-white sm:-right-4">›</button>
        </div>
        <div id="featured-pages" class="mt-5 flex justify-center gap-2" aria-label="Páginas del carrusel"></div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="clip-banner flex flex-col items-center justify-between gap-6 border-2 border-red-500/80 bg-gray-950/90 px-6 py-6 shadow-[0_0_25px_rgba(255,38,60,0.35)] sm:flex-row sm:px-10">
            <div class="flex items-center gap-4">
                <img src="/images/index/logo.png" alt="ANIMOJI" class="h-14 w-14 drop-shadow-[0_0_10px_rgba(255,38,60,0.8)]">
                <div>
                    <h2 class="font-display text-2xl italic tracking-wide text-white sm:text-3xl" style="transform: skewX(-6deg)">QUE TE DICE EL ANIME</h2>
                    <p class="text-sm text-gray-400">Sinopsis, géneros, episodios, puntuación, canciones...</p>
                </div>
            </div>
            <a @if (($url = $navUrl('index.banner_explorar', route('anime.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="inline-flex shrink-0 items-center gap-2 rounded-full bg-gradient-to-r from-red-600 to-red-500 px-7 py-3 font-semibold text-white shadow-[0_0_20px_rgba(255,38,60,0.55)] transition hover:scale-105 hover:shadow-[0_0_35px_rgba(255,38,60,0.85)]">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.35-4.35"></path></svg>
                Explorar ahora <span>→</span>
            </a>
        </div>
    </section>
</div>

<script>
    (() => {
        const track = document.getElementById('featured-track');
        const pages = document.getElementById('featured-pages');
        if (!track || !pages) return;

        let pageCount = 1;
        let activePage = 0;
        const measure = () => {
            pageCount = Math.max(1, Math.ceil(track.scrollWidth / track.clientWidth));
            activePage = Math.min(pageCount - 1, Math.round(track.scrollLeft / track.clientWidth));
            pages.replaceChildren(...Array.from({ length: pageCount }, (_, index) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.setAttribute('aria-label', `Página ${index + 1}`);
                button.setAttribute('aria-current', index === activePage ? 'true' : 'false');
                button.className = `h-2.5 w-2.5 rounded-full transition ${index === activePage ? 'bg-red-500 shadow-[0_0_10px_rgba(255,38,60,0.9)]' : 'bg-gray-600 hover:bg-gray-400'}`;
                button.addEventListener('click', () => track.scrollTo({ left: index * track.clientWidth, behavior: 'smooth' }));
                return button;
            }));
        };
        document.querySelectorAll('[data-carousel-step]').forEach((button) => {
            button.addEventListener('click', () => track.scrollBy({
                left: Number(button.dataset.carouselStep) * Math.round(track.clientWidth * 0.8),
                behavior: 'smooth',
            }));
        });
        track.addEventListener('scroll', measure, { passive: true });
        window.addEventListener('resize', measure);
        measure();
    })();
</script>

<style>
    .particle {
        position: absolute;
        z-index: -5;
        height: 10px;
        width: 10px;
        border-radius: 9999px;
        background: #ff263c;
        box-shadow: 0 0 14px 3px rgba(255,38,60,.8);
        animation: float-up 6s ease-in-out infinite;
    }
    @keyframes float-up {
        0%, 100% { transform: translateY(0) scale(1); opacity: .85; }
        50% { transform: translateY(-26px) scale(1.25); opacity: .45; }
    }
</style>
@endsection
