@extends('layouts.auth')

@section('title', 'Iniciar sesión · ANIMOJI')

@section('content')
<main
    class="relative flex min-h-screen items-center overflow-hidden bg-[#0a0308]"
    style="
        --accent: {{ $theme['hex'] }};
        --accent-light: color-mix(in srgb, var(--accent) 72%, white);
        --accent-dark: color-mix(in srgb, var(--accent) 65%, black);
        --accent-faint: color-mix(in srgb, var(--accent) 45%, transparent);
        --accent-soft: color-mix(in srgb, var(--accent) 60%, transparent);
        --accent-glow: color-mix(in srgb, var(--accent) 45%, transparent);
        --accent-glow-strong: color-mix(in srgb, var(--accent) 85%, transparent);
    "
>
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $theme['bg'] }}')"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-black/20 to-black/60"></div>

    <p class="pointer-events-none absolute left-6 top-10 hidden select-none text-xl tracking-[0.5em] text-[var(--accent-soft)] [writing-mode:vertical-rl] lg:block">
        音楽は、永遠に続く
    </p>

    <span class="absolute left-1/2 top-6 z-20 hidden -translate-x-1/2 items-center gap-2 rounded-full border border-[var(--accent-faint)] bg-black/50 px-4 py-1.5 text-[11px] uppercase tracking-[0.3em] text-gray-300 backdrop-blur lg:flex">
        <span class="h-2.5 w-2.5 rounded-full bg-[var(--accent)] shadow-[0_0_10px_var(--accent-glow-strong)]"></span>
        Tema {{ $theme['label'] }}
    </span>

    <a
        href="{{ route('home') }}"
        aria-label="Cerrar"
        class="absolute right-6 top-6 z-20 grid h-10 w-10 place-items-center rounded-full border border-[var(--accent-soft)] bg-black/50 text-[var(--accent)] transition hover:bg-[var(--accent-faint)] hover:text-white"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 6l12 12M18 6 6 18"></path>
        </svg>
    </a>

    <img
        src="{{ $theme['character'] }}"
        alt=""
        class="pointer-events-none absolute bottom-0 left-0 z-10 hidden h-[92vh] select-none object-contain md:block"
    >

    <span class="pointer-events-none absolute bottom-16 right-4 hidden select-none text-[10px] uppercase tracking-[0.4em] text-[var(--accent-faint)] lg:block">
        ANIME · MANGA · MÚSICA · VIDEOJUEGOS
    </span>

    <div class="absolute bottom-8 left-8 z-10 hidden items-center gap-3 lg:flex">
        <span class="flex h-6 items-end gap-[2px]" aria-hidden="true">
            <span class="h-2 w-[3px] rounded bg-[var(--accent)]"></span>
            <span class="h-4 w-[3px] rounded bg-[var(--accent)]"></span>
            <span class="h-5 w-[3px] rounded bg-[var(--accent)]"></span>
            <span class="h-3 w-[3px] rounded bg-[var(--accent)]"></span>
            <span class="h-6 w-[3px] rounded bg-[var(--accent)]"></span>
            <span class="h-4 w-[3px] rounded bg-[var(--accent)]"></span>
            <span class="h-2 w-[3px] rounded bg-[var(--accent)]"></span>
        </span>
        <p class="text-sm leading-snug text-gray-300">Tu mundo,<br>tus historias,<br>en un solo lugar...</p>
    </div>

    <div class="relative z-10 mx-auto w-full max-w-md px-4 sm:px-6 lg:ml-auto lg:mr-[8vw]">
        <div class="clip-auth border border-[var(--accent-soft)] bg-black/80 p-8 shadow-[0_0_40px_var(--accent-glow)] backdrop-blur-sm">
            <div class="flex justify-center">
                <img src="{{ $theme['logo'] }}" alt="ANIMOJI" class="h-16 w-16 drop-shadow-[0_0_12px_var(--accent-glow-strong)]">
            </div>

            <h1 class="mt-4 text-center font-display text-5xl italic leading-none tracking-wide" style="transform: skewX(-6deg)">
                <span class="text-white">INICIAR</span>
                <span class="ml-2 text-[var(--accent)] [text-shadow:0_0_20px_var(--accent-glow-strong)]">SESIÓN</span>
            </h1>
            <p class="mt-3 text-center text-[11px] uppercase tracking-[0.35em] text-gray-400">Bienvenido de nuevo</p>
            <div class="mx-auto mt-4 h-px w-3/4 bg-gradient-to-r from-transparent via-[var(--accent-soft)] to-transparent"></div>

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                @if ($status)
                    <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-2 text-sm text-emerald-300" role="status">
                        {{ $status }}
                    </div>
                @endif

                <div>
                    <label for="email" class="mb-1.5 flex items-center gap-2 text-sm font-medium text-gray-200">
                        Correo o Usuario
                    </label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Ingresa tu correo o nombre de usuario"
                        class="w-full rounded-lg border border-[var(--accent-faint)] bg-black/50 px-4 py-3 text-sm text-gray-100 placeholder-gray-500 outline-none transition focus:border-[var(--accent)] focus:shadow-[0_0_14px_var(--accent-glow)]"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-gray-200">Contraseña</label>
                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Ingresa tu contraseña"
                            class="w-full rounded-lg border border-[var(--accent-faint)] bg-black/50 py-3 pl-4 pr-20 text-sm text-gray-100 placeholder-gray-500 outline-none transition focus:border-[var(--accent)] focus:shadow-[0_0_14px_var(--accent-glow)]"
                        >
                        <button
                            id="toggle-password"
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 transition hover:text-[var(--accent)]"
                            aria-controls="password"
                            aria-label="Mostrar contraseña"
                        >Mostrar</button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-400">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                        class="h-4 w-4 rounded border-[var(--accent-soft)] bg-black/50 text-[var(--accent)] focus:ring-[var(--accent)]"
                    >
                    Recordarme en este dispositivo
                </label>

                <button
                    type="submit"
                    class="group mt-2 w-full rounded-lg bg-gradient-to-r from-[var(--accent-dark)] via-[var(--accent)] to-[var(--accent-light)] py-3.5 font-display text-xl italic tracking-wider text-white shadow-[0_0_22px_var(--accent-glow)] transition hover:scale-[1.02] hover:shadow-[0_0_35px_var(--accent-glow-strong)]"
                >
                    INICIAR SESIÓN
                    <span class="ml-1 inline-block transition group-hover:translate-x-1">»</span>
                </button>

                @if ($canResetPassword)
                    <a
                        href="{{ route('password.request') }}"
                        class="block text-center text-xs text-gray-500 underline-offset-2 transition hover:text-[var(--accent)] hover:underline"
                    >¿Olvidaste tu contraseña?</a>
                @endif
            </form>

            <p class="mt-6 text-center text-sm text-gray-400">
                ¿Desea
                <a href="{{ route('register') }}" class="font-semibold text-[var(--accent)] underline underline-offset-2 transition hover:text-[var(--accent-light)]">
                    registrarse
                </a>
                ?
            </p>
            <a href="{{ route('home') }}" class="mt-4 block text-center text-xs text-gray-500 underline-offset-2 transition hover:text-white hover:underline">Volver al inicio público</a>
        </div>
    </div>
</main>

<script>
    const passwordField = document.getElementById('password');
    const togglePassword = document.getElementById('toggle-password');

    togglePassword.addEventListener('click', () => {
        const isVisible = passwordField.type === 'text';
        passwordField.type = isVisible ? 'password' : 'text';
        togglePassword.textContent = isVisible ? 'Mostrar' : 'Ocultar';
        togglePassword.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });
</script>
@endsection
