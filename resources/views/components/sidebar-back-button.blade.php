@php
    $backDestination = request()->routeIs('dashboard') ? route('home') : route('dashboard');
@endphp

<a href="{{ $backDestination }}" data-sidebar-back data-sidebar-back-history="false"
    class="mx-4 mb-4 flex items-center gap-3 rounded-xl border border-white/15 px-4 py-3 text-sm font-medium text-gray-300 transition hover:border-white/30 hover:bg-white/5 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-400"
>
    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20z"></path></svg>
    Volver
</a>
