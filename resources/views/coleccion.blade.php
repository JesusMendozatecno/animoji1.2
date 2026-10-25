@extends('layouts.user')

@section('title', 'Mi colección · ANIMOJI')
@section('page-title', 'Mi colección')
@section('brand-title', 'KANEKI · MI COLECCIÓN')
@section('brand-subtitle', 'Todo lo que me apasiona...')

@section('content')
@php
    $cards = [
        ['key' => 'anime', 'label' => 'Anime', 'stat' => $stats['anime'], 'img' => '/images/usuario/cat-anime.jpg', 'icon' => 'M4 4h16v12H4zm2 14h12v2H6z', 'route' => 'anime.index', 'accent' => 'border-red-500/70 shadow-[0_0_22px_rgba(255,38,60,0.35)] hover:border-red-400', 'button' => 'border-red-500/70 text-red-300 hover:bg-red-500/15'],
        ['key' => 'manga', 'label' => 'Manga', 'stat' => $stats['manga'], 'img' => '/images/usuario/cat-manga.jpg', 'icon' => 'M4 4h7v16H4zm9 0h7v16h-7z', 'route' => 'manga.index', 'accent' => 'border-violet-500/70 shadow-[0_0_22px_rgba(139,92,246,0.35)] hover:border-violet-400', 'button' => 'border-violet-500/70 text-violet-300 hover:bg-violet-500/15'],
        ['key' => 'musica', 'label' => 'Música', 'stat' => $stats['music'], 'img' => '/images/usuario/cat-musica.jpg', 'icon' => 'M12 3v10.55A4 4 0 1 0 14 17V7h4V3z', 'route' => 'music.index', 'accent' => 'border-blue-500/70 shadow-[0_0_22px_rgba(59,130,246,0.35)] hover:border-blue-400', 'button' => 'border-blue-500/70 text-blue-300 hover:bg-blue-500/15'],
        ['key' => 'juegos', 'label' => 'Videojuegos', 'stat' => $stats['games'], 'img' => '/images/usuario/cat-juegos.jpg', 'icon' => 'M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h6l2 2h2l2-2h6a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z', 'route' => 'games.index', 'accent' => 'border-emerald-500/70 shadow-[0_0_22px_rgba(34,197,94,0.35)] hover:border-emerald-400', 'button' => 'border-emerald-500/70 text-emerald-300 hover:bg-emerald-500/15'],
    ];
    $tagStyles = [
        'Anime' => 'bg-red-500/15 text-red-300 ring-red-400/40',
        'Manga' => 'bg-violet-500/15 text-violet-300 ring-violet-400/40',
        'Música' => 'bg-blue-500/15 text-blue-300 ring-blue-400/40',
        'Videojuegos' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/40',
    ];
    $fallbackCover = fn (int $index) => '/images/usuario/recent-'.(($index % 4) + 1).'.jpg';
    $genreChips = ['Acción', 'Aventura', 'Drama', 'Fantasía', 'Misterio', 'Shonen', 'J-Rock', 'OST'];
@endphp

<section class="relative overflow-hidden rounded-3xl border border-white/10">
    <img src="/images/usuario/hero-coleccion1.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-[#07070f] via-[#07070f]/55 to-transparent"></div>
    <div class="relative p-8 sm:p-10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="font-display text-5xl italic leading-none tracking-wide text-white sm:text-6xl" style="transform: skewX(-6deg)">
                    MI <span class="text-red-500 [text-shadow:0_0_20px_rgba(255,38,60,0.7)]">COLECCIÓN</span>
                </h1>
                <p class="mt-3 text-sm text-gray-300">Anime · Manga · Música · Videojuegos</p>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-gray-300">
                    Un lugar donde guardo todo lo que me hace feliz.<br>
                    Aquí vive mi pasión, mi inspiración y mi mundo.
                </p>
            </div>
            <p class="hidden select-none text-xl tracking-[0.4em] text-sky-300/70 [writing-mode:vertical-rl] lg:block">好きなことを続ける</p>
        </div>
    </div>
</section>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_320px]">
    <div class="min-w-0">
        <div class="grid gap-5 sm:grid-cols-2 2xl:grid-cols-4">
            @foreach ($cards as $card)
                <a href="{{ route($card['route']) }}" class="group relative isolate flex min-h-64 flex-col justify-end overflow-hidden rounded-2xl border-2 p-4 transition duration-300 hover:-translate-y-1.5 {{ $card['accent'] }}">
                    <div class="absolute inset-0 -z-10">
                        <img src="{{ $card['img'] }}" alt="{{ $card['label'] }}" class="h-full w-full object-cover opacity-55 transition duration-500 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#07070f] via-[#07070f]/60 to-transparent"></div>
                    </div>
                    <span class="mb-2 grid h-9 w-9 place-items-center rounded-lg bg-black/50 text-white ring-1 ring-white/25">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $card['icon'] }}"></path></svg>
                    </span>
                    <p class="font-display text-3xl italic tracking-wide text-white" style="transform: skewX(-6deg)">{{ $card['label'] }}</p>
                    <p class="text-xs text-gray-300">Total registrado</p>
                    <p class="text-2xl font-black text-white">{{ $card['stat'] }}</p>
                    <span class="mt-3 w-fit rounded-full border px-4 py-1.5 text-xs font-semibold transition group-hover:scale-105 {{ $card['button'] }}">Ver colección →</span>
                </a>
            @endforeach
        </div>

        <section class="mt-8">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-red-500/15 text-red-400 ring-1 ring-red-400/40">🕒</span>
                    <h2 class="text-xl font-bold text-white">Últimos agregados</h2>
                </div>
                <a href="{{ route('anime.index') }}" class="flex items-center gap-1.5 text-sm text-gray-300 transition hover:text-white">Ver todos →</a>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">
                @forelse (array_slice($recent, 0, 4) as $index => $item)
                    <article class="group overflow-hidden rounded-2xl border border-white/10 bg-[#0d0d1a] transition hover:-translate-y-1 hover:border-red-400/50">
                        <a href="{{ route($item['route'], $item['slug']) }}">
                            <div class="relative h-24 overflow-hidden">
                                <img src="{{ $item['cover'] ?? $fallbackCover($index) }}" alt="{{ $item['title'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                <span class="absolute left-2 top-2 rounded-md px-2 py-0.5 text-[11px] font-bold ring-1 {{ $tagStyles[$item['type']] ?? $tagStyles['Anime'] }}">{{ $item['type'] }}</span>
                            </div>
                            <div class="p-3.5">
                                <p class="truncate text-sm font-semibold text-white">{{ $item['title'] }}</p>
                                <p class="mt-0.5 text-[11px] text-gray-400">Agregado recientemente</p>
                            </div>
                        </a>
                    </article>
                @empty
                    <p class="col-span-full rounded-2xl border border-dashed border-white/15 p-8 text-center text-sm text-gray-400">Aún no hay títulos en el catálogo.</p>
                @endforelse
            </div>
        </section>
    </div>

    <aside class="flex flex-col gap-6">
        <section class="rounded-2xl border border-white/10 bg-[#0d0d1a] p-5">
            <div class="flex items-center gap-2">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-red-500/15 text-red-400">🖼</span>
                <h2 class="font-bold text-white">Resumen de tu colección</h2>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-3">
                @foreach ($cards as $card)
                    <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3 text-center">
                        <span class="mx-auto grid h-8 w-8 place-items-center rounded-lg text-white ring-1 ring-white/20 {{ str_replace('border-', 'bg-', explode(' ', $card['button'])[0]) }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $card['icon'] }}"></path></svg>
                        </span>
                        <p class="mt-1.5 text-xl font-black text-white">{{ $card['stat'] }}</p>
                        <p class="text-[11px] text-gray-400">{{ $card['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-[#0d0d1a] p-5">
            <div class="flex items-center gap-2">
                <span class="text-violet-400">💜</span>
                <h2 class="font-bold text-white">Géneros favoritos</h2>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($genreChips as $genre)
                    <span class="rounded-lg border border-white/15 bg-white/5 px-3 py-1.5 text-xs text-gray-300 transition hover:border-violet-400/50">{{ $genre }}</span>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-white/10 bg-[#0d0d1a] p-5">
            <div class="flex items-center gap-2">
                <span class="text-sky-400">🕐</span>
                <h2 class="font-bold text-white">Recientes</h2>
            </div>
            <ul class="mt-4 flex flex-col gap-3">
                @forelse (array_slice($recent, 0, 5) as $index => $item)
                    <li>
                        <a href="{{ route($item['route'], $item['slug']) }}" class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/[0.03] p-2.5 transition hover:border-sky-400/40">
                            <img src="{{ $item['cover'] ?? $fallbackCover($index) }}" alt="{{ $item['title'] }}" class="h-11 w-11 shrink-0 rounded-lg object-cover">
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-white">{{ $item['title'] }}</span>
                                <span class="mt-0.5 inline-block rounded px-1.5 py-0.5 text-[10px] font-semibold ring-1 {{ $tagStyles[$item['type']] ?? $tagStyles['Anime'] }}">{{ $item['type'] }}</span>
                            </span>
                            <span class="text-gray-500" aria-hidden="true">⋮</span>
                        </a>
                    </li>
                @empty
                    <li class="text-center text-xs text-gray-400">Sin elementos recientes.</li>
                @endforelse
            </ul>
        </section>
    </aside>
</div>
@endsection
