@extends('layouts.admin')

@section('title', 'Panel Administrativo · ANIMOJI')

@section('content')
@php
    $cards = [
        ['title' => 'Anime', 'total' => $stats['anime'], 'img' => '/images/admin/card-anime.jpg', 'icon' => 'M8 5v14l11-7z', 'border' => 'border-pink-500/60', 'btn' => 'bg-pink-500', 'glow' => 'shadow-[0_0_20px_rgba(236,72,153,0.3)]', 'route' => 'anime.index'],
        ['title' => 'Manga', 'total' => $stats['manga'], 'img' => '/images/admin/card-manga.jpg', 'icon' => 'M4 4h7v16H4zm9 0h7v16h-7z', 'border' => 'border-violet-500/60', 'btn' => 'bg-violet-500', 'glow' => 'shadow-[0_0_20px_rgba(139,92,246,0.3)]', 'route' => 'manga.index'],
        ['title' => 'Música', 'total' => $stats['music'], 'img' => '/images/admin/card-musica.jpg', 'icon' => 'M12 3v10.55A4 4 0 1 0 14 17V7h4V3z', 'border' => 'border-sky-500/60', 'btn' => 'bg-sky-500', 'glow' => 'shadow-[0_0_20px_rgba(56,189,248,0.3)]', 'route' => 'music.index'],
    ];
    $weekly = [3, 5, 4, 7, 5, 9, 6];
    $days = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
    $linePoints = collect($weekly)->map(fn (int $value, int $index) => (40 + $index * 75).','. (210 - ($value / 10) * 170))->implode(' ');
    $donuts = [
        ['title' => 'Anime', 'subtitle' => 'Distribución por categoría', 'total' => $stats['anime'], 'legend' => [['Acción', 45], ['Aventura', 30], ['Comedia', 25]]],
        ['title' => 'Manga', 'subtitle' => 'Distribución por categoría', 'total' => $stats['manga'], 'legend' => [['Shōnen', 50], ['Seinen', 30], ['Otros', 20]]],
        ['title' => 'Música', 'subtitle' => 'Distribución por género', 'total' => $stats['music'], 'legend' => [['J-POP', 60], ['Rock', 25], ['Otros', 15]]],
    ];
    $chartColors = ['#3b82f6', '#fb7185', '#fbbf24'];
@endphp

<section class="relative overflow-hidden rounded-2xl border border-white/10">
    <img src="/images/admin/banner.jpg" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-[#07070f] via-[#07070f]/70 to-transparent"></div>
    <div class="relative flex items-center justify-between gap-6 p-7 sm:p-9">
        <div class="flex items-center gap-4">
            <span class="flex h-12 w-12 items-end justify-center gap-1 pb-2" aria-hidden="true">
                <span class="w-2 rounded bg-gradient-to-t from-violet-600 to-fuchsia-400" style="height: 45%"></span>
                <span class="w-2 rounded bg-gradient-to-t from-violet-600 to-fuchsia-400" style="height: 75%"></span>
                <span class="w-2 rounded bg-gradient-to-t from-violet-600 to-fuchsia-400" style="height: 100%"></span>
            </span>
            <div>
                <h1 class="text-3xl font-bold text-white sm:text-4xl">Panel <span class="text-fuchsia-400 [text-shadow:0_0_16px_rgba(217,70,239,0.6)]">Administrativo</span></h1>
                <p class="mt-1 text-sm text-gray-400">Control total del sistema ANIMOJI</p>
            </div>
        </div>
        <p class="hidden max-w-[14rem] text-right text-sm italic leading-snug text-gray-300 lg:block">"Grandes historias<br>comienzan con una buena<br>organización."</p>
    </div>
</section>

<div class="mt-6 grid gap-5 xl:grid-cols-[1fr_1fr_1fr_320px]">
    @foreach ($cards as $card)
        <a href="{{ route($card['route']) }}" class="group relative isolate overflow-hidden rounded-2xl border-2 bg-[#0d0d1a] p-4 transition hover:-translate-y-1 {{ $card['border'] }} {{ $card['glow'] }}">
            <div class="relative h-28 overflow-hidden rounded-xl">
                <img src="{{ $card['img'] }}" alt="{{ $card['title'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                <span class="absolute bottom-2 left-2 grid h-9 w-9 place-items-center rounded-lg bg-black/70 text-white ring-1 ring-white/20">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $card['icon'] }}"></path></svg>
                </span>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <div>
                    <p class="text-lg font-bold text-white">{{ $card['title'] }}</p>
                    <p class="text-xs text-gray-500">Total: {{ $card['total'] }}</p>
                </div>
                <span class="grid h-9 w-9 place-items-center rounded-full text-white transition group-hover:scale-110 {{ $card['btn'] }}" aria-hidden="true">→</span>
            </div>
        </a>
    @endforeach

    <section class="rounded-2xl border border-violet-500/40 bg-[#0d0d1a] p-5 shadow-[0_0_20px_rgba(139,92,246,0.18)]">
        <div class="flex items-center gap-2">
            <span class="grid h-8 w-8 place-items-center rounded-lg bg-violet-500/20 text-violet-300" aria-hidden="true">👥</span>
            <h2 class="font-bold text-white">Usuarios &amp; Roles</h2>
        </div>
        <div class="mt-4 flex flex-col gap-3">
            <a href="{{ route('admin.usuarios') }}" class="flex items-center gap-3 rounded-xl border border-blue-400/30 bg-blue-500/10 px-4 py-3 transition hover:bg-blue-500/20">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-blue-500 text-white" aria-hidden="true">👤</span>
                <span class="flex-1 text-sm font-semibold text-white">Usuarios</span>
                <span class="text-sm text-gray-400">{{ $stats['users'] }}</span>
                <span class="text-gray-500" aria-hidden="true">›</span>
            </a>
            <a href="{{ route('admin.usuarios') }}" class="flex items-center gap-3 rounded-xl border border-violet-400/30 bg-violet-500/10 px-4 py-3 transition hover:bg-violet-500/20">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-violet-500 text-white" aria-hidden="true">🛡️</span>
                <span class="flex-1 text-sm font-semibold text-white">Admins</span>
                <span class="text-sm text-gray-400">{{ $stats['admins'] ?? 1 }}</span>
                <span class="text-gray-500" aria-hidden="true">›</span>
            </a>
        </div>
    </section>
</div>

<div class="mt-6 grid gap-5 xl:grid-cols-[1.4fr_1fr_1fr_1fr]">
    <section class="rounded-2xl border border-white/10 bg-[#0d0d1a] p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-violet-400" aria-hidden="true">📈</span>
                <h2 class="font-bold text-white">Actividad semanal</h2>
            </div>
            <span class="rounded-lg border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-gray-400">📅 Últimos 7 días ▾</span>
        </div>
        <p class="mt-3 flex items-center gap-1.5 text-xs text-gray-400">
            <span class="h-2.5 w-2.5 rounded-sm bg-blue-500"></span> Total de registros
        </p>
        <svg viewBox="0 0 520 240" class="mt-2 w-full" role="img" aria-label="Gráfica de actividad semanal">
            <g stroke="#1f2937" stroke-width="1">
                @foreach (range(0, 4) as $index)
                    <line x1="40" y1="{{ 40 + $index * 42 }}" x2="500" y2="{{ 40 + $index * 42 }}"></line>
                @endforeach
            </g>
            <g fill="#6b7280" font-size="12">
                @foreach ([9, 7, 5, 3, 1] as $index => $value)
                    <text x="22" y="{{ 44 + $index * 42 }}">{{ $value }}</text>
                @endforeach
                @foreach ($days as $index => $day)
                    <text x="{{ 36 + $index * 75 }}" y="232">{{ $day }}</text>
                @endforeach
            </g>
            <polyline points="{{ $linePoints }}" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linejoin="round"></polyline>
            @foreach ($weekly as $index => $value)
                <circle cx="{{ 40 + $index * 75 }}" cy="{{ 210 - ($value / 10) * 170 }}" r="5" fill="#3b82f6" stroke="#0d0d1a" stroke-width="2"></circle>
            @endforeach
        </svg>
    </section>

    @foreach ($donuts as $donut)
        @php
            $percentage = 0;
            $stops = [];
            foreach ($donut['legend'] as $index => [$label, $value]) {
                $stops[] = $chartColors[$index].' '.$percentage.'% '.($percentage + $value).'%';
                $percentage += $value;
            }
            $donutBackground = 'conic-gradient('.implode(', ', $stops).')';
        @endphp
        <section class="rounded-2xl border border-white/10 bg-[#0d0d1a] p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-bold text-white">{{ $donut['title'] }}</h2>
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-white/5 text-gray-400" aria-hidden="true">→</span>
            </div>
            <p class="text-xs text-gray-500">{{ $donut['subtitle'] }}</p>
            <div class="mt-4 flex justify-center">
                <div class="relative grid h-36 w-36 place-items-center rounded-full" style="background: {{ $donutBackground }}">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-[#0d0d1a] text-center text-sm font-bold leading-tight text-white">Total<br>{{ $donut['total'] }}</span>
                </div>
            </div>
            <ul class="mt-4 flex flex-col gap-1.5 text-xs">
                @foreach ($donut['legend'] as $index => [$label, $value])
                    <li class="flex items-center gap-2">
                        <span @class(['h-2.5 w-2.5 rounded-full', 'bg-blue-500' => $index === 0, 'bg-rose-400' => $index === 1, 'bg-amber-400' => $index === 2])></span>
                        <span class="text-gray-400">{{ $label }}</span>
                        <span class="ml-auto font-semibold text-gray-300">{{ $value }}%</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</div>
@endsection
