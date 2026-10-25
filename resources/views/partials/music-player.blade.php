<div
    id="catalog-music-player"
    class="fixed inset-x-0 bottom-0 z-40 hidden border-t border-white/10 bg-[#0a0a14]/95 backdrop-blur"
    role="region"
    aria-label="Reproductor de música"
    data-save-url="{{ auth()->check() ? route('personal.progreso') : '' }}"
>
    <div class="h-1 w-full bg-white/10">
        <div id="music-progress-bar" class="h-full bg-gradient-to-r from-fuchsia-500 to-red-500 transition-[width] duration-150" style="width: 0%"></div>
    </div>
    <p id="music-player-status" class="hidden border-b border-red-500/30 bg-red-500/10 px-4 py-1.5 text-center text-xs text-red-300" role="status" aria-live="polite"></p>

    <div class="mx-auto flex max-w-7xl items-center gap-3 px-3 py-2 sm:gap-4 sm:px-5">
        <div class="flex min-w-0 flex-1 items-center gap-3 sm:flex-none sm:basis-64">
            <img id="music-player-cover" src="/images/musica/cover-1.jpg" alt="" class="h-11 w-11 shrink-0 rounded-lg object-cover">
            <div class="min-w-0">
                <p id="music-player-title" class="truncate text-sm font-semibold text-white"></p>
                <p id="music-player-artist" class="truncate text-xs text-gray-400"></p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
            <button id="music-player-previous" type="button" aria-label="Anterior" class="hidden rounded-full p-2 text-gray-300 transition hover:text-white sm:block">|◀</button>
            <button id="music-player-toggle" type="button" aria-label="Reproducir" class="grid h-10 w-10 place-items-center rounded-full bg-white text-gray-950 transition hover:bg-gray-200">▶</button>
            <button id="music-player-next" type="button" aria-label="Siguiente" class="rounded-full p-2 text-gray-300 transition hover:text-white">▶|</button>
        </div>

        <div class="hidden min-w-0 flex-1 items-center gap-2 sm:flex">
            <span id="music-player-time" class="w-10 text-right text-[11px] tabular-nums text-gray-400">0:00</span>
            <input id="music-player-seek" type="range" min="0" max="100" step="0.1" value="0" aria-label="Progreso de la canción" class="h-1 w-full cursor-pointer appearance-none rounded-full bg-white/20 accent-fuchsia-500">
            <span id="music-player-duration" class="w-10 text-[11px] tabular-nums text-gray-400">0:00</span>
        </div>

        <div class="hidden shrink-0 items-center gap-1 lg:flex">
            <label class="sr-only" for="music-player-volume">Volumen</label>
            <span aria-hidden="true">🔊</span>
            <input id="music-player-volume" type="range" min="0" max="1" step="0.05" value="1" class="h-1 w-20 cursor-pointer appearance-none rounded-full bg-white/20 accent-fuchsia-500">
            <button id="music-player-shuffle" type="button" aria-label="Reproducción aleatoria" aria-pressed="false" class="rounded-full p-2 text-gray-400 transition hover:text-fuchsia-400">🔀</button>
            <button id="music-player-repeat" type="button" aria-label="Repetir" class="rounded-full p-2 text-gray-400 transition hover:text-fuchsia-400">🔁</button>
            <button id="music-player-queue-toggle" type="button" aria-label="Cola de reproducción" aria-expanded="false" class="rounded-full p-2 text-gray-400 transition hover:text-white">☷</button>
        </div>
    </div>

    <div id="music-player-queue" class="hidden max-h-64 overflow-y-auto border-t border-white/10 px-4 py-3 sm:px-6">
        <p id="music-player-queue-title" class="mb-2 text-xs font-bold uppercase tracking-widest text-gray-500"></p>
        <ol id="music-player-queue-list" class="flex flex-col gap-1"></ol>
    </div>

    <audio id="catalog-music-audio" preload="metadata"></audio>
</div>

<script type="application/json" id="music-player-tracks">@json($musicPlayerTracks ?? [])</script>
<script>
    (() => {
        const player = document.getElementById('catalog-music-player');
        const audio = document.getElementById('catalog-music-audio');
        const tracksElement = document.getElementById('music-player-tracks');
        if (!player || !audio || !tracksElement) return;

        const status = document.getElementById('music-player-status');
        const serverTracks = JSON.parse(tracksElement.textContent ?? '[]');
        const storageKey = 'animoji.music-player.v1';
        const validTrack = (track) => {
            if (!track || !Number.isInteger(Number(track.id)) || typeof track.title !== 'string' || typeof track.url !== 'string') return false;
            try {
                return ['http:', 'https:'].includes(new URL(track.url, window.location.href).protocol);
            } catch {
                return false;
            }
        };
        const safeTracks = (tracks) => Array.isArray(tracks)
            ? tracks.filter(validTrack).map((track) => ({ ...track, id: Number(track.id) }))
            : [];
        let savedState = null;
        let queue = safeTracks(serverTracks);
        let current = null;
        let repeat = 'off';
        let shuffle = false;
        let queueVisible = false;
        let lastSavedAt = 0;
        let pendingPosition = 0;

        try {
            savedState = JSON.parse(window.localStorage.getItem(storageKey) ?? 'null');
        } catch {
            status.textContent = 'No se pudo recuperar la cola de reproducción guardada.';
            status.classList.remove('hidden');
        }

        if (savedState && Array.isArray(savedState.queue)) {
            queue = safeTracks(savedState.queue);
            repeat = ['off', 'one', 'all'].includes(savedState.repeat) ? savedState.repeat : 'off';
            shuffle = Boolean(savedState.shuffle);
            current = queue.find((track) => track.id === Number(savedState.currentId)) ?? null;
            pendingPosition = Math.max(0, Number(savedState.position) || 0);
            const volume = Number(savedState.volume);
            audio.volume = Number.isFinite(volume) ? Math.min(1, Math.max(0, volume)) : 1;
        }

        const formatTime = (seconds) => {
            if (!Number.isFinite(seconds) || seconds < 0) return '0:00';
            const minutes = Math.floor(seconds / 60);
            return `${minutes}:${String(Math.floor(seconds % 60)).padStart(2, '0')}`;
        };

        const setStatus = (message) => {
            status.textContent = message;
            status.classList.toggle('hidden', message === '');
        };

        const persist = () => {
            try {
                window.localStorage.setItem(storageKey, JSON.stringify({
                    queue,
                    currentId: current?.id ?? null,
                    position: audio.currentTime || pendingPosition,
                    volume: audio.volume,
                    repeat,
                    shuffle,
                }));
            } catch {
                setStatus('No se pudo guardar la cola de reproducción en este navegador.');
            }
        };

        const renderQueue = () => {
            const list = document.getElementById('music-player-queue-list');
            list.replaceChildren();
            document.getElementById('music-player-queue-title').textContent = `Cola · ${queue.length} canción${queue.length === 1 ? '' : 'es'}`;

            queue.forEach((track, index) => {
                const row = document.createElement('li');
                row.className = `flex items-center gap-3 rounded-lg px-2 py-1.5 transition ${track.id === current?.id ? 'bg-white/10' : 'hover:bg-white/5'}`;
                const play = document.createElement('button');
                play.type = 'button';
                play.className = 'min-w-0 flex-1 text-left';
                const title = document.createElement('span');
                title.className = 'block truncate text-sm text-white';
                title.textContent = track.title;
                const artist = document.createElement('span');
                artist.className = 'block truncate text-xs text-gray-500';
                artist.textContent = track.artist || '—';
                play.append(title, artist);
                play.addEventListener('click', () => playAt(index));

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'rounded-full p-1.5 text-gray-500 transition hover:text-red-400';
                remove.setAttribute('aria-label', `Quitar ${track.title} de la cola`);
                remove.textContent = '✕';
                remove.addEventListener('click', () => removeFromQueue(index));
                row.append(play, remove);
                list.append(row);
            });
        };

        const updatePlayer = (renderQueueList = false) => {
            player.classList.toggle('hidden', current === null);
            document.body.classList.toggle('music-player-active', current !== null);
            if (!current) return;

            document.getElementById('music-player-title').textContent = current.title;
            document.getElementById('music-player-artist').textContent = current.artist || 'Desconocido';
            document.getElementById('music-player-cover').src = current.cover || '/images/musica/cover-1.jpg';
            document.getElementById('music-player-cover').alt = current.title;
            document.getElementById('music-player-toggle').textContent = audio.paused ? '▶' : 'Ⅱ';
            document.getElementById('music-player-toggle').setAttribute('aria-label', audio.paused ? 'Reproducir' : 'Pausar');
            document.getElementById('music-player-time').textContent = formatTime(audio.currentTime);
            document.getElementById('music-player-duration').textContent = formatTime(audio.duration || Number(current.duration) || 0);
            document.getElementById('music-player-seek').value = audio.duration ? String((audio.currentTime / audio.duration) * 100) : '0';
            document.getElementById('music-progress-bar').style.width = audio.duration ? `${(audio.currentTime / audio.duration) * 100}%` : '0%';
            document.getElementById('music-player-repeat').textContent = repeat === 'one' ? '🔂' : '🔁';
            document.getElementById('music-player-repeat').classList.toggle('text-fuchsia-400', repeat !== 'off');
            document.getElementById('music-player-shuffle').classList.toggle('text-fuchsia-400', shuffle);
            document.getElementById('music-player-shuffle').setAttribute('aria-pressed', String(shuffle));
            document.getElementById('music-player-queue').classList.toggle('hidden', !queueVisible);
            document.getElementById('music-player-queue-toggle').setAttribute('aria-expanded', String(queueVisible));
            if (renderQueueList) renderQueue();
        };

        const saveProgress = (force = false) => {
            if (!current || !player.dataset.saveUrl || !Number.isFinite(audio.duration) || audio.duration <= 0) return;
            const now = Date.now();
            if (!force && now - lastSavedAt < 10_000) return;
            lastSavedAt = now;

            fetch(player.dataset.saveUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    section: 'musica',
                    id: Number(current.id),
                    position: Math.floor(audio.currentTime),
                    percentage: Math.min(100, (audio.currentTime / audio.duration) * 100),
                }),
                keepalive: force,
            }).then((response) => {
                if (!response.ok) throw new Error('No se pudo guardar el progreso de reproducción.');
                setStatus('');
            }).catch(() => setStatus('No se pudo guardar el progreso de reproducción.'));
        };

        const start = async (track, shouldPlay = true) => {
            if (!validTrack(track)) {
                setStatus('Esta canción no tiene una fuente de audio válida.');
                return;
            }
            const changed = current?.id !== track.id;
            current = track;
            pendingPosition = changed ? Math.max(0, Number(track.position) || 0) : 0;
            if (changed || audio.src !== new URL(track.url, window.location.href).href) {
                audio.src = track.url;
                audio.load();
            }
            updatePlayer(true);
            persist();
            setStatus('');

            if (shouldPlay) {
                try {
                    await audio.play();
                } catch {
                    setStatus('El navegador bloqueó la reproducción o la fuente no es compatible.');
                }
            }
            updatePlayer();
        };

        const playAt = (index) => {
            const track = queue[index];
            if (!track) return;
            start(track);
        };

        const playNext = (automatic = false) => {
            if (queue.length === 0) return;
            const index = queue.findIndex((track) => track.id === current?.id);
            let nextIndex = index + 1;
            if (shuffle && queue.length > 1) {
                const candidates = queue.map((_, candidate) => candidate).filter((candidate) => candidate !== index);
                nextIndex = candidates[Math.floor(Math.random() * candidates.length)];
            } else if (nextIndex >= queue.length) {
                if (repeat === 'all') nextIndex = 0;
                else if (automatic) return;
                else nextIndex = 0;
            }
            playAt(nextIndex);
        };

        const playPrevious = () => {
            if (audio.currentTime > 3) {
                audio.currentTime = 0;
                updatePlayer();
                return;
            }
            const index = queue.findIndex((track) => track.id === current?.id);
            playAt(index > 0 ? index - 1 : repeat === 'all' ? queue.length - 1 : 0);
        };

        const removeFromQueue = (index) => {
            const [removed] = queue.splice(index, 1);
            if (removed?.id === current?.id) {
                audio.pause();
                const replacement = queue[index] ?? queue[index - 1] ?? null;
                current = null;
                if (replacement) start(replacement, false);
                else audio.removeAttribute('src');
            }
            persist();
            updatePlayer(true);
        };

        document.querySelectorAll('[data-play-track]').forEach((button) => {
            button.addEventListener('click', () => {
                const id = Number(button.dataset.playTrack);
                let index = queue.findIndex((track) => Number(track.id) === id);
                if (index < 0) {
                    queue = safeTracks(serverTracks);
                    index = queue.findIndex((track) => Number(track.id) === id);
                }
                playAt(index);
            });
        });
        document.getElementById('music-player-toggle').addEventListener('click', async () => {
            if (audio.paused) {
                try {
                    await audio.play();
                    setStatus('');
                } catch {
                    setStatus('No se pudo reanudar la reproducción.');
                }
            } else {
                audio.pause();
                saveProgress(true);
            }
            updatePlayer();
        });
        document.getElementById('music-player-previous').addEventListener('click', playPrevious);
        document.getElementById('music-player-next').addEventListener('click', () => playNext());
        document.getElementById('music-player-seek').addEventListener('input', (event) => {
            if (audio.duration) audio.currentTime = (Number(event.target.value) / 100) * audio.duration;
            updatePlayer();
        });
        document.getElementById('music-player-volume').addEventListener('input', (event) => {
            audio.volume = Math.min(1, Math.max(0, Number(event.target.value)));
            persist();
        });
        document.getElementById('music-player-shuffle').addEventListener('click', () => {
            shuffle = !shuffle;
            persist();
            updatePlayer();
        });
        document.getElementById('music-player-repeat').addEventListener('click', () => {
            repeat = repeat === 'off' ? 'all' : repeat === 'all' ? 'one' : 'off';
            persist();
            updatePlayer();
        });
        document.getElementById('music-player-queue-toggle').addEventListener('click', () => {
            queueVisible = !queueVisible;
            updatePlayer(true);
        });

        audio.addEventListener('loadedmetadata', () => {
            if (pendingPosition > 0 && pendingPosition < audio.duration - 2) audio.currentTime = pendingPosition;
            pendingPosition = 0;
            updatePlayer();
        });
        audio.addEventListener('timeupdate', () => {
            updatePlayer();
            saveProgress();
            persist();
        });
        audio.addEventListener('play', updatePlayer);
        audio.addEventListener('pause', () => {
            updatePlayer();
            persist();
        });
        audio.addEventListener('ended', () => {
            if (repeat === 'one') {
                audio.currentTime = 0;
                audio.play().catch(() => setStatus('No se pudo reiniciar la canción.'));
            } else {
                playNext(true);
            }
        });
        audio.addEventListener('error', () => setStatus('No se pudo reproducir esta fuente de audio. Comprueba el formato o el archivo.'));
        window.addEventListener('pagehide', () => {
            persist();
            saveProgress(true);
        });

        if (current) {
            audio.src = current.url;
            audio.volume = Math.min(1, Math.max(0, Number(savedState?.volume ?? 1)));
            audio.addEventListener('loadedmetadata', updatePlayer, { once: true });
            updatePlayer();
        } else {
            updatePlayer();
        }
    })();
</script>

<style>
    body.music-player-active { padding-bottom: 5rem; }
</style>
