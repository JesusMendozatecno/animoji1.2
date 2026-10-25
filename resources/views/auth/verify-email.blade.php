@extends('layouts.auth')

@section('title', 'Verificar correo · ANIMOJI')

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
        class="pointer-events-none absolute bottom-0 right-0 z-10 hidden h-[92vh] select-none object-contain md:block"
    >

    <div class="relative z-10 mx-auto w-full max-w-md px-4 sm:px-6 lg:mr-auto lg:ml-[8vw]">
        <div class="clip-auth border border-[var(--accent-soft)] bg-black/80 p-8 shadow-[0_0_40px_var(--accent-glow)] backdrop-blur-sm">
            <div class="flex justify-center">
                <img src="{{ $theme['logo'] }}" alt="ANIMOJI" class="h-16 w-16 drop-shadow-[0_0_12px_var(--accent-glow-strong)]">
            </div>

            <h1 class="mt-4 text-center font-display text-4xl italic leading-none tracking-wide text-white" style="transform: skewX(-6deg)">
                VERIFICA TU CORREO
            </h1>
            <p class="mt-3 text-center text-[11px] uppercase tracking-[0.25em] text-gray-400">Un paso más para continuar</p>
            <div class="mx-auto mt-4 h-px w-3/4 bg-gradient-to-r from-transparent via-[var(--accent-soft)] to-transparent"></div>

            <p class="mt-6 text-sm leading-relaxed text-gray-300">
                ¡Gracias por registrarte! Antes de continuar, verifica tu correo electrónico con el enlace que te enviamos. Si no lo recibiste, puedes solicitar otro.
            </p>

            @if ($status === 'verification-link-sent')
                <div class="mt-5 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300" role="status">
                    Enviamos un nuevo enlace de verificación al correo de tu cuenta.
                </div>
            @endif

            <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-gradient-to-r from-[var(--accent-dark)] via-[var(--accent)] to-[var(--accent-light)] px-5 py-3 font-display text-lg italic tracking-wider text-white shadow-[0_0_22px_var(--accent-glow)] transition hover:scale-[1.02] hover:shadow-[0_0_35px_var(--accent-glow-strong)] sm:w-auto"
                    >
                        REENVIAR CORREO
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="w-full rounded-md px-3 py-2 text-sm text-gray-400 underline transition hover:text-white focus:outline-none focus:ring-2 focus:ring-[var(--accent)] focus:ring-offset-2 focus:ring-offset-black sm:w-auto"
                    >
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
