@extends('layouts.auth')

@section('title', 'Confirmar contraseña · ANIMOJI')

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

    <a
        href="{{ route('home') }}"
        aria-label="Volver al inicio"
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

    <div class="relative z-10 mx-auto w-full max-w-md px-4 sm:px-6 lg:ml-auto lg:mr-[8vw]">
        <div class="clip-auth border border-[var(--accent-soft)] bg-black/80 p-8 shadow-[0_0_40px_var(--accent-glow)] backdrop-blur-sm">
            <div class="flex justify-center">
                <img src="{{ $theme['logo'] }}" alt="ANIMOJI" class="h-16 w-16 drop-shadow-[0_0_12px_var(--accent-glow-strong)]">
            </div>

            <h1 class="mt-4 text-center font-display text-4xl italic leading-none tracking-wide text-white" style="transform: skewX(-6deg)">
                CONFIRMAR CONTRASEÑA
            </h1>
            <p class="mt-3 text-center text-[11px] uppercase tracking-[0.25em] text-gray-400">Verificación de seguridad</p>
            <div class="mx-auto mt-4 h-px w-3/4 bg-gradient-to-r from-transparent via-[var(--accent-soft)] to-transparent"></div>

            <p class="mt-6 text-sm leading-relaxed text-gray-300">
                Esta es una zona segura. Confirma tu contraseña para continuar.
            </p>

            <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-gray-200">Contraseña</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autofocus
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-[var(--accent-faint)] bg-black/50 px-4 py-3 text-sm text-gray-100 placeholder-gray-500 outline-none transition focus:border-[var(--accent)] focus:shadow-[0_0_14px_var(--accent-glow)]"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-[var(--accent-dark)] via-[var(--accent)] to-[var(--accent-light)] py-3.5 font-display text-xl italic tracking-wider text-white shadow-[0_0_22px_var(--accent-glow)] transition hover:scale-[1.02] hover:shadow-[0_0_35px_var(--accent-glow-strong)]"
                >
                    CONFIRMAR
                </button>
            </form>
        </div>
    </div>
</main>
@endsection
