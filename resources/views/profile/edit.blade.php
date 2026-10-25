@extends('layouts.auth')

@section('title', 'Cuenta · ANIMOJI')

@section('content')
@php
    $isAdmin = $user->hasRole('admin') || $user->hasRole('superadmin');
    $isSuperAdmin = $user->hasRole('superadmin');
    $avatar = $user->avatar ? '/'.ltrim($user->avatar, '/') : '/images/cuenta/avatar.png';
    $createdAt = $user->created_at?->translatedFormat('d M Y') ?? '—';
    $profileSections = ['cuenta', 'password', 'delete', 'preferencias', 'documentacion', 'historial'];
    $activeSection = old('_profile_section', request()->query('section', session('profile_section', 'cuenta')));
    $activeSection = in_array($activeSection, $profileSections, true) ? $activeSection : 'cuenta';
@endphp

<div class="min-h-screen bg-[#0a0509] text-gray-100">
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col overflow-hidden border-r border-red-500/20 bg-[#0d060b] lg:flex">
        @if ($sidebarImage)
            <div class="absolute inset-0 overflow-hidden">
                <img
                    src="/{{ ltrim($sidebarImage->image_path, '/') }}"
                    alt=""
                    class="absolute inset-0 h-full w-full object-cover"
                    style="object-position: {{ $sidebarImage->pos_x }}% {{ $sidebarImage->pos_y }}%; transform: scale({{ $sidebarImage->scale }})"
                >
                <div class="absolute inset-0 bg-gradient-to-b from-[#0d060b]/85 via-[#0d060b]/75 to-[#0d060b]/95"></div>
            </div>
        @else
            <div class="absolute inset-0">
                <img src="/images/cuenta/sidebar.jpg" alt="" class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-b from-[#0d060b]/85 via-[#0d060b]/75 to-[#0d060b]/95"></div>
            </div>
        @endif

        <div class="relative z-10 flex flex-1 flex-col">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-6 py-5">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-red-500 to-rose-700 text-lg font-black text-white shadow-[0_0_16px_rgba(255,38,60,0.6)]">A</span>
                <span>
                    <span class="block font-display text-2xl italic font-bold leading-none text-white">ANIMOJI</span>
                    <span class="text-[10px] tracking-[0.3em] text-gray-400">アニメ · マンガ · 音楽</span>
                </span>
            </a>

            @if ($customizing && $isSuperAdmin)
                <a href="{{ route('admin.customizations.index') }}" class="mx-4 rounded-lg border border-dashed border-white/50 bg-black/50 px-3 py-2 text-xs text-white">
                    Ajustar imagen del menú de Cuenta
                </a>
            @endif

            <nav class="mt-4 flex flex-col gap-1.5 px-4">
                <a href="#cuenta" @class(['side-item', 'side-active' => $activeSection === 'cuenta']) data-profile-tab="cuenta" data-profile-active="{{ $activeSection === 'cuenta' ? 'true' : 'false' }}" @if ($activeSection === 'cuenta') aria-current="page" @endif>
                    <span aria-hidden="true">👤</span> Cuenta
                </a>
                <a href="#password" @class(['side-item', 'side-active' => $activeSection === 'password']) data-profile-tab="password" data-profile-active="{{ $activeSection === 'password' ? 'true' : 'false' }}" @if ($activeSection === 'password') aria-current="page" @endif>
                    <span aria-hidden="true">🔑</span> Cambiar contraseña
                </a>
                <a href="#delete" @class(['side-item text-red-400', 'side-active' => $activeSection === 'delete']) data-profile-tab="delete" data-profile-active="{{ $activeSection === 'delete' ? 'true' : 'false' }}" @if ($activeSection === 'delete') aria-current="page" @endif>
                    <span aria-hidden="true">⚠</span> Borrar cuenta
                </a>
                <a href="#preferencias" @class(['side-item', 'side-active' => $activeSection === 'preferencias']) data-profile-tab="preferencias" data-profile-active="{{ $activeSection === 'preferencias' ? 'true' : 'false' }}" @if ($activeSection === 'preferencias') aria-current="page" @endif>
                    <span aria-hidden="true">⚙</span> Preferencias
                </a>
                <a href="#documentacion" @class(['side-item', 'side-active' => $activeSection === 'documentacion']) data-profile-tab="documentacion" data-profile-active="{{ $activeSection === 'documentacion' ? 'true' : 'false' }}" @if ($activeSection === 'documentacion') aria-current="page" @endif>
                    <span aria-hidden="true">📚</span> Documentación
                </a>
                <a href="#historial" @class(['side-item', 'side-active' => $activeSection === 'historial']) data-profile-tab="historial" data-profile-active="{{ $activeSection === 'historial' ? 'true' : 'false' }}" @if ($activeSection === 'historial') aria-current="page" @endif>
                    <span aria-hidden="true">🕒</span> Historial
                </a>
            </nav>

            <div class="mt-auto">
                @include('components.sidebar-back-button')
                <div class="px-5 pb-5">
                    <p class="border-l-2 border-red-500/70 px-4 pb-4 pt-12 text-sm italic leading-relaxed text-gray-300">
                        "La música, el anime<br>y el manga también<br>son una forma de vida..."
                    </p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col lg:ml-64">
        <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-white/10 bg-[#0a0509]/95 px-4 py-3 backdrop-blur sm:px-6">
            <a href="{{ route('dashboard') }}" class="font-semibold text-white lg:hidden">ANIMOJI</a>
            <div class="ml-auto flex items-center gap-4">
                <span class="hidden text-sm text-gray-400 sm:block">{{ $user->name }}</span>
                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full p-1 hover:bg-white/5">
                        <img src="{{ $avatar }}" alt="" class="h-9 w-9 rounded-full border border-red-500/60 object-cover">
                        <span class="text-sm text-gray-200">Mi cuenta⌄</span>
                    </summary>
                    <nav class="absolute right-0 z-40 mt-2 w-60 overflow-hidden rounded-xl border border-white/10 bg-[#11101a] p-2 text-sm shadow-xl" aria-label="Menú de cuenta">
                        @include('components.account-menu-items', ['user' => $user])
                    </nav>
                </details>
            </div>
        </header>

        <main class="flex-1 space-y-6 px-4 py-6 sm:px-6">
            <section class="relative overflow-hidden rounded-2xl border border-white/10">
                <img src="/images/cuenta/banner.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-r from-[#0a0509] via-[#0a0509]/55 to-transparent"></div>
                <div class="relative p-7 sm:p-9">
                    <p class="text-2xl font-bold leading-relaxed text-white sm:text-3xl">自己を信じて、<br>進み続けろ</p>
                    <p class="mt-2 text-sm text-gray-300">Cree en ti mismo y sigue adelante.</p>
                </div>
            </section>

            @if (session('success'))
                <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-2.5 text-sm text-emerald-300" role="status">{{ session('success') }}</div>
            @endif
            @if ($status)
                <div class="rounded-xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-2.5 text-sm text-emerald-300" role="status">{{ $status }}</div>
            @endif

            <section id="cuenta" data-profile-panel="cuenta" @if ($activeSection !== 'cuenta') hidden @endif class="grid gap-6 xl:grid-cols-[340px_1fr_360px]">
                <article class="rounded-2xl border border-red-500/40 bg-[#120a0f] p-5 shadow-[0_0_24px_rgba(255,38,60,0.15)]">
                    <div class="flex items-center gap-4">
                        <button type="button" id="open-avatar" class="rounded-full focus-visible:ring-4 focus-visible:ring-white/60" aria-label="Cambiar foto de perfil">
                            <img src="{{ $avatar }}" alt="{{ $user->name }}" class="h-24 w-24 rounded-full border-4 border-red-500/70 object-cover shadow-[0_0_18px_rgba(255,38,60,0.55)]">
                        </button>
                        <div class="min-w-0">
                            <p class="truncate text-xl font-bold text-white">{{ $user->name }} @if ($isAdmin) <span class="text-red-500">👑</span> @endif</p>
                            @if ($isAdmin)
                                <span class="mt-1 inline-block rounded-full bg-red-500/20 px-2.5 py-0.5 text-[11px] font-semibold text-red-300 ring-1 ring-red-400/50">Administrador</span>
                            @endif
                            <p class="mt-2 text-xs leading-relaxed text-gray-400">El anime, la música y el manga forman parte de mi vida.</p>
                            <button type="button" class="mt-2 text-xs font-semibold text-red-300 hover:text-red-200" onclick="document.getElementById('open-avatar').click()">📷 Cambiar foto</button>
                            @error('avatar')<p class="mt-2 text-xs font-semibold text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-3 gap-2 border-t border-white/10 pt-4 text-center">
                        <div><p class="text-[11px] text-gray-500">Registrado</p><p class="mt-0.5 text-xs font-semibold text-gray-300">{{ $createdAt }}</p></div>
                        <div><p class="text-[11px] text-gray-500">Última conexión</p><p class="mt-0.5 text-xs font-semibold text-gray-300">Ahora</p></div>
                        <div><p class="text-[11px] text-gray-500">Estado</p><p class="mt-0.5 text-xs font-semibold text-emerald-400">En línea</p></div>
                    </div>
                </article>

                <article class="rounded-2xl border border-white/10 bg-[#120a0f] p-5">
                    <div class="flex items-center gap-2.5">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-red-500/15 text-red-400">👤</span>
                        <h2 class="font-bold text-white">Información personal</h2>
                    </div>
                    <form method="POST" action="{{ route('profile.update') }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_profile_section" value="cuenta">
                        <div>
                            <label for="name" class="text-xs text-gray-400">Nombre</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name" class="mt-1 w-full rounded-lg border border-red-500/40 bg-black/40 px-4 py-2.5 text-sm text-gray-100 outline-none focus:border-red-500">
                            @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="text-xs text-gray-400">Correo electrónico</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="mt-1 w-full rounded-lg border border-red-500/40 bg-black/40 px-4 py-2.5 text-sm text-gray-100 outline-none focus:border-red-500">
                            @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        </div>
                        @if ($mustVerifyEmail && ! $user->hasVerifiedEmail())
                            <p class="text-xs text-amber-300">Tu correo aún no está verificado.
                                <button form="resend-verification" type="submit" class="underline">Reenviar enlace de verificación</button>
                            </p>
                        @endif
                        <button type="submit" class="rounded-lg bg-gradient-to-r from-red-600 to-red-500 px-5 py-2 text-sm font-semibold text-white shadow-[0_0_14px_rgba(255,38,60,0.45)] transition hover:shadow-[0_0_22px_rgba(255,38,60,0.7)]">Guardar cambios</button>
                    </form>
                    <form id="resend-verification" method="POST" action="{{ route('verification.send') }}" class="hidden">@csrf</form>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs text-emerald-400">Se envió un nuevo enlace a tu correo.</p>
                    @endif

                    <dl class="mt-6 space-y-3 border-t border-white/10 pt-4">
                        <div class="flex items-center gap-3"><dt class="text-xs text-gray-500">Rol</dt><dd class="ml-auto rounded-full bg-red-500/15 px-2.5 py-0.5 text-[11px] font-semibold text-red-300">{{ $roles->first() ?? 'usuario' }}</dd></div>
                        <div class="flex items-center gap-3"><dt class="text-xs text-gray-500">Fecha de registro</dt><dd class="ml-auto text-sm font-semibold text-white">{{ $createdAt }}</dd></div>
                    </dl>
                </article>

                <article class="rounded-2xl border border-white/10 bg-[#120a0f] p-5">
                    <div class="flex items-center gap-2.5"><span class="grid h-9 w-9 place-items-center rounded-xl bg-red-500/15 text-red-400">📊</span><h2 class="font-bold text-white">Estadísticas del usuario</h2></div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3"><span>🎬</span><p class="mt-2 text-xl font-black text-white">{{ $stats['anime'] }}</p><p class="text-[11px] text-gray-400">Anime vistos</p></div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3"><span>📖</span><p class="mt-2 text-xl font-black text-white">{{ $stats['manga'] }}</p><p class="text-[11px] text-gray-400">Mangas leídos</p></div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3"><span>🎵</span><p class="mt-2 text-xl font-black text-white">{{ $stats['music'] }}</p><p class="text-[11px] text-gray-400">Canciones escuchadas</p></div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3"><span>🎮</span><p class="mt-2 text-xl font-black text-white">{{ $stats['games'] }}</p><p class="text-[11px] text-gray-400">Videojuegos</p></div>
                        <div class="rounded-xl border border-white/10 bg-white/[0.03] p-3"><span>♥</span><p class="mt-2 text-xl font-black text-white">{{ $stats['favorites'] }}</p><p class="text-[11px] text-gray-400">Favoritos</p></div>
                    </div>
                </article>
            </section>

            <div class="space-y-6">
                    <section id="password" data-profile-panel="password" @if ($activeSection !== 'password') hidden @endif class="rounded-2xl border border-red-500/40 bg-[#120a0f] p-5">
                        <h2 class="font-bold text-white">Cambiar contraseña</h2>
                        <p class="text-xs text-gray-400">Actualiza tu contraseña para mantener tu cuenta segura.</p>
                        <form method="POST" action="{{ route('password.update', ['section' => 'password']) }}" class="mt-5 max-w-md space-y-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_profile_section" value="password">
                            <div>
                                <label for="current_password" class="text-xs text-gray-400">Contraseña actual</label>
                                <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-lg border border-red-500/40 bg-black/40 px-4 py-2.5 text-sm text-gray-100 outline-none focus:border-red-500">
                                @error('current_password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="new_password" class="text-xs text-gray-400">Nueva contraseña</label>
                                <input id="new_password" name="password" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-lg border border-red-500/40 bg-black/40 px-4 py-2.5 text-sm text-gray-100 outline-none focus:border-red-500">
                                @if ($activeSection === 'password')@error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror @endif
                            </div>
                            <div>
                                <label for="password_confirmation" class="text-xs text-gray-400">Confirmar nueva contraseña</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-1 w-full rounded-lg border border-red-500/40 bg-black/40 px-4 py-2.5 text-sm text-gray-100 outline-none focus:border-red-500">
                            </div>
                            <button type="submit" class="rounded-lg bg-gradient-to-r from-red-600 to-red-500 px-6 py-2.5 text-sm font-semibold text-white shadow-[0_0_14px_rgba(255,38,60,0.45)] transition hover:shadow-[0_0_22px_rgba(255,38,60,0.7)]">Actualizar contraseña</button>
                        </form>
                    </section>

                    <section id="delete" data-profile-panel="delete" @if ($activeSection !== 'delete') hidden @endif class="rounded-2xl border border-red-500/50 bg-[#120a0f] p-5">
                        <h2 class="font-bold text-white">Borrar cuenta</h2>
                        <p class="mt-1 text-sm text-gray-400">Esta acción es permanente y eliminará los datos asociados a tu cuenta.</p>
                        <form method="POST" action="{{ route('profile.destroy', ['section' => 'delete']) }}" class="mt-5 max-w-md space-y-4" onsubmit="return confirm('¿Seguro que deseas borrar tu cuenta? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="_profile_section" value="delete">
                            <div>
                                <label for="delete_password" class="text-xs text-gray-400">Escribe tu contraseña para confirmar</label>
                                <input id="delete_password" name="password" type="password" required autocomplete="current-password" class="mt-1 w-full rounded-lg border border-red-500/40 bg-black/40 px-4 py-2.5 text-sm text-gray-100 outline-none focus:border-red-500">
                                @if ($activeSection === 'delete')@error('password')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror @endif
                            </div>
                            <button type="submit" class="rounded-lg bg-red-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600">Borrar cuenta</button>
                        </form>
                    </section>

                    <section id="preferencias" data-profile-panel="preferencias" @if ($activeSection !== 'preferencias') hidden @endif class="rounded-2xl border border-white/10 bg-[#120a0f] p-5">
                        <h2 class="font-bold text-white">Preferencias</h2>
                        <p class="text-xs text-gray-400">Personaliza los colores de la experiencia.</p>
                        <div class="mt-4 rounded-xl border border-white/10 bg-black/30 p-4">
                            <label for="load-color" class="text-sm font-semibold text-white">Color de la pantalla de carga</label>
                            <div class="mt-3 flex items-center gap-3">
                                <input id="load-color" type="color" class="h-10 w-14 cursor-pointer rounded border border-white/20 bg-transparent" aria-label="Color de carga">
                                <span id="load-color-value" class="rounded-lg border border-white/15 px-3 py-1.5 font-mono text-xs text-gray-300"></span>
                            </div>
                            <p class="mt-2 text-[11px] text-gray-400">Se guarda en este navegador.</p>

                            <div class="mt-5 border-t border-white/10 pt-4">
                                <p class="text-sm font-semibold text-white">Imágenes del inicio por color</p>
                                <p class="mt-1 text-xs text-gray-400">Elige el tema para las imágenes de la página de inicio.</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('profile.theme', ['section' => 'preferencias']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="_profile_section" value="preferencias">
                                        <input type="hidden" name="index_theme" value="">
                                        <button type="submit" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $index_theme ? 'border-white/15 text-gray-400 hover:border-white/30 hover:text-white' : 'border-white/40 bg-white/10 text-white' }}">⭘ Por defecto</button>
                                    </form>
                                    @foreach ($themes as $key => $theme)
                                        <form method="POST" action="{{ route('profile.theme', ['section' => 'preferencias']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="_profile_section" value="preferencias">
                                            <input type="hidden" name="index_theme" value="{{ $key }}">
                                            <button type="submit" class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $index_theme === $key ? 'border-white/40 bg-white/10 text-white' : 'border-white/15 text-gray-400 hover:border-white/30 hover:text-white' }}">
                                                <span class="h-3 w-3 rounded-full" style="background-color: {{ $theme['hex'] }}"></span>{{ $theme['label'] }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                                @error('index_theme')<p class="mt-2 text-xs text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </section>

                <section id="documentacion" data-profile-panel="documentacion" @if ($activeSection !== 'documentacion') hidden @endif class="rounded-2xl border border-white/10 bg-[#120a0f] p-5">
                    <h2 class="font-bold text-white">Documentación</h2>
                    <p class="mt-1 text-sm text-gray-400">Guías y manuales disponibles para ayudarte a usar Animoji.</p>
                    @if ($manuals->isNotEmpty())
                        <div class="mt-5 space-y-3">
                            @foreach ($manuals as $manual)
                                <details class="rounded-xl border border-white/10 bg-black/30 p-4">
                                    <summary class="cursor-pointer font-semibold text-white">{{ $manual->title }} @if ($manual->module)<span class="ml-2 text-xs font-normal text-gray-400">{{ $manual->module }}</span>@endif</summary>
                                    @if ($manual->summary)<p class="mt-3 text-sm text-gray-300">{{ $manual->summary }}</p>@endif
                                    @if ($manual->content)<div class="mt-3 whitespace-pre-line text-sm leading-relaxed text-gray-400">{{ $manual->content }}</div>@endif
                                </details>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-5 rounded-xl border border-white/10 bg-black/30 p-4">
                            <p class="font-semibold text-white">Guía rápida</p>
                            <p class="mt-2 text-sm leading-relaxed text-gray-400">Explora los catálogos para descubrir anime, manga, música y videojuegos. Puedes guardar tus favoritos y registrar tu progreso para consultarlo después en tu historial.</p>
                            <a href="{{ route('dashboard') }}" class="mt-4 inline-flex rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-500">Ir al inicio</a>
                        </div>
                    @endif
                </section>

                <section id="historial" data-profile-panel="historial" @if ($activeSection !== 'historial') hidden @endif class="rounded-2xl border border-white/10 bg-[#120a0f] p-5">
                    <h2 class="font-bold text-white">Historial</h2>
                    <p class="mt-1 text-sm text-gray-400">Tu actividad reciente en anime, manga, música y videojuegos.</p>
                    @if ($history->isNotEmpty())
                        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($history as $item)
                                <article class="flex gap-3 rounded-xl border border-white/10 bg-black/30 p-3">
                                    @if ($item['cover'])
                                        <img src="{{ str_starts_with($item['cover'], 'http') ? $item['cover'] : asset($item['cover']) }}" alt="" class="h-20 w-14 shrink-0 rounded-lg object-cover">
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-white">{{ $item['title'] }}</p>
                                        @if ($item['context'])<p class="mt-1 truncate text-xs text-gray-400">{{ $item['context'] }}</p>@endif
                                        <p class="mt-2 text-xs text-red-300">{{ number_format($item['percentage'], 0) }}% completado</p>
                                        @if ($item['updated_at'])<p class="mt-1 text-[11px] text-gray-500">{{ $item['updated_at']->diffForHumans() }}</p>@endif
                                        @if ($item['url'])<a href="{{ $item['url'] }}" class="mt-2 inline-block text-xs font-semibold text-gray-300 underline hover:text-white">Ver contenido</a>@endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-5 rounded-xl border border-white/10 bg-black/30 p-4 text-sm text-gray-400">Todavía no hay actividad registrada en tu historial.</p>
                    @endif
                </section>
            </div>
        </main>
    </div>
</div>

<div id="avatar-modal" class="fixed inset-0 z-[95] hidden place-items-center bg-black/75 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="avatar-modal-title">
    <div class="flex max-h-[90vh] w-[min(680px,94vw)] flex-col overflow-hidden rounded-2xl border border-white/15 bg-[#0b1220] shadow-[0_0_60px_rgba(0,0,0,0.7)]">
        <div class="flex items-center justify-between border-b border-white/10 px-5 py-3.5">
            <h2 id="avatar-modal-title" class="font-display text-xl italic font-bold text-white">Cambiar foto de perfil</h2>
            <button type="button" id="close-avatar" class="text-gray-400 hover:text-white" aria-label="Cerrar">✕</button>
        </div>
        <div class="flex-1 space-y-4 overflow-y-auto p-5">
            <form id="avatar-form" method="POST" action="{{ route('profile.avatar', ['section' => 'cuenta']) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_profile_section" value="cuenta">
                <input id="avatar-file" type="file" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-red-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white" aria-label="Seleccionar imagen de perfil">
                <input id="avatar-upload" type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="hidden">
                <p class="mt-1.5 text-[11px] text-gray-400">JPG, PNG o WebP · Máximo 4 MB · La foto se guarda recortada en cuadrado</p>
                <p id="avatar-client-error" class="mt-1 hidden text-xs font-semibold text-red-400"></p>
            </form>

            <div id="avatar-editor" class="hidden grid gap-5 sm:grid-cols-[1fr_auto] sm:items-center">
                <div id="avatar-stage" class="relative aspect-square w-full max-w-[320px] cursor-grab touch-none select-none overflow-hidden rounded-2xl border-2 border-white/20 bg-[#07070f] active:cursor-grabbing" role="application" aria-label="Área de recorte: arrastra para mover la imagen">
                    <canvas id="avatar-canvas" class="h-full w-full object-cover" draggable="false"></canvas>
                    <div class="pointer-events-none absolute inset-0 grid grid-cols-3 grid-rows-3" aria-hidden="true">
                        @for ($i = 0; $i < 9; $i++)<span class="border border-white/10"></span>@endfor
                    </div>
                    <span class="pointer-events-none absolute inset-0 rounded-full border-2 border-dashed border-white/40" aria-hidden="true"></span>
                </div>
                <div class="space-y-4">
                    <div class="grid place-items-center">
                        <div class="h-24 w-24 overflow-hidden rounded-full border-4 border-red-500/70 shadow-[0_0_18px_rgba(255,38,60,0.45)]"><canvas id="avatar-preview" class="h-full w-full object-cover"></canvas></div>
                        <p class="mt-2 text-[11px] text-gray-400">Vista previa circular</p>
                    </div>
                    <div>
                        <label for="avatar-zoom" class="mb-1 block text-xs font-semibold text-gray-300">Ampliar / reducir: <span id="avatar-zoom-label">1.00</span>×</label>
                        <input id="avatar-zoom" type="range" min="1" max="3" step="0.05" value="1" class="w-full accent-red-500">
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div><label for="avatar-x" class="mb-1 block text-gray-400">Horizontal <span id="avatar-x-label">50</span>%</label><input id="avatar-x" type="range" min="0" max="100" value="50" class="w-full accent-red-500"></div>
                        <div><label for="avatar-y" class="mb-1 block text-gray-400">Vertical <span id="avatar-y-label">50</span>%</label><input id="avatar-y" type="range" min="0" max="100" value="50" class="w-full accent-red-500"></div>
                    </div>
                    <button type="button" id="avatar-reset" class="w-full rounded-xl border border-white/15 px-3 py-2 text-xs text-gray-300 hover:border-white/40 hover:text-white">↺ Restablecer ajuste</button>
                    <p id="avatar-file-info" class="truncate text-[11px] text-gray-400"></p>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-white/10 px-5 py-4">
            <button type="button" id="cancel-avatar" class="rounded-xl border border-white/15 px-4 py-2.5 text-sm text-gray-300 hover:border-white/40 hover:text-white">Cancelar</button>
            <button type="button" id="save-avatar" disabled class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500 disabled:cursor-not-allowed disabled:opacity-60">Guardar foto</button>
        </div>
    </div>
</div>

<script>
    (() => {
        const modal = document.getElementById('avatar-modal');
        const input = document.getElementById('avatar-file');
        const upload = document.getElementById('avatar-upload');
        const form = document.getElementById('avatar-form');
        const editor = document.getElementById('avatar-editor');
        const stage = document.getElementById('avatar-stage');
        const canvas = document.getElementById('avatar-canvas');
        const preview = document.getElementById('avatar-preview');
        const saveButton = document.getElementById('save-avatar');
        const error = document.getElementById('avatar-client-error');
        const size = 512;
        let image = null;
        let objectUrl = null;
        let dragging = false;

        const open = () => {
            modal.classList.remove('hidden');
            modal.classList.add('grid');
            document.getElementById('close-avatar').focus();
        };
        const close = () => {
            modal.classList.add('hidden');
            modal.classList.remove('grid');
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
            image = null;
            input.value = '';
            upload.value = '';
            editor.classList.add('hidden');
            saveButton.disabled = true;
            error.classList.add('hidden');
        };
        document.getElementById('open-avatar').addEventListener('click', open);
        document.getElementById('close-avatar').addEventListener('click', close);
        document.getElementById('cancel-avatar').addEventListener('click', close);
        modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape' && !modal.classList.contains('hidden')) close(); });

        const controls = {
            zoom: document.getElementById('avatar-zoom'),
            x: document.getElementById('avatar-x'),
            y: document.getElementById('avatar-y'),
        };
        const draw = () => {
            if (!image) return;
            for (const target of [canvas, preview]) {
                target.width = size;
                target.height = size;
                const context = target.getContext('2d');
                if (!context) continue;
                const scale = Math.max(size / image.width, size / image.height) * Number(controls.zoom.value);
                const width = image.width * scale;
                const height = image.height * scale;
                context.clearRect(0, 0, size, size);
                context.drawImage(image, (size - width) * (Number(controls.x.value) / 100), (size - height) * (Number(controls.y.value) / 100), width, height);
            }
            document.getElementById('avatar-zoom-label').textContent = Number(controls.zoom.value).toFixed(2);
            document.getElementById('avatar-x-label').textContent = controls.x.value;
            document.getElementById('avatar-y-label').textContent = controls.y.value;
        };
        Object.values(controls).forEach((control) => control.addEventListener('input', draw));
        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;
            error.classList.add('hidden');
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                error.textContent = 'Formato no permitido. Usa JPG, PNG o WebP.';
                error.classList.remove('hidden');
                return;
            }
            if (file.size > 4 * 1024 * 1024) {
                error.textContent = 'La imagen supera el tamaño máximo permitido (4 MB).';
                error.classList.remove('hidden');
                return;
            }
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            image = new Image();
            image.onload = () => {
                controls.zoom.value = '1';
                controls.x.value = '50';
                controls.y.value = '50';
                document.getElementById('avatar-file-info').textContent = `${file.name} · ${(file.size / 1024).toFixed(0)} KB`;
                editor.classList.remove('hidden');
                saveButton.disabled = false;
                draw();
            };
            image.onerror = () => {
                error.textContent = 'No se pudo leer la imagen. Inténtalo con otra.';
                error.classList.remove('hidden');
            };
            image.src = objectUrl;
        });
        document.getElementById('avatar-reset').addEventListener('click', () => {
            controls.zoom.value = '1';
            controls.x.value = '50';
            controls.y.value = '50';
            draw();
        });
        const position = (event) => {
            const rect = stage.getBoundingClientRect();
            controls.x.value = String(Math.max(0, Math.min(100, Math.round(((event.clientX - rect.left) / rect.width) * 100))));
            controls.y.value = String(Math.max(0, Math.min(100, Math.round(((event.clientY - rect.top) / rect.height) * 100))));
            draw();
        };
        stage.addEventListener('pointerdown', (event) => {
            if (!image) return;
            dragging = true;
            stage.setPointerCapture(event.pointerId);
            position(event);
        });
        stage.addEventListener('pointermove', (event) => { if (dragging) position(event); });
        stage.addEventListener('pointerup', () => { dragging = false; });
        stage.addEventListener('pointercancel', () => { dragging = false; });
        saveButton.addEventListener('click', () => {
            if (!image || saveButton.disabled) return;
            saveButton.disabled = true;
            saveButton.textContent = 'Guardando…';
            canvas.toBlob((blob) => {
                if (!blob) {
                    error.textContent = 'No se pudo procesar la imagen. Inténtalo nuevamente.';
                    error.classList.remove('hidden');
                    saveButton.disabled = false;
                    saveButton.textContent = 'Guardar foto';
                    return;
                }
                const croppedFile = new File([blob], 'avatar.jpg', { type: 'image/jpeg' });
                const data = new DataTransfer();
                data.items.add(croppedFile);
                upload.files = data.files;
                form.submit();
            }, 'image/jpeg', 0.9);
        });

        const colorInput = document.getElementById('load-color');
        const colorValue = document.getElementById('load-color-value');
        const savedColor = localStorage.getItem('animoji.loadColor') || '#ff263c';
        colorInput.value = savedColor;
        colorValue.textContent = savedColor;
        colorInput.addEventListener('input', () => {
            colorValue.textContent = colorInput.value;
            localStorage.setItem('animoji.loadColor', colorInput.value);
        });

        const profileSections = new Set(['cuenta', 'password', 'delete', 'preferencias', 'documentacion', 'historial']);
        const panels = document.querySelectorAll('[data-profile-panel]');
        const tabs = document.querySelectorAll('[data-profile-tab]');
        const activateSection = (section) => {
            const activeSection = profileSections.has(section) ? section : 'cuenta';
            panels.forEach((panel) => {
                panel.hidden = panel.dataset.profilePanel !== activeSection;
            });
            tabs.forEach((tab) => {
                const isActive = tab.dataset.profileTab === activeSection;
                tab.classList.toggle('side-active', isActive);
                tab.dataset.profileActive = isActive ? 'true' : 'false';
                if (isActive) {
                    tab.setAttribute('aria-current', 'page');
                } else {
                    tab.removeAttribute('aria-current');
                }
            });
        };
        const hashSection = window.location.hash.slice(1);
        if (profileSections.has(hashSection)) {
            activateSection(hashSection);
        } else {
            activateSection(@js($activeSection));
            window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}#${@js($activeSection)}`);
        }
        window.addEventListener('hashchange', () => activateSection(window.location.hash.slice(1)));
    })();
</script>

<style>
    [data-profile-panel][hidden] { display: none !important; }
    .side-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        border-radius: .75rem;
        padding: .75rem 1rem;
        font-size: .875rem;
        font-weight: 500;
        color: #d1d5db;
        transition: all .15s ease;
    }
    .side-item:hover { background: rgba(255,255,255,.05); color: #fff; }
    .side-active {
        background: linear-gradient(90deg, rgba(225,29,72,.9), rgba(159,18,57,.7));
        color: #fff;
        box-shadow: 0 0 18px rgba(255,38,60,.45);
    }
    details summary::-webkit-details-marker { display: none; }
</style>
@endsection
