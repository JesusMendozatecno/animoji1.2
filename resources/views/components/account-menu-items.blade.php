@php
    $menuUser = $user ?? auth()->user();
    $canAccessAdmin = $menuUser?->hasRole('superadmin') ?? false;
@endphp

<div data-account-menu-items>
    <a href="{{ route('profile.edit') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">Cuenta de usuario</a>
    <a href="{{ route('coleccion') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">Mi colección</a>
    <a href="{{ route('dashboard') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">Presentación</a>
    @if ($canAccessAdmin)
        <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">Administración</a>
        <a href="{{ route('admin.galeria') }}" class="block rounded-lg px-3 py-2 text-gray-200 hover:bg-white/10">Galería de imágenes</a>
    @endif
    <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-white/10 pt-1">
        @csrf
        <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-red-400 hover:bg-red-500/10">Cerrar sesión</button>
    </form>
</div>
