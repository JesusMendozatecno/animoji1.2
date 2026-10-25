@extends('layouts.public')

@section('title', $episode['label'].' · '.$anime['title'].' · ANIMOJI')

@section('content')
<section
    id="anime-player"
    class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
    data-save-url="{{ $user ? route('personal.progreso') : '' }}"
    data-anime-id="{{ $anime['id'] }}"
    data-episode-id="{{ $episode['id'] }}"
>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ $anime['url'] }}" class="text-sm text-gray-400 transition hover:text-white">← Volver a {{ $anime['title'] }}</a>
        <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-300 ring-1 ring-red-400/40">
            {{ $episode['label'] }}
        </span>
    </div>

    <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_300px]">
        <div class="min-w-0">
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black">
                @if ($episode['video_url'])
                    <video
                        id="anime-video"
                        src="{{ $episode['video_url'] }}"
                        class="aspect-video w-full bg-black"
                        controls
                        playsinline
                        preload="metadata"
                        aria-label="{{ $episode['label'] }} · {{ $anime['title'] }}"
                    ></video>
                @else
                    <div class="grid aspect-video w-full place-items-center bg-[#0a0a14] px-6 text-center">
                        <div>
                            <p class="text-5xl" aria-hidden="true">📺</p>
                            <h1 class="mt-4 text-lg font-semibold text-white">Este episodio no tiene fuente de vídeo</h1>
                            <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">
                                Aún no se ha registrado una fuente para {{ $episode['label'] }}.
                                Cuando un administrador la añada desde Biblioteca → Anime → Episodios, podrás verlo aquí.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <p id="progress-save-status" class="mt-2 min-h-5 text-xs text-red-300" role="status" aria-live="polite"></p>

            <div class="mt-5">
                <h1 class="text-2xl font-black text-white">
                    {{ $episode['label'] }}
                    @if ($episode['title'])
                        <span class="font-semibold text-gray-300"> · {{ $episode['title'] }}</span>
                    @endif
                </h1>
                @if ($episode['synopsis'])
                    <p class="mt-2 text-sm leading-relaxed text-gray-400">{{ $episode['synopsis'] }}</p>
                @endif
                @if ($userProgress && $userProgress['percentage'] > 0)
                    <p class="mt-3 text-xs text-gray-500">
                        Tu progreso:
                        <span class="text-amber-300">{{ round($userProgress['percentage']) }}%</span>
                        @if ($userProgress['completed'])
                            <span class="text-emerald-400"> · ✅ completado</span>
                        @endif
                    </p>
                @endif

                <div class="mt-5 flex flex-wrap gap-3">
                    @if ($prevUrl)
                        <a href="{{ $prevUrl }}" class="rounded-full border border-white/15 px-5 py-2 text-sm font-semibold text-gray-300 transition hover:border-white/40 hover:text-white">
                            ← Episodio anterior
                        </a>
                    @endif
                    @if ($nextUrl)
                        <a href="{{ $nextUrl }}" class="rounded-full bg-red-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-red-500">
                            Siguiente episodio →
                        </a>
                    @else
                        <span class="self-center text-xs text-gray-600">Último episodio publicado</span>
                    @endif
                </div>
            </div>
        </div>

        <aside class="rounded-2xl border border-white/10 bg-white/[0.02] p-4">
            <h2 class="text-sm font-bold uppercase tracking-widest text-gray-400">Episodios · {{ count($episodes) }}</h2>
            <ol class="mt-3 flex max-h-[26rem] flex-col gap-1.5 overflow-y-auto pr-1">
                @foreach ($episodes as $index => $item)
                    <li>
                        <a
                            href="{{ $item['url'] }}"
                            @if ($item['current']) aria-current="page" @endif
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition {{ $item['current'] ? 'bg-red-500/15 text-white ring-1 ring-red-400/50' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                        >
                            <span class="w-8 shrink-0 text-xs font-bold {{ $item['current'] ? 'text-red-300' : 'text-gray-600' }}">{{ $index + 1 }}</span>
                            <span class="min-w-0 flex-1 truncate">
                                {{ $item['label'] }}@if ($item['title'])<span class="text-gray-500"> · {{ $item['title'] }}</span>@endif
                            </span>
                            @if ($item['current'])
                                <span class="shrink-0 text-xs text-red-300" aria-label="Episodio actual">▶</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ol>
        </aside>
    </div>
</section>

<script type="application/json" id="saved-player-progress">@json($userProgress)</script>
<script>
    (() => {
        const player = document.getElementById('anime-player');
        const video = document.getElementById('anime-video');
        const status = document.getElementById('progress-save-status');
        if (!player || !video || !status || !player.dataset.saveUrl) return;

        const saved = JSON.parse(document.getElementById('saved-player-progress')?.textContent ?? 'null');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        let lastSave = 0;

        const progressPayload = () => {
            const duration = video.duration;
            const position = video.currentTime;
            if (!Number.isFinite(duration) || duration <= 0 || position <= 0) return null;

            const percentage = Math.min(100, (position / duration) * 100);
            return {
                section: 'anime',
                id: Number(player.dataset.animeId),
                target: 'episode',
                target_id: Number(player.dataset.episodeId),
                position: Math.floor(position),
                percentage,
                completed: percentage >= 95,
            };
        };

        const saveProgress = (final = false) => {
            const payload = progressPayload();
            if (!payload) return;

            if (!final && Date.now() - lastSave < 10_000) return;
            lastSave = Date.now();
            fetch(player.dataset.saveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
                keepalive: final,
            }).then((response) => {
                if (!response.ok) throw new Error('No se pudo guardar el progreso.');
                status.textContent = '';
            }).catch(() => {
                status.textContent = 'No se pudo guardar tu progreso. Comprueba tu conexión e inténtalo de nuevo.';
            });
        };

        if (saved && !saved.completed && Number(saved.position) > 0) {
            video.addEventListener('loadedmetadata', () => {
                if (saved.position < video.duration - 2) video.currentTime = saved.position;
            }, { once: true });
        }

        video.addEventListener('timeupdate', () => saveProgress());
        video.addEventListener('pause', () => saveProgress(true));
        video.addEventListener('ended', () => saveProgress(true));
        window.addEventListener('pagehide', () => saveProgress(true));
    })();
</script>
@endsection
