<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ANIMOJI')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-[#07070d] text-gray-100">
    @php
        $section = $section ?? 'anime';
        $config = $config ?? [];
        $user = $user ?? auth()->user();
        $sectionLabel = $config['label'] ?? ucfirst($section);
        $favoritesRoute = $section.'.favoritos';
        $listsRoute = $section.'.listas';
        $historyRoute = $section.'.seguir';
        $pendingRoute = $section.'.pendientes';
        $currentPage = $currentPage ?? 'favoritos';
        $indexRoute = $config['indexRoute'] ?? 'anime.index';
        $currentLabel = match ($currentPage) {
            'listas' => 'MIS LISTAS',
            'pendientes' => 'PENDIENTES',
            'seguir' => 'SEGUIR',
            default => 'FAVORITOS',
        };
        $sidebarHint = $sidebarHint ?? 'Marcados con ❤️ desde el catálogo';
        $theme = $config['theme'] ?? 'red';
        $navigation = [
            ['key' => 'inicio', 'label' => 'Inicio', 'route' => $indexRoute, 'icon' => 'M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z'],
            ['key' => 'favoritos', 'label' => $config['favorites'] ?? 'Favoritos', 'route' => $favoritesRoute, 'icon' => 'M12 21s-7.5-4.9-10-9.2C.6 8.6 2.4 5 6 5c2.1 0 3.4 1.1 4 2 .6-.9 1.9-2 4-2 3.6 0 5.4 3.6 4 6.8C19.5 16.1 12 21 12 21z'],
            ['key' => 'listas', 'label' => $config['list'] ?? 'Mis listas', 'route' => $listsRoute, 'icon' => 'M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z'],
            ['key' => 'seguir', 'label' => $config['continue'] ?? 'Seguir', 'route' => $historyRoute, 'icon' => 'M8 5v14l11-7z'],
            ['key' => 'pendientes', 'label' => $config['pending'] ?? 'Pendientes', 'route' => $pendingRoute, 'icon' => 'M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm1 11H7v-2h4V6h2z'],
        ];
        $accent = match ($theme) {
            'mono' => 'bg-white text-black',
            'blue' => 'bg-blue-600 text-white shadow-[0_0_18px_rgba(37,99,235,0.55)]',
            'teal' => 'bg-teal-600 text-white shadow-[0_0_18px_rgba(20,184,166,0.55)]',
            default => 'bg-gradient-to-r from-fuchsia-600 to-red-500 text-white shadow-[0_0_18px_rgba(255,38,60,0.45)]',
        };
        $quoteBorder = match ($theme) {
            'mono' => 'border-white/60',
            'blue' => 'border-blue-400/70',
            'teal' => 'border-teal-400/70',
            default => 'border-red-500/70',
        };
    @endphp

    <div class="flex min-h-screen bg-[#07070d] text-gray-100">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-white/10 bg-[#0a0a14] lg:flex">
            <div class="absolute inset-0 overflow-hidden">
                <img src="/images/{{ $section }}/sidebar.jpg" alt="" class="absolute inset-0 h-full w-full object-cover opacity-70">
                <div class="absolute inset-0 bg-gradient-to-b from-[#0a0a14]/70 via-[#0a0a14]/75 to-[#0a0a14]/95"></div>
            </div>
            <div class="relative z-10 flex flex-1 flex-col">
                <a href="{{ route($user ? 'dashboard' : 'home') }}" class="flex items-center gap-3 px-6 py-5">
                    <img src="/images/{{ $section }}/logo.png" alt="{{ $sectionLabel }}" class="h-11 w-11 drop-shadow-[0_0_10px_rgba(255,38,60,0.7)]">
                    <span>
                        <span class="block font-display text-2xl italic font-bold leading-none tracking-wide text-white">ANIMO<span class="text-red-500">JI</span></span>
                        <span class="mt-0.5 block text-[10px] tracking-[0.2em] text-gray-400">{{ strtoupper($sectionLabel) }} · {{ $currentLabel }}</span>
                    </span>
                </a>

                <nav class="mt-4 flex flex-col gap-1 px-4" aria-label="{{ $sectionLabel }} navigation">
                    @foreach ($navigation as $item)
                        @php($active = $item['key'] === $currentPage)
                        <a href="{{ route($item['route']) }}" @class([
                            'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition',
                            $accent => $active,
                            'text-gray-300 hover:bg-white/5 hover:text-white' => ! $active,
                        ]) @if ($active) aria-current="page" @endif>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $item['icon'] }}"></path></svg>
                            <span class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto pt-16">
                    @include('components.sidebar-back-button')
                    <div class="px-6 pb-8">
                        <p @class(['border-l-2 pl-3 text-sm italic leading-relaxed text-gray-300', $quoteBorder])>{{ $config['quote'] ?? 'Tus favoritos en un solo lugar.' }}</p>
                        <p class="mt-2 pl-3 text-xs text-gray-400">{{ $sidebarHint }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:ml-64">
            <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-white/10 bg-[#07070d]/95 px-4 py-3 backdrop-blur sm:px-6">
                <div class="relative max-w-xl flex-1">
                    <svg class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.35-4.35"></path></svg>
                    <input type="search" placeholder="Buscar en {{ $sectionLabel }}..." aria-label="Buscar en {{ $sectionLabel }}" class="w-full rounded-full border border-white/10 bg-white/5 py-2.5 pl-11 pr-16 text-sm text-gray-200 placeholder-gray-400 outline-none transition focus:border-white/30 focus:bg-white/10">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 rounded border border-white/15 px-1.5 py-0.5 text-[10px] text-gray-400">Ctrl + K</span>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    @if ($user)
                        <details class="relative">
                            <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full p-0.5 transition hover:bg-white/5">
                                <img src="{{ $user->avatar ? '/'.ltrim($user->avatar, '/') : '/images/cuenta/avatar.png' }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full border-2 border-white/20 object-cover">
                                <span class="hidden text-sm font-semibold text-white sm:block">{{ $user->name }}</span>
                            </summary>
                            <nav class="absolute right-0 z-50 mt-3 w-60 overflow-hidden rounded-xl border border-white/10 bg-[#0d0d18] p-2 text-sm shadow-xl" aria-label="Menú de cuenta">
                                @include('components.account-menu-items', ['user' => $user])
                            </nav>
                        </details>
                    @else
                        <a href="{{ route('login') }}" class="whitespace-nowrap text-sm font-semibold text-gray-100 transition hover:text-red-400">Iniciar sesión</a>
                        @if (\Illuminate\Support\Facades\Route::has('register'))
                            <a href="{{ route('register') }}" class="whitespace-nowrap rounded-md bg-gradient-to-r from-red-600 to-red-500 px-4 py-2 text-sm font-semibold text-white shadow-[0_0_18px_rgba(255,38,60,0.35)] transition hover:shadow-[0_0_26px_rgba(255,38,60,0.65)]">Registrarme</a>
                        @endif
                    @endif
                </div>
            </header>
            <main class="flex-1 px-4 py-6 sm:px-6">
                @yield('content')
            </main>
        </div>
    </div>
    @include('partials.music-player', ['musicPlayerTracks' => $musicPlayerTracks ?? []])
</body>
</html>
