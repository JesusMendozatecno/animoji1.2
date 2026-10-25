<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ANIMOJI · Administración')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Teko:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-[#060b16] text-gray-100">
    @php
        $user = $user ?? auth()->user();
        $navigationOverrides = $navigationOverrides ?? [];
        $customizations = $customizations ?? [];
        $navUrl = fn (string $key, string $fallback): ?string => ($navigationOverrides[$key] ?? null) === \App\Support\Navigation::NO_DESTINATION
            ? null
            : ($navigationOverrides[$key] ?? $fallback);
        $sidebar = $customizations['admin.sidebar'] ?? null;
        $avatar = $user?->avatar ? '/'.ltrim($user->avatar, '/') : '/images/cuenta/avatar.png';
        $isSuperAdmin = $user?->hasRole('superadmin') ?? false;
        $mainMenu = [
            ['key' => 'admin', 'label' => 'Inicio', 'route' => 'admin.dashboard', 'nav' => 'admin.menu.inicio', 'icon' => 'M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z'],
            ['key' => 'biblioteca', 'label' => 'Biblioteca', 'route' => 'admin.biblioteca', 'nav' => 'admin.menu.biblioteca', 'icon' => 'M4 4h7v7H4zm9 0h7v7h-7zM4 13h7v7H4zm9 0h7v7h-7zM16 4h4v7h-4zm0 9h4v7h-4z'],
            ['key' => 'customizacion', 'label' => 'Personalización', 'route' => 'admin.customizations.index', 'nav' => 'admin.menu.personalizacion', 'icon' => 'M12 2C6.49 2 2 6.49 2 12s4.49 10 10 10a2.5 2.5 0 0 0 2.5-2.5 2.5 2.5 0 0 1 2.5-2.5H16a6 6 0 0 0 6-6c0-4.96-4.49-8-10-8zM6.5 13a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm4-6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z'],
            ['key' => 'usuarios', 'label' => 'Usuarios', 'route' => 'admin.usuarios', 'nav' => 'admin.menu.usuarios', 'icon' => 'M16 11a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm-8 0a4 4 0 1 0-4-4 4 4 0 0 0 4 4zm0 2c-2.67 0-8 1.34-8 4v3h9.5a5.5 5.5 0 0 1 5.34-4H16zm8 0v3h3v-3c0-1.36-.5-2.6-1.32-3.55A7.4 7.4 0 0 1 24 13z'],
        ];
        $collectionMenu = [
            ['label' => 'Todos', 'query' => '', 'icon' => 'M4 4h7v7H4zm9 0h7v7h-7zM4 13h7v7H4zm9 0h7v7h-7z'],
            ['label' => 'Favoritos', 'query' => '?f=favoritos', 'icon' => 'M12 21s-7-4.35-9.5-8.5C.5 9 2.5 5 6 5c2 0 3.5 1 4 2 .5-1 2-2 4-2 3.5 0 5.5 4 3.5 7.5C19 16.65 12 21 12 21z'],
            ['label' => 'En Proceso', 'query' => '?f=proceso', 'icon' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm4.24 14.24-5.24-3.24V7h1.5v5.25l4.5 2.67z'],
            ['label' => 'Completados', 'query' => '?f=completados', 'icon' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8z'],
        ];
        $galleryMenu = [
            ['key' => 'galeria', 'label' => 'Imágenes', 'route' => 'admin.galeria', 'query' => '', 'nav' => 'admin.menu.galeria', 'icon' => 'M21 19V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5z'],
            ['key' => 'videos', 'label' => 'Videos', 'route' => 'admin.galeria', 'query' => '?tipo=videos', 'nav' => null, 'icon' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zM10 16.5v-9l6 4.5z'],
            ['key' => 'fav-galeria', 'label' => 'Favoritos', 'route' => 'admin.galeria', 'query' => '?tipo=favoritos', 'nav' => null, 'icon' => 'M12 21s-7-4.35-9.5-8.5C.5 9 2.5 5 6 5c2 0 3.5 1 4 2 .5-1 2-2 4-2 3.5 0 5.5 4 3.5 7.5C19 16.65 12 21 12 21z'],
        ];
    @endphp

    <div class="flex min-h-screen bg-[#060b16] text-gray-100">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-blue-500/20 bg-[#08101f] lg:flex">
            <div class="absolute inset-0 overflow-hidden">
                @if ($sidebar)
                    <img src="{{ $sidebar['image'] }}" alt="" class="absolute inset-0 h-full w-full object-cover" style="object-position: {{ $sidebar['x'] }}% {{ $sidebar['y'] }}%; transform: scale({{ $sidebar['scale'] }})">
                @else
                    <img src="/images/admin/sidebar.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
                @endif
                <div class="absolute inset-0 bg-gradient-to-b from-[#08101f]/85 via-[#08101f]/75 to-[#08101f]/95"></div>
            </div>

            @if (($customizing ?? false) && $isSuperAdmin)
                <a href="{{ route('admin.customizations.index') }}" class="absolute right-2 top-2 z-30 rounded-full border border-dashed border-white/70 bg-black/70 px-3 py-1.5 text-[11px] font-bold text-white">Ajustar menú</a>
            @endif

            <div class="relative z-10 flex flex-1 flex-col overflow-y-auto">
                <div class="relative flex items-center gap-3 border-b border-blue-500/15 px-5 py-5">
                    <img src="{{ $avatar }}" alt="{{ $user?->name ?? 'Usuario' }}" class="h-14 w-14 shrink-0 rounded-full border-2 border-blue-400/70 object-cover shadow-[0_0_16px_rgba(59,130,246,0.55)]">
                    <div class="min-w-0">
                        <p class="truncate font-display text-lg italic font-bold leading-tight text-white">{{ $user?->name ?? 'ANIMOJI' }}</p>
                        <p class="truncate text-[11px] leading-snug text-blue-300/80">Administrador<br>ANIMOJI</p>
                    </div>
                </div>

                <nav class="mt-4 flex flex-col gap-1 px-4" aria-label="Menú administrativo">
                    @foreach ($mainMenu as $item)
                        @php
                            $isActive = match ($item['key']) {
                                'admin' => request()->routeIs('admin.dashboard'),
                                'biblioteca' => request()->routeIs('admin.biblioteca*'),
                                'customizacion' => request()->routeIs('admin.customizations.*'),
                                'usuarios' => request()->routeIs('admin.usuarios*'),
                                default => false,
                            };
                        @endphp
                        @php
                            $itemUrl = $navUrl($item['nav'], route($item['route']));
                        @endphp
                        <a @if ($itemUrl !== null) href="{{ $itemUrl }}" @else aria-disabled="true" @endif @class([
                            'flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition',
                            'bg-gradient-to-r from-blue-600 to-sky-500 text-white shadow-[0_0_18px_rgba(59,130,246,0.5)]' => $isActive,
                            'text-gray-300 hover:bg-blue-500/10 hover:text-white' => ! $isActive,
                        ])>
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $item['icon'] }}"></path></svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <p class="mt-6 px-7 text-[10px] font-bold uppercase tracking-[0.3em] text-blue-400/70">Mi colección</p>
                <nav class="mt-2 flex flex-col gap-1 px-4" aria-label="Mi colección">
                    @php
                        $collectionUrl = $navUrl('admin.menu.coleccion', route('coleccion'));
                    @endphp
                    @foreach ($collectionMenu as $item)
                        <a @if ($collectionUrl !== null) href="{{ $collectionUrl.$item['query'] }}" @else aria-disabled="true" @endif class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-blue-500/10 hover:text-white">
                            <svg class="h-5 w-5 shrink-0 text-blue-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $item['icon'] }}"></path></svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <p class="mt-6 px-7 text-[10px] font-bold uppercase tracking-[0.3em] text-blue-400/70">Galería</p>
                <nav class="mt-2 flex flex-col gap-1 px-4" aria-label="Galería">
                    @foreach ($galleryMenu as $item)
                        @php
                            $galleryUrl = $item['nav']
                                ? $navUrl($item['nav'], route($item['route']))
                                : route($item['route']);
                            $galleryUrl = $galleryUrl === null ? null : $galleryUrl.$item['query'];
                            $isActive = match ($item['key']) {
                                'galeria' => ! in_array(request()->query('tipo'), ['videos', 'favoritos'], true),
                                'videos' => request()->query('tipo') === 'videos',
                                'fav-galeria' => request()->query('tipo') === 'favoritos',
                                default => false,
                            };
                        @endphp
                        <a @if ($galleryUrl !== null) href="{{ $galleryUrl }}" @else aria-disabled="true" @endif @class([
                            'flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition',
                            'border border-blue-400/60 bg-blue-500/15 text-white shadow-[0_0_16px_rgba(59,130,246,0.35)]' => $isActive,
                            'text-gray-300 hover:bg-blue-500/10 hover:text-white' => ! $isActive,
                        ]) @if ($isActive) aria-current="page" @endif>
                            <svg class="h-5 w-5 shrink-0 text-blue-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $item['icon'] }}"></path></svg>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto">
                    @include('components.sidebar-back-button')
                    <p class="relative px-6 pb-2 pt-16 text-sm italic leading-relaxed text-blue-100/80">« El anime, la música,<br>el manga y los videojuegos...<br>no son solo un hobby,<br>son mi forma de vida. »</p>
                    <p class="relative px-6 pb-6 pt-3 text-right font-display text-3xl italic font-bold text-blue-400 [text-shadow:0_0_18px_rgba(59,130,246,0.7)]">KANEKI</p>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen min-w-0 flex-1 flex-col lg:ml-64">
            <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-blue-500/15 bg-[#060b16]/95 px-4 py-3 backdrop-blur sm:px-6">
                <div class="relative max-w-xl flex-1">
                    <svg class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m21 21-4.35-4.35"></path></svg>
                    <input type="search" aria-label="Buscar en el sistema" placeholder="Buscar en el sistema..." class="w-full rounded-full border border-white/10 bg-white/5 py-2.5 pl-11 pr-16 text-sm text-gray-200 placeholder-gray-500 outline-none transition focus:border-blue-400/50 focus:bg-white/10">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 rounded border border-white/15 px-1.5 py-0.5 text-[10px] text-gray-500">Ctrl + K</span>
                </div>

                <div class="ml-auto flex items-center gap-4">
                    <span class="grid h-9 w-9 place-items-center rounded-full border border-white/10 bg-white/5 text-gray-300" aria-label="Tema oscuro">☾</span>
                    <span class="relative grid h-9 w-9 place-items-center rounded-full border border-white/10 bg-white/5 text-gray-300" aria-label="3 notificaciones">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 22a2 2 0 0 0 2-2h-4a2 2 0 0 0 2 2zm6-6v-5a6 6 0 0 0-5-5.91V4a1 1 0 0 0-2 0v1.09A6 6 0 0 0 6 11v5l-2 2v1h16v-1z"></path></svg>
                        <span class="absolute -right-0.5 -top-0.5 grid h-4 w-4 place-items-center rounded-full bg-blue-500 text-[9px] font-bold text-white">3</span>
                    </span>
                    <details class="relative">
                        <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full p-0.5 transition hover:bg-white/5">
                            <img src="{{ $avatar }}" alt="{{ $user?->name ?? 'Usuario' }}" class="h-10 w-10 rounded-full border-2 border-blue-400/70 object-cover">
                            <span class="hidden text-left sm:block">
                                <span class="block max-w-36 truncate text-sm font-semibold leading-tight text-white">{{ $user?->name }}</span>
                                <span class="block text-[11px] text-blue-300">Administrador</span>
                            </span>
                            <span class="text-gray-400" aria-hidden="true">⌄</span>
                        </summary>
                        <nav class="absolute right-0 z-50 mt-3 w-60 overflow-hidden rounded-xl border border-white/10 bg-[#0d0d18] p-2 text-sm shadow-[0_0_30px_rgba(59,130,246,0.25)]" aria-label="Menú de cuenta">
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
</body>
</html>
