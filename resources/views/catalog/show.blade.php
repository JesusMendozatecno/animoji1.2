@extends('layouts.public')

@section('title', $item->title.' · ANIMOJI')

@section('content')
@php
    $meta = match ($type) {
        'anime' => ['label' => 'Anime', 'indexRoute' => 'anime.index', 'accent' => 'from-fuchsia-500 to-purple-500'],
        'manga' => ['label' => 'Manga', 'indexRoute' => 'manga.index', 'accent' => 'from-purple-500 to-fuchsia-500'],
        'music' => ['label' => 'Música', 'indexRoute' => 'music.index', 'accent' => 'from-cyan-400 to-blue-500'],
        default => ['label' => 'Juegos', 'indexRoute' => 'games.index', 'accent' => 'from-emerald-400 to-teal-500'],
    };
    $cover = $item->cover;
    $cover = $cover
        ? (str_starts_with($cover, 'http') || str_starts_with($cover, '/') ? $cover : '/storage/'.ltrim($cover, '/'))
        : null;
    $description = $item->synopsis ?? $item->description ?? null;
@endphp

<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <a href="{{ route($meta['indexRoute']) }}" class="text-sm text-gray-400 transition hover:text-white">
        ← Volver a {{ $meta['label'] }}
    </a>

    <div class="mt-6 grid gap-8 lg:grid-cols-[320px_1fr]">
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-gradient-to-br {{ $meta['accent'] }}">
            <div class="aspect-[3/4]">
                @if ($cover)
                    <img src="{{ $cover }}" alt="{{ $item->title }}" class="h-full w-full object-cover">
                @else
                    <span class="grid h-full w-full place-items-center text-7xl font-black text-gray-950/40">{{ mb_substr($item->title, 0, 1) }}</span>
                @endif
            </div>
        </div>

        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-white/10 px-3 py-1 text-xs uppercase tracking-widest text-gray-400">{{ $meta['label'] }}</span>
                @if ($item->status)
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-gray-300">{{ $item->status }}</span>
                @endif
            </div>

            <h1 class="mt-4 text-3xl font-black tracking-tight text-white sm:text-5xl">{{ $item->title }}</h1>
            @if ($item->rating)
                <p class="mt-3 text-lg font-semibold text-amber-300">★ {{ $item->rating }} / {{ in_array($type, ['anime', 'manga'], true) ? 10 : 5 }}</p>
            @endif
            <p class="mt-6 whitespace-pre-line leading-relaxed text-gray-300">
                {{ $description ?: 'Todavía no hay una descripción para este título.' }}
            </p>

            @if ($user)
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <form method="POST" action="{{ route('personal.favoritos.toggle') }}">
                        @csrf
                        <input type="hidden" name="section" value="{{ $section }}">
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $isFavorite ? 'border-red-400/70 bg-red-500/15 text-red-300' : 'border-white/15 text-gray-300 hover:border-red-400/60 hover:text-red-300' }}">
                            {{ $isFavorite ? '❤️ En favoritos' : '🤍 Añadir a favoritos' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('personal.pendientes.toggle') }}">
                        @csrf
                        <input type="hidden" name="section" value="{{ $section }}">
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold transition {{ $isPending ? 'border-amber-400/70 bg-amber-500/15 text-amber-300' : 'border-white/15 text-gray-300 hover:border-amber-400/60 hover:text-amber-300' }}">
                            {{ $isPending ? '⏳ En pendientes' : 'Añadir a pendientes' }}
                        </button>
                    </form>
                </div>
            @endif

            <dl class="mt-8 grid gap-4 sm:grid-cols-2">
                @if ($type === 'anime')
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Estudio</dt>
                        <dd class="mt-1 font-medium text-white">{{ $item->studio ?? '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Año</dt>
                        <dd class="mt-1 font-medium text-white">{{ $item->year ?? '—' }}</dd>
                    </div>
                @elseif ($type === 'manga')
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Autor</dt>
                        <dd class="mt-1 font-medium text-white">{{ $item->author ?? '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Artista</dt>
                        <dd class="mt-1 font-medium text-white">{{ $item->artist ?? '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Año</dt>
                        <dd class="mt-1 font-medium text-white">{{ $item->year ?? '—' }}</dd>
                    </div>
                @elseif ($type === 'music')
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Artista</dt>
                        <dd class="mt-1 font-medium text-white">{{ $item->artist ?? '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Álbum</dt>
                        <dd class="mt-1 font-medium text-white">{{ $item->album ?? '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Duración</dt>
                        <dd class="mt-1 font-medium text-white">
                            @if ($item->duration)
                                {{ intdiv($item->duration, 60) }}:{{ str_pad((string) ($item->duration % 60), 2, '0', STR_PAD_LEFT) }}
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                @else
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Plataformas</dt>
                        <dd class="mt-1 font-medium text-white">{{ is_array($item->platforms) ? implode(', ', $item->platforms) : '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-white/10 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-400">Año</dt>
                        <dd class="mt-1 font-medium text-white">{{ $item->release_year ?? '—' }}</dd>
                    </div>
                    @if ($item->requirements)
                        <div class="rounded-xl border border-white/10 p-4 sm:col-span-2">
                            <dt class="text-xs uppercase tracking-wide text-gray-400">Requisitos</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm text-gray-300">{{ $item->requirements }}</dd>
                        </div>
                    @endif
                @endif
            </dl>

            @if ($type === 'game')
                @if ($item->download_url)
                    <a href="{{ $item->download_url }}" target="_blank" rel="noopener noreferrer" class="mt-6 inline-flex rounded-xl bg-white px-5 py-3 text-sm font-semibold text-gray-950 transition hover:bg-gray-200">
                        Descargar juego
                    </a>
                @else
                    <p class="mt-6 text-sm text-gray-400">Este título se consulta desde su ficha; ANIMOJI no ejecuta juegos en el navegador.</p>
                @endif
            @endif
        </div>
    </div>

    @if ($type === 'anime')
        <section class="mt-12">
            <h2 class="text-lg font-semibold text-white">Temporadas y episodios</h2>
            @forelse ($seasons as $season)
                <div class="mt-5">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400">
                        Temporada {{ $season['number'] }}
                        <span class="ml-2 font-normal normal-case text-gray-500">· {{ count($season['episodes']) }} episodio{{ count($season['episodes']) === 1 ? '' : 's' }}</span>
                    </h3>
                    <ul class="mt-3 flex flex-col gap-2">
                        @foreach ($season['episodes'] as $episode)
                            @php
                                $percentage = min(100, max(0, $episode['percentage']));
                            @endphp
                            <li>
                                <a href="{{ $episode['url'] }}" class="group flex items-center gap-4 rounded-xl border border-white/10 bg-white/[0.02] p-3 transition hover:border-red-400/50 hover:bg-white/[0.05]">
                                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-white/15 text-sm font-bold text-gray-300 group-hover:border-red-400/70 group-hover:text-red-300">{{ $episode['episode_number'] }}</span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-white">{{ $episode['label'] }}@if ($episode['title'])<span class="font-normal text-gray-400"> · {{ $episode['title'] }}</span>@endif</span>
                                        <span class="mt-1 block h-1 w-full max-w-48 overflow-hidden rounded-full bg-white/10"><span class="block h-full rounded-full bg-gradient-to-r from-fuchsia-500 to-red-500" style="width: {{ $percentage }}%"></span></span>
                                    </span>
                                    <span class="shrink-0 text-right text-xs text-gray-400">
                                        @if ($episode['completed'])
                                            <span class="block text-emerald-400">✅ Visto</span>
                                        @elseif ($percentage > 0)
                                            <span class="block text-amber-300">{{ round($percentage) }}%</span>
                                        @endif
                                        <span class="block {{ $episode['has_source'] ? '' : 'text-gray-500' }}">{{ $episode['duration'] ? intdiv($episode['duration'], 60).' min' : '—' }}</span>
                                    </span>
                                    <span class="shrink-0 rounded-full bg-white px-3.5 py-1.5 text-xs font-bold text-gray-950 transition group-hover:bg-red-500 group-hover:text-white">Ver</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="mt-3 text-sm text-gray-400">Este anime todavía no tiene episodios publicados.</p>
            @endforelse
        </section>
    @elseif ($type === 'manga')
        <section class="mt-12">
            <h2 class="text-lg font-semibold text-white">
                Capítulos <span class="text-sm font-normal text-gray-400">· {{ count($chapters) }} publicado{{ count($chapters) === 1 ? '' : 's' }}</span>
            </h2>
            @if (empty($chapters))
                <p class="mt-3 text-sm text-gray-400">Este manga todavía no tiene capítulos publicados.</p>
            @else
                <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($chapters as $chapter)
                        <li>
                            <a href="{{ $chapter['url'] }}" class="group flex items-center gap-4 rounded-xl border border-white/10 bg-white/[0.02] p-3 transition hover:border-violet-400/50 hover:bg-white/[0.05]">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full border border-white/15 text-xs font-bold text-gray-300 group-hover:border-violet-400/70 group-hover:text-violet-300">{{ $chapter['chapter_number'] }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-white">{{ $chapter['label'] }}@if ($chapter['title'])<span class="font-normal text-gray-400"> · {{ $chapter['title'] }}</span>@endif</span>
                                    <span class="block text-xs text-gray-400">
                                        {{ $chapter['pages_count'] }} página{{ $chapter['pages_count'] === 1 ? '' : 's' }}
                                        @if ($chapter['completed'])
                                            <span class="text-emerald-400">· ✅ Leído</span>
                                        @elseif ($chapter['percentage'] > 0)
                                            <span class="text-amber-300">· {{ round($chapter['percentage']) }}%</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="shrink-0 rounded-full bg-white px-3.5 py-1.5 text-xs font-bold text-gray-950 transition group-hover:bg-violet-500 group-hover:text-white">Leer</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @else
        <section class="mt-12 rounded-2xl border border-white/10 bg-white/[0.02] p-6">
            <h2 class="text-lg font-semibold text-white">Comunidad</h2>
            <p class="mt-2 text-sm text-gray-400">Guarda este contenido en tus favoritos o pendientes; aparecerá en las secciones personales de {{ $meta['label'] }}.</p>
        </section>
    @endif
</section>
@endsection
