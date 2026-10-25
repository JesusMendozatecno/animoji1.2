@extends('layouts.public')

@section('title', $chapter['label'].' · '.$manga['title'].' · ANIMOJI')

@section('content')
<section
    id="manga-reader"
    class="mx-auto max-w-5xl px-4 py-8 sm:px-6"
    data-save-url="{{ $user ? route('personal.progreso') : '' }}"
    data-manga-id="{{ $manga['id'] }}"
    data-chapter-id="{{ $chapter['id'] }}"
    data-previous-url="{{ $prevUrl ?? '' }}"
    data-next-url="{{ $nextUrl ?? '' }}"
>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ $manga['url'] }}" class="text-sm text-gray-400 transition hover:text-white">← Volver a {{ $manga['title'] }}</a>
        <span class="rounded-full bg-violet-500/15 px-3 py-1 text-xs font-semibold text-violet-300 ring-1 ring-violet-400/40">
            {{ $chapter['label'] }}@if ($chapter['title']) · {{ $chapter['title'] }}@endif
        </span>
    </div>

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3">
        <div class="flex items-center gap-3">
            <div class="h-1.5 w-32 overflow-hidden rounded-full bg-white/10 sm:w-44" role="progressbar" aria-label="Progreso del capítulo" aria-valuemin="0" aria-valuemax="100">
                <div id="reader-progress-bar" class="h-full rounded-full bg-gradient-to-r from-violet-500 to-fuchsia-500 transition-[width]" style="width: 0%"></div>
            </div>
            <span id="reader-page-count" class="text-xs tabular-nums text-gray-400">Pág. 1 / {{ count($pages) ?: '—' }}</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="flex rounded-full border border-white/15 p-0.5 text-xs font-semibold" aria-label="Modo de lectura">
                <button id="vertical-mode" type="button" class="rounded-full bg-violet-600 px-3 py-1.5 text-white transition" aria-pressed="true">⇅ Vertical</button>
                <button id="paged-mode" type="button" class="rounded-full px-3 py-1.5 text-gray-400 transition hover:text-white" aria-pressed="false">⛶ Paginado</button>
            </div>

            <label class="sr-only" for="chapter-select">Capítulo</label>
            <select id="chapter-select" class="rounded-full border border-white/15 bg-[#0a0a14] px-3 py-1.5 text-xs text-gray-200 outline-none">
                @foreach ($chapters as $item)
                    <option value="{{ $item['url'] }}" @selected($item['current'])>
                        {{ $item['label'] }}{{ $item['current'] ? ' (actual)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if (count($pages) === 0)
        <div class="mt-6 rounded-2xl border border-dashed border-white/15 bg-white/[0.02] px-6 py-16 text-center">
            <p class="text-4xl" aria-hidden="true">📖</p>
            <h1 class="mt-3 text-lg font-semibold text-white">Este capítulo no tiene páginas disponibles</h1>
            <p class="mx-auto mt-1 max-w-md text-sm text-gray-500">
                {{ $chapter['label'] }} todavía no tiene imágenes registradas. Cuando un administrador las añada
                desde Biblioteca → Manga → Capítulos, podrás leerlo aquí.
            </p>
        </div>
    @else
        <div id="vertical-reader" class="mt-4 max-h-[75vh] overflow-y-auto rounded-2xl border border-white/10 bg-black/40 p-2">
            @foreach ($pages as $index => $page)
                <div id="manga-page-{{ $index + 1 }}" class="relative mb-2">
                    <p class="page-load-error hidden grid min-h-64 place-items-center rounded-xl bg-white/5 text-sm text-red-300">
                        ⚠️ No se pudo cargar la página {{ $page['number'] }}.
                    </p>
                    <img
                        src="{{ $page['url'] }}"
                        alt="Página {{ $page['number'] }}"
                        loading="lazy"
                        data-page-index="{{ $index + 1 }}"
                        class="mx-auto w-full rounded-xl"
                    >
                    <p class="mt-1 text-center text-[11px] text-gray-600">Página {{ $page['number'] }}</p>
                </div>
            @endforeach
        </div>

        <div id="paged-reader" class="mt-4 hidden">
            <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-black/40">
                <p id="paged-load-error" class="hidden min-h-[60vh] place-items-center px-6 text-center text-sm text-red-300">
                    ⚠️ No se pudo cargar esta página. Comprueba la imagen o inténtalo más tarde.
                </p>
                <img id="paged-image" src="" alt="" class="mx-auto max-h-[75vh] w-auto">
                <button
                    id="previous-page-arrow"
                    type="button"
                    aria-label="Página anterior"
                    class="absolute left-2 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-black/60 text-2xl text-white transition hover:bg-black/80 disabled:opacity-30"
                >‹</button>
                <button
                    id="next-page-arrow"
                    type="button"
                    aria-label="Página siguiente"
                    class="absolute right-2 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-black/60 text-2xl text-white transition hover:bg-black/80 disabled:opacity-30"
                >›</button>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-center gap-2">
                <button id="previous-page" type="button" class="rounded-full border border-white/15 px-4 py-2 text-sm text-gray-300 transition hover:border-white/40 hover:text-white disabled:opacity-40">← Página anterior</button>
                <div class="flex max-w-md flex-wrap justify-center gap-1">
                    @foreach ($pages as $index => $page)
                        <button
                            type="button"
                            data-go-to-page="{{ $index + 1 }}"
                            aria-label="Ir a la página {{ $page['number'] }}"
                            class="page-dot h-7 w-7 rounded-full text-xs transition {{ $index === 0 ? 'bg-violet-600 font-bold text-white' : 'text-gray-500 hover:bg-white/10' }}"
                        >{{ $page['number'] }}</button>
                    @endforeach
                </div>
                <button id="next-page" type="button" class="rounded-full border border-white/15 px-4 py-2 text-sm text-gray-300 transition hover:border-white/40 hover:text-white disabled:opacity-40">Página siguiente →</button>
            </div>
        </div>
    <p id="reader-save-status" class="mt-2 min-h-5 text-xs text-red-300" role="status" aria-live="polite"></p>

    <script type="application/json" id="reader-data">
        @json(['pages' => $pages, 'progress' => $userProgress])
    </script>
    <script>
        (() => {
            const reader = document.getElementById('manga-reader');
            const dataElement = document.getElementById('reader-data');
            if (!reader || !dataElement) return;

            const data = JSON.parse(dataElement.textContent ?? '{}');
            const pages = data.pages ?? [];
            const saved = data.progress;
            const vertical = document.getElementById('vertical-reader');
            const paged = document.getElementById('paged-reader');
            const image = document.getElementById('paged-image');
            const progressBar = document.getElementById('reader-progress-bar');
            const pageCount = document.getElementById('reader-page-count');
            const saveStatus = document.getElementById('reader-save-status');
            const previousUrl = reader.dataset.previousUrl;
            const nextUrl = reader.dataset.nextUrl;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            let mode = 'vertical';
            let current = Math.max(1, Math.min(pages.length || 1, Number(saved?.position) || 1));
            let saveTimer;
            let lastSave = 0;

            const setPage = (page, save = true) => {
                if (page < 1 || page > pages.length) return;
                current = page;
                if (image) {
                    image.src = pages[current - 1].url;
                    image.alt = `Página ${pages[current - 1].number}`;
                }
                document.querySelectorAll('.page-dot').forEach((button) => {
                    const active = Number(button.dataset.goToPage) === current;
                    button.classList.toggle('bg-violet-600', active);
                    button.classList.toggle('font-bold', active);
                    button.classList.toggle('text-white', active);
                    button.classList.toggle('text-gray-500', !active);
                });
                if (pageCount) pageCount.textContent = `Pág. ${current} / ${pages.length || '—'}`;
                if (progressBar) progressBar.style.width = `${pages.length ? (current / pages.length) * 100 : 0}%`;

                const previousDisabled = current === 1 && !previousUrl;
                const nextDisabled = current === pages.length && !nextUrl;
                ['previous-page', 'previous-page-arrow'].forEach((id) => {
                    const button = document.getElementById(id);
                    if (button) button.disabled = previousDisabled;
                });
                ['next-page', 'next-page-arrow'].forEach((id) => {
                    const button = document.getElementById(id);
                    if (button) button.disabled = nextDisabled;
                });

                if (save) saveProgress();
            };

            const navigateChapter = (url) => {
                if (url) window.location.href = url;
            };

            const previous = () => {
                if (current > 1) setPage(current - 1);
                else navigateChapter(previousUrl);
            };

            const next = () => {
                if (current < pages.length) setPage(current + 1);
                else navigateChapter(nextUrl);
            };

            const setMode = (nextMode) => {
                mode = nextMode;
                if (vertical) vertical.classList.toggle('hidden', mode !== 'vertical');
                if (paged) paged.classList.toggle('hidden', mode !== 'paged');
                document.getElementById('vertical-mode')?.classList.toggle('bg-violet-600', mode === 'vertical');
                document.getElementById('vertical-mode')?.classList.toggle('text-white', mode === 'vertical');
                document.getElementById('vertical-mode')?.classList.toggle('text-gray-400', mode !== 'vertical');
                document.getElementById('paged-mode')?.classList.toggle('bg-violet-600', mode === 'paged');
                document.getElementById('paged-mode')?.classList.toggle('text-white', mode === 'paged');
                document.getElementById('paged-mode')?.classList.toggle('text-gray-400', mode !== 'paged');
                document.getElementById('vertical-mode')?.setAttribute('aria-pressed', String(mode === 'vertical'));
                document.getElementById('paged-mode')?.setAttribute('aria-pressed', String(mode === 'paged'));

                if (mode === 'paged') setPage(current);
                else document.getElementById(`manga-page-${current}`)?.scrollIntoView({ block: 'start', behavior: 'smooth' });
            };

            const saveProgress = (final = false) => {
                if (!reader.dataset.saveUrl || pages.length === 0) return;
                const now = Date.now();
                if (!final && now - lastSave < 5_000) return;
                lastSave = now;
                const payload = {
                    section: 'manga',
                    id: Number(reader.dataset.mangaId),
                    target: 'chapter',
                    target_id: Number(reader.dataset.chapterId),
                    position: current,
                    percentage: (current / pages.length) * 100,
                    completed: current >= pages.length,
                };

                fetch(reader.dataset.saveUrl, {
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
                    if (saveStatus) saveStatus.textContent = '';
                }).catch(() => {
                    if (saveStatus) saveStatus.textContent = 'No se pudo guardar tu progreso. Comprueba tu conexión e inténtalo de nuevo.';
                });
            };

            document.getElementById('vertical-mode')?.addEventListener('click', () => setMode('vertical'));
            document.getElementById('paged-mode')?.addEventListener('click', () => setMode('paged'));
            document.getElementById('chapter-select')?.addEventListener('change', (event) => navigateChapter(event.target.value));
            document.getElementById('previous-page')?.addEventListener('click', previous);
            document.getElementById('previous-page-arrow')?.addEventListener('click', previous);
            document.getElementById('next-page')?.addEventListener('click', next);
            document.getElementById('next-page-arrow')?.addEventListener('click', next);
            document.querySelectorAll('[data-go-to-page]').forEach((button) => {
                button.addEventListener('click', () => setPage(Number(button.dataset.goToPage)));
            });

            vertical?.addEventListener('scroll', () => {
                if (mode !== 'vertical' || pages.length === 0) return;
                const scrolled = vertical.scrollTop + vertical.clientHeight * 0.5;
                const ratio = vertical.scrollHeight > 0 ? scrolled / vertical.scrollHeight : 1;
                const page = Math.min(pages.length, Math.max(1, Math.ceil(ratio * pages.length)));
                if (page !== current) {
                    current = page;
                    setPage(current, false);
                    window.clearTimeout(saveTimer);
                    saveTimer = window.setTimeout(() => saveProgress(), 1_500);
                }
            }, { passive: true });

            document.querySelectorAll('#vertical-reader img').forEach((pageImage) => {
                pageImage.addEventListener('error', () => pageImage.previousElementSibling?.classList.remove('hidden'));
                pageImage.addEventListener('load', () => pageImage.previousElementSibling?.classList.add('hidden'));
            });
            image?.addEventListener('error', () => document.getElementById('paged-load-error')?.classList.add('grid'));
            image?.addEventListener('load', () => document.getElementById('paged-load-error')?.classList.remove('grid'));

            document.addEventListener('keydown', (event) => {
                if (mode !== 'paged' || pages.length === 0) return;
                if (event.key === 'ArrowRight') next();
                if (event.key === 'ArrowLeft') previous();
            });
            window.addEventListener('pagehide', () => {
                window.clearTimeout(saveTimer);
                saveProgress(true);
            });

            if (pages.length > 0) {
                setPage(current, false);
                if (saved?.position > 1 && mode === 'vertical') {
                    window.setTimeout(() => document.getElementById(`manga-page-${current}`)?.scrollIntoView({ block: 'start' }), 120);
                }
            }
        })();
    </script>
@endif
</section>
@endsection
