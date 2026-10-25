@extends('layouts.catalog')

@section('title', $config['favorites'].' · ANIMOJI')

@section('content')
<header class="mb-6">
    <h1 class="font-display text-3xl font-bold italic text-white">{{ $config['favorites'] }}</h1>
    <p class="mt-1 text-sm text-gray-400">
        {{ $items->total() }} elemento{{ $items->total() === 1 ? '' : 's' }} en tus favoritos de {{ $config['label'] }}.
    </p>
</header>

@if ($items->isEmpty())
    <div class="rounded-2xl border border-dashed border-white/15 bg-white/[0.02] px-6 py-16 text-center">
        <p class="text-4xl">🤍</p>
        <h2 class="mt-3 text-lg font-semibold text-white">Aún no tienes favoritos</h2>
        <p class="mx-auto mt-1 max-w-md text-sm text-gray-400">
            Abre el catálogo de {{ $config['label'] }} y marca el corazón de cualquier tarjeta para guardarlo aquí.
        </p>
        <a href="{{ route($config['indexRoute']) }}" class="mt-6 inline-block rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-gray-950 transition hover:bg-gray-200">
            Ir al catálogo de {{ $config['label'] }}
        </a>
    </div>
@else
    <div class="flex flex-col gap-3">
        @foreach ($items as $item)
            @php
                $cover = $item['cover'] ?? null;
                $cover = $cover
                    ? (str_starts_with($cover, '/') || str_starts_with($cover, 'http') ? $cover : '/'.$cover)
                    : '/images/'.$section.'/cover-1.jpg';
            @endphp
            <article class="group flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.03] p-3 transition hover:border-white/25 hover:bg-white/[0.06]">
                @if ($item['url'])
                    <a href="{{ $item['url'] }}" class="h-24 w-20 shrink-0 overflow-hidden rounded-xl bg-black/40">
                        <img src="{{ $cover }}" alt="{{ $item['title'] }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                    </a>
                @else
                    <div class="h-24 w-20 shrink-0 overflow-hidden rounded-xl bg-black/40">
                        <img src="{{ $cover }}" alt="{{ $item['title'] }}" loading="lazy" class="h-full w-full object-cover">
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    @if ($item['url'])
                        <a href="{{ $item['url'] }}" class="block truncate font-semibold text-white transition hover:text-gray-200" title="{{ $item['title'] }}">{{ $item['title'] }}</a>
                    @else
                        <span class="block truncate font-semibold text-white" title="{{ $item['title'] }}">{{ $item['title'] }}</span>
                    @endif
                    @if (! empty($item['context']))
                        <p class="mt-0.5 truncate text-sm text-gray-300">{{ $item['context'] }}</p>
                    @elseif (! empty($item['subtitle']))
                        <p class="mt-0.5 truncate text-sm text-gray-400">{{ $item['subtitle'] }}</p>
                    @endif
                </div>
                <form method="POST" action="{{ route('personal.favoritos.toggle') }}">
                    @csrf
                    <input type="hidden" name="section" value="{{ $section }}">
                    <input type="hidden" name="id" value="{{ $item['id'] }}">
                    <button type="submit" aria-label="Quitar {{ $item['title'] }} de favoritos" title="Quitar de favoritos" class="rounded-full border border-red-400/40 p-2 text-red-300 transition hover:border-red-400/70 hover:bg-red-500/10">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21s-7.5-4.9-10-9.2C.6 8.6 2.4 5 6 5c2.1 0 3.4 1.1 4 2 .6-.9 1.9-2 4-2 3.6 0 5.4 3.6 4 6.8C19.5 16.1 12 21 12 21z"></path></svg>
                    </button>
                </form>
            </article>
        @endforeach
    </div>
@endif

<div class="mt-8">
    {{ $items->links() }}
</div>
@endsection
