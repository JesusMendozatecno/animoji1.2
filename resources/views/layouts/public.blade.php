<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'ANIMOJI'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-[#07070d] text-gray-100">
    @php
        $user = $user ?? auth()->user();
        $canLogin = $canLogin ?? \Illuminate\Support\Facades\Route::has('login');
        $canRegister = $canRegister ?? \Illuminate\Support\Facades\Route::has('register');
        $navigationOverrides = $navigationOverrides ?? [];
        $navUrl = fn (string $key, string $fallback): ?string => ($navigationOverrides[$key] ?? null) === \App\Support\Navigation::NO_DESTINATION
            ? null
            : ($navigationOverrides[$key] ?? $fallback);
        $logoUrl = $navUrl('header.logo', route('home'));
        $avatar = $user?->avatar ? '/'.ltrim($user->avatar, '/') : '/images/cuenta/avatar.png';
    @endphp

    <header class="sticky top-0 z-30 border-b-2 border-red-500/70 bg-[#0a0a12]/95 shadow-[0_2px_20px_rgba(255,38,60,0.35)] backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a @if ($logoUrl !== null) href="{{ $logoUrl }}" @else aria-disabled="true" @endif class="flex shrink-0 items-center gap-3">
                <img src="/images/index/logo.png" alt="ANIMOJI" class="h-11 w-11 drop-shadow-[0_0_10px_rgba(255,38,60,0.9)]">
                <span class="font-display text-3xl italic font-bold tracking-wide text-white" style="transform: skewX(-8deg)">
                    ANIMO<span class="text-red-500 [text-shadow:0_0_16px_rgba(255,38,60,0.9)]">JI</span>
                </span>
            </a>

            <nav class="hidden items-center gap-8 md:flex" aria-label="Navegación principal">
                <a @if (($url = $navUrl('header.inicio', route('home'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="group flex items-center gap-2 text-sm font-medium {{ request()->routeIs('home') ? 'text-red-500 [text-shadow:0_0_12px_rgba(255,38,60,0.8)]' : 'text-gray-200 transition hover:text-red-400' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path></svg>Inicio
                </a>
                <a @if (($url = $navUrl('header.explorar', route('anime.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="group flex items-center gap-2 text-sm font-medium text-gray-200 transition hover:text-red-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm2.19 12.19L6 18l3.81-8.19L18 6l-3.81 8.19z"></path></svg>Explorar
                </a>
                <a @if (($url = $navUrl('header.mi_biblioteca', route('dashboard'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="group flex items-center gap-2 text-sm font-medium text-gray-200 transition hover:text-red-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4 4h7v16H4zm9 0h7v16h-7z"></path></svg>Mi Biblioteca
                </a>
                <a @if (($url = $navUrl('header.buscar', route('anime.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="group flex items-center gap-2 text-sm font-medium text-gray-200 transition hover:text-red-400">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M15.5 14h-.79l-.28-.27a6.5 6.5 0 1 0-.7.7l.27.28v.79l5 4.99L20.49 19zm-6 0A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14z"></path></svg>Buscar
                </a>
            </nav>

            <div class="flex items-center gap-3">
                @if ($user)
                    <details class="relative">
                        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full p-1 transition hover:bg-white/5">
                            <img src="{{ $avatar }}" alt="" class="h-10 w-10 rounded-full border-2 border-red-500/70 object-cover">
                            <span class="hidden max-w-36 truncate text-sm font-semibold text-white sm:block">{{ $user->name }}</span>
                            <span class="text-gray-400" aria-hidden="true">⌄</span>
                        </summary>
                        <nav class="absolute right-0 z-50 mt-3 w-60 overflow-hidden rounded-xl border border-white/10 bg-[#0d0d18] p-2 text-sm shadow-[0_0_30px_rgba(255,38,60,0.25)]" aria-label="Menú de usuario">
                            @include('components.account-menu-items', ['user' => $user])
                        </nav>
                    </details>
                @else
                    @if ($canLogin)
                        <a @if (($url = $navUrl('header.iniciar_sesion', route('login'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="whitespace-nowrap text-sm font-semibold text-gray-100 transition hover:text-red-400">Iniciar sesión</a>
                    @endif
                    @if ($canRegister)
                        <a @if (($url = $navUrl('header.registrarme', route('register'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="inline-flex items-center gap-2 rounded-md bg-gradient-to-r from-red-600 to-red-500 px-5 py-2.5 text-sm font-semibold text-white shadow-[0_0_18px_rgba(255,38,60,0.55)] transition hover:scale-105 hover:shadow-[0_0_30px_rgba(255,38,60,0.85)]">
                            Registrarme <span aria-hidden="true">↗</span>
                        </a>
                    @endif
                @endif
            </div>
        </div>

        <nav class="flex gap-5 overflow-x-auto border-t border-red-500/20 px-4 py-2 text-sm md:hidden" aria-label="Navegación móvil">
            <a @if (($url = $navUrl('header.inicio', route('home'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="whitespace-nowrap text-gray-300 transition hover:text-red-400">⌂ Inicio</a>
            <a @if (($url = $navUrl('header.explorar', route('anime.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="whitespace-nowrap text-gray-300 transition hover:text-red-400">◎ Explorar</a>
            <a @if (($url = $navUrl('header.mi_biblioteca', route('dashboard'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="whitespace-nowrap text-gray-300 transition hover:text-red-400">▤ Mi Biblioteca</a>
            <a @if (($url = $navUrl('header.buscar', route('anime.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="whitespace-nowrap text-gray-300 transition hover:text-red-400">⌕ Buscar</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-red-500/30 bg-[#0a0a12] py-6">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 sm:px-6 lg:flex-row lg:px-8">
            <p class="flex items-center gap-2 text-sm text-gray-400">
                <svg class="h-4 w-4 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"></path></svg>
                www.animoji.com
            </p>

            <div class="flex flex-col items-center gap-2">
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Síguenos</p>
                <div class="flex items-center gap-3">
                    @foreach (['Discord' => '◈', 'Facebook' => 'f', 'TikTok' => '♪', 'YouTube' => '▶', 'Twitter' => '𝕏'] as $network => $icon)
                        <a href="#" aria-label="{{ $network }}" class="social-dot">{{ $icon }}</a>
                    @endforeach
                </div>
            </div>

            <nav class="flex flex-wrap items-center justify-center gap-2 text-xs font-semibold uppercase tracking-widest text-gray-400" aria-label="Secciones">
                <a @if (($url = $navUrl('footer.anime', route('anime.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="transition hover:text-red-400">Anime</a><span class="text-red-500">•</span>
                <a @if (($url = $navUrl('footer.manga', route('manga.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="transition hover:text-red-400">Manga</a><span class="text-red-500">•</span>
                <a @if (($url = $navUrl('footer.musica', route('music.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="transition hover:text-red-400">Música</a><span class="text-red-500">•</span>
                <a @if (($url = $navUrl('footer.juegos', route('games.index'))) !== null) href="{{ $url }}" @else aria-disabled="true" @endif class="transition hover:text-red-400">Videojuegos</a>
            </nav>
        </div>
        <p class="mt-4 text-center text-xs text-gray-500">
            © {{ now()->year }} ANIMOJI. Todos los derechos reservados.
            @if ($user)
                · <form method="POST" action="{{ route('logout') }}" class="inline">@csrf<button type="submit" class="underline-offset-2 transition hover:text-red-400 hover:underline">Cerrar sesión</button></form>
            @endif
        </p>
    </footer>

    <style>
        .social-dot {
            display: grid;
            height: 2.25rem;
            width: 2.25rem;
            place-items: center;
            border-radius: 9999px;
            border: 1px solid rgba(239,68,68,.6);
            background: rgba(10,10,18,.9);
            color: #f3f4f6;
            box-shadow: 0 0 12px rgba(255,38,60,.35);
            transition: all .25s ease;
        }
        .social-dot:hover { color: #fff; background: rgba(255,38,60,.25); box-shadow: 0 0 18px rgba(255,38,60,.75); transform: translateY(-2px); }
        details summary::-webkit-details-marker { display: none; }
    </style>
    @include('partials.music-player', ['musicPlayerTracks' => $musicPlayerTracks ?? []])
</body>
</html>
