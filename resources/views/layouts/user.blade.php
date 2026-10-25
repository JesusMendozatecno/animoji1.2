<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ANIMOJI · Panel de usuario')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-[#07070f] text-gray-100">
    @php
        $user = $user ?? auth()->user();
        $navigationOverrides = $navigationOverrides ?? [];
        $customizations = $customizations ?? [];
        $navUrl = fn (string $key, string $fallback): ?string => ($navigationOverrides[$key] ?? null) === \App\Support\Navigation::NO_DESTINATION
            ? null
            : ($navigationOverrides[$key] ?? $fallback);
        $sidebar = $customizations['usuario.sidebar'] ?? null;
        $avatar = $user?->avatar ? '/'.ltrim($user->avatar, '/') : '/images/cuenta/avatar.png';
        $isAdmin = $user && ($user->hasRole('admin') || $user->hasRole('superadmin'));
        $isSuperAdmin = $user?->hasRole('superadmin') ?? false;
        $mainMenu = [
            ['key' => 'inicio', 'label' => 'Inicio', 'route' => 'dashboard', 'nav' => 'usuario.menu.inicio', 'icon' => 'M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z'],
            ['key' => 'anime', 'label' => 'Anime', 'route' => 'anime.index', 'nav' => 'usuario.menu.anime', 'icon' => 'M4 4h16v12H4zm2 14h12v2H6z'],
            ['key' => 'musica', 'label' => 'Música', 'route' => 'music.index', 'nav' => 'usuario.menu.musica', 'icon' => 'M12 3v10.55A4 4 0 1 0 14 17V7h4V3z'],
            ['key' => 'manga', 'label' => 'Manga', 'route' => 'manga.index', 'nav' => 'usuario.menu.manga', 'icon' => 'M4 4h7v16H4zm9 0h7v16h-7z'],
            ['key' => 'juegos', 'label' => 'Videojuegos', 'route' => 'games.index', 'nav' => 'usuario.menu.juegos', 'icon' => 'M21 6H3a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h6l2 2h2l2-2h6a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2z'],
        ];
    @endphp

    <div class="flex min-h-screen bg-[#07070f] text-gray-100">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-white/10 bg-[#0a0a16] lg:flex">
            <div class="absolute inset-0 overflow-hidden">
                @if ($sidebar)
                    <img src="{{ $sidebar['image'] }}" alt="" class="absolute inset-0 h-full w-full select-none object-cover" style="object-position: {{ $sidebar['x'] }}% {{ $sidebar['y'] }}%; transform: scale({{ $sidebar['scale'] }})">
                @else
                    <img src="/images/usuario/sidebar.jpg" alt="" class="absolute inset-0 h-full w-full select-none object-cover">
                @endif
                <div class="absolute inset-0 bg-gradient-to-b from-[#0a0a16]/85 via-[#0a0a16]/75 to-[#0a0a16]/95"></div>
            </div>

            @if (($customizing ?? false) && $isSuperAdmin)
                <a href="{{ route('admin.customizations.index') }}" class="absolute right-2 top-2 z-30 rounded-full border border-dashed border-white/70 bg-black/70 px-3 py-1.5 text-[11px] font-bold text-white">Ajustar menú</a>
            @endif

            <div class="relative z-10 flex flex-1 flex-col overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-6 py-5">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-pink-500 to-violet-600 text-lg font-black text-white shadow-[0_0_16px_rgba(236,72,153,0.55)]">A</span>
                    <span class="min-w-0">
                        <span class="block font-display text-2xl italic font-bold leading-none text-white" style="transform: skewX(-8deg)">@yield('brand-title', 'ANIMOJI')</span>
                        <span class="block truncate text-[10px] text-gray-400">@yield('brand-subtitle', 'Tu mundo, en un solo lugar')</span>
                    </span>
                </a>

                <nav class="mt-2 flex flex-col gap-1 px-4" aria-label="Menú principal">
                    @foreach ($mainMenu as $item)
                        @php
                            $itemUrl = $navUrl($item['nav'], route($item['route']));
                        @endphp
                        <a @if ($itemUrl !== null) href="{{ $itemUrl }}" @else aria-disabled="true" @endif @class([
                            'flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition',
                            'bg-gradient-to-r from-violet-600 to-indigo-500 text-white shadow-[0_0_16px_rgba(139,92,246,0.45)]' => $item['key'] === 'inicio',
                            'text-gray-300 hover:bg-white/5 hover:text-white' => $item['key'] !== 'inicio',
                        ])>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $item['icon'] }}"></path></svg>
                            {{ $item['label'] }}
                            @if ($item['key'] !== 'inicio')
                                <span class="ml-auto text-gray-600" aria-hidden="true">›</span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                <div class="mx-4 my-3 border-t border-white/10"></div>
                <p class="px-6 text-[11px] font-semibold uppercase tracking-widest text-gray-500">EXTRAS</p>

                <nav class="mt-2 flex flex-col gap-1 px-4" aria-label="Opciones de usuario">
                    @php
                        $collectionUrl = $navUrl('usuario.menu.coleccion', route('coleccion'));
                    @endphp
                    <a @if ($collectionUrl !== null) href="{{ $collectionUrl }}" @else aria-disabled="true" @endif @class([
                        'flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition',
                        'bg-violet-500/15 text-white' => request()->routeIs('coleccion'),
                        'text-gray-300 hover:bg-white/5 hover:text-white' => ! request()->routeIs('coleccion'),
                    ]) @if (request()->routeIs('coleccion')) aria-current="page" @endif>
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54z"></path></svg>
                        Favoritos
                        <span class="ml-auto text-gray-600" aria-hidden="true">›</span>
                    </a>
                    @php
                        $configUrl = $navUrl('usuario.menu.config', route('profile.edit'));
                    @endphp
                    <a @if ($configUrl !== null) href="{{ $configUrl }}" @else aria-disabled="true" @endif class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/5 hover:text-white">
                        <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.14 12.94a7.07 7.07 0 0 0 0-1.88l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.61-.22l-2.39.96a7 7 0 0 0-1.62-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54a7 7 0 0 0-1.62.94l-2.39-.96a.5.5 0 0 0-.61.22L2.65 8.84a.5.5 0 0 0 .12.64l2.03 1.58a7.07 7.07 0 0 0 0 1.88l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32a.5.5 0 0 0 .61.22l2.39-.96c.5.38 1.04.7 1.62.94l.36 2.54a.5.5 0 0 0 .5.42h3.84a.5.5 0 0 0 .5-.42l.36-2.54a7 7 0 0 0 1.62-.94l2.39.96a.5.5 0 0 0 .61-.22l1.92-3.32a.5.5 0 0 0-.12-.64zM12 15.5A3.5 3.5 0 1 1 15.5 12 3.5 3.5 0 0 1 12 15.5z"></path></svg>
                        Configuración
                        <span class="ml-auto text-gray-600" aria-hidden="true">›</span>
                    </a>
                </nav>

                <div class="mt-auto pt-16">
                    @include('components.sidebar-back-button')
                    <div class="px-6 pb-4">
                        <p class="text-sm italic leading-relaxed text-gray-300">"Grandes historias,<br>en un solo lugar"</p>
                        <div class="mt-2 h-1 w-10 rounded bg-violet-500"></div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:ml-64">
            <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-white/10 bg-[#07070f]/95 px-4 py-3 backdrop-blur sm:px-6">
                <div class="flex items-center gap-2 text-sm text-gray-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"></path></svg>
                    @yield('page-title', 'Inicio')
                </div>
                <div class="relative ml-4 hidden max-w-md flex-1 md:block">
                    <svg class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.35-4.35"></path></svg>
                    <input type="search" placeholder="Buscar en tu colección..." aria-label="Buscar en tu colección" class="w-full rounded-full border border-white/10 bg-white/5 py-2.5 pl-11 pr-4 text-sm text-gray-200 placeholder-gray-500 outline-none transition focus:border-violet-400/50 focus:bg-white/10">
                </div>

                <div class="ml-auto flex items-center gap-4">
                    <span class="relative grid h-9 w-9 place-items-center rounded-full border border-white/10 bg-white/5 text-gray-300" aria-label="3 notificaciones">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6v-5a6 6 0 0 0-5-5.91V4a1 1 0 0 0-2 0v1.09A6 6 0 0 0 6 11v5l-2 2v1h16v-1z"></path></svg>
                        <span class="absolute -right-0.5 -top-0.5 grid h-4 w-4 place-items-center rounded-full bg-red-500 text-[9px] font-bold text-white">3</span>
                    </span>
                    <details class="relative">
                        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full p-0.5 transition hover:shadow-[0_0_14px_rgba(255,38,60,0.5)]">
                            <img src="{{ $avatar }}" alt="{{ $user->name }}" class="h-10 w-10 rounded-full border-2 border-red-500/70 object-cover shadow-[0_0_12px_rgba(255,38,60,0.45)]">
                            <span class="hidden text-left sm:block">
                                <span class="block max-w-36 truncate text-sm font-semibold leading-tight text-white">{{ $user->name }}</span>
                                <span class="block text-[11px] text-gray-500">{{ $isAdmin ? 'Administrador' : 'Miembro' }}</span>
                            </span>
                            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="m7 10 5 5 5-5z"></path></svg>
                        </summary>
                        <nav class="absolute right-0 z-50 mt-3 w-60 overflow-hidden rounded-xl border border-white/10 bg-[#0d0d18] p-2 text-sm shadow-[0_0_30px_rgba(255,38,60,0.25)]" aria-label="Menú de usuario">
                            <div class="border-b border-white/10 px-2 py-2">
                                <p class="truncate font-semibold text-white">{{ $user->name }}</p>
                                <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                            </div>
                            @include('components.account-menu-items', ['user' => $user])
                        </nav>
                    </details>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6">
                @yield('content')
            </main>
        </div>
    </div>

    <style>
        details summary::-webkit-details-marker { display: none; }
    </style>
    @stack('scripts')
</body>
</html>
