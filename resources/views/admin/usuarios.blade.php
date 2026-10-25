@extends('layouts.admin')

@section('title', 'Usuarios y administradores · ANIMOJI')

@section('content')
@php
    $roleStyles = [
        'superadmin' => 'bg-purple-500/15 text-purple-300 ring-purple-400/40',
        'admin' => 'bg-red-500/15 text-red-300 ring-red-400/40',
        'editor' => 'bg-sky-500/15 text-sky-300 ring-sky-400/40',
        'moderator' => 'bg-amber-500/15 text-amber-300 ring-amber-400/40',
        'support' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/40',
        'creator' => 'bg-pink-500/15 text-pink-300 ring-pink-400/40',
        'user' => 'bg-white/10 text-gray-300 ring-white/20',
    ];
    $userRows = collect($items->items());
    $modal = old('_modal');
    $modalUserId = old('_user_id');
@endphp

<header>
    <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-blue-400/80">Administración</p>
    <h1 class="mt-1 font-display text-3xl italic font-bold text-white sm:text-4xl">Usuarios y administradores</h1>
    <p class="mt-1 text-sm text-gray-400">Consulta, edita cuentas y gestiona roles. Todas las acciones se verifican en el servidor.</p>
</header>

@if (session('success'))
    <div role="status" class="mt-4 rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div role="alert" class="mt-4 rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ session('error') }}</div>
@endif

<div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-xl border border-white/10 bg-[#0a1220] p-4">
        <p class="text-[11px] uppercase tracking-wider text-gray-500">Total usuarios</p>
        <p class="mt-1 text-2xl font-bold text-white">{{ $stats['total'] }}</p>
    </div>
    <div class="rounded-xl border border-white/10 bg-[#0a1220] p-4">
        <p class="text-[11px] uppercase tracking-wider text-gray-500">Administradores</p>
        <p class="mt-1 text-2xl font-bold text-red-400">{{ $stats['admins'] }}</p>
    </div>
    <div class="rounded-xl border border-white/10 bg-[#0a1220] p-4">
        <p class="text-[11px] uppercase tracking-wider text-gray-500">Superadmins</p>
        <p class="mt-1 text-2xl font-bold text-purple-400">{{ $stats['superadmins'] }}</p>
    </div>
    <div class="rounded-xl border border-white/10 bg-[#0a1220] p-4">
        <p class="text-[11px] uppercase tracking-wider text-gray-500">Correos verificados</p>
        <p class="mt-1 text-2xl font-bold text-emerald-400">{{ $stats['verified'] }}</p>
    </div>
</div>

<section class="mt-6 rounded-2xl border border-white/10 bg-[#0a1220] p-5">
    <form method="GET" action="{{ route('admin.usuarios') }}" class="flex flex-wrap items-center gap-3">
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Buscar por nombre o correo..." aria-label="Buscar usuarios por nombre o correo" class="w-72 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-gray-200 placeholder-gray-500 outline-none transition focus:border-blue-400/50">
        <button type="submit" class="rounded-xl border border-white/15 px-4 py-2 text-sm text-gray-300 transition hover:border-white/40 hover:text-white">Buscar</button>
        <span class="ml-auto text-xs text-gray-500">{{ $items->total() }} usuario(s)</span>
    </form>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead>
                <tr class="border-b border-white/10 text-[11px] uppercase tracking-wider text-gray-500">
                    <th scope="col" class="px-3 py-2.5 font-semibold">Usuario</th>
                    <th scope="col" class="px-3 py-2.5 font-semibold">Roles</th>
                    <th scope="col" class="px-3 py-2.5 font-semibold">Verificación</th>
                    <th scope="col" class="px-3 py-2.5 font-semibold">Registro</th>
                    <th scope="col" class="px-3 py-2.5 text-right font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($userRows as $row)
                    <tr class="border-b border-white/5 transition hover:bg-white/[0.03]">
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ $row['avatar'] ?? '/images/cuenta/avatar.png' }}" alt="{{ $row['name'] }}" class="h-9 w-9 shrink-0 rounded-full border border-white/20 object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-white">
                                        {{ $row['name'] }}
                                        @if ($row['id'] === $user->id)
                                            <span class="ml-1 text-[10px] font-bold text-blue-400">(tú)</span>
                                        @endif
                                    </p>
                                    <p class="truncate text-xs text-gray-500">{{ $row['email'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex flex-wrap gap-1.5">
                                @forelse ($row['roles'] as $role)
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase ring-1 {{ $roleStyles[$role] ?? $roleStyles['user'] }}">{{ $role }}</span>
                                @empty
                                    <span class="text-xs text-gray-600">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            @if ($row['email_verified_at'])
                                <span class="rounded bg-emerald-500/15 px-2 py-0.5 text-[11px] font-semibold text-emerald-300 ring-1 ring-emerald-400/40">Verificado</span>
                            @else
                                <span class="rounded bg-amber-500/15 px-2 py-0.5 text-[11px] font-semibold text-amber-300 ring-1 ring-amber-400/40">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-400">
                            {{ $row['created_at'] ? \Illuminate\Support\Carbon::parse($row['created_at'])->locale('es')->translatedFormat('d M Y') : '—' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-right">
                            <button type="button" data-open-modal="edit" data-user="{{ base64_encode(json_encode($row, JSON_THROW_ON_ERROR)) }}" class="rounded-lg border border-white/15 px-2.5 py-1.5 text-xs text-gray-300 transition hover:border-blue-400/60 hover:text-white">✏ Editar</button>
                            <button type="button" data-open-modal="roles" data-user="{{ base64_encode(json_encode($row, JSON_THROW_ON_ERROR)) }}" class="ml-1.5 rounded-lg border border-white/15 px-2.5 py-1.5 text-xs text-gray-300 transition hover:border-purple-400/60 hover:text-white">🛡 Roles</button>
                            <button type="button" data-open-modal="delete" data-user="{{ base64_encode(json_encode($row, JSON_THROW_ON_ERROR)) }}" @disabled($row['id'] === $user->id) @if ($row['id'] === $user->id) title="No puedes eliminar tu propia cuenta" @endif class="ml-1.5 rounded-lg border border-red-500/30 px-2.5 py-1.5 text-xs text-red-400 transition hover:border-red-400 hover:bg-red-500/10 hover:text-red-300 disabled:cursor-not-allowed disabled:opacity-40">🗑</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-sm text-gray-500">
                            @if ($filters['q'])
                                No se encontraron usuarios con «{{ $filters['q'] }}».
                            @else
                                No hay usuarios registrados.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <nav class="mt-5 flex flex-wrap items-center justify-center gap-2 text-sm" aria-label="Paginación de usuarios">
            @if ($items->onFirstPage())
                <span class="rounded-lg border border-white/10 px-3 py-2 text-gray-600" aria-disabled="true">Anterior</span>
            @else
                <a href="{{ $items->previousPageUrl() }}" class="rounded-lg border border-white/15 px-3 py-2 text-gray-300 transition hover:border-blue-400/60 hover:text-white">Anterior</a>
            @endif
            @foreach ($items->getUrlRange(max(1, $items->currentPage() - 2), min($items->lastPage(), $items->currentPage() + 2)) as $page => $url)
                @if ($page === $items->currentPage())
                    <span class="rounded-lg border border-blue-400/60 bg-blue-500/20 px-3 py-2 font-semibold text-white" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="rounded-lg border border-white/15 px-3 py-2 text-gray-300 transition hover:border-blue-400/60 hover:text-white">{{ $page }}</a>
                @endif
            @endforeach
            @if ($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}" class="rounded-lg border border-white/15 px-3 py-2 text-gray-300 transition hover:border-blue-400/60 hover:text-white">Siguiente</a>
            @else
                <span class="rounded-lg border border-white/10 px-3 py-2 text-gray-600" aria-disabled="true">Siguiente</span>
            @endif
        </nav>
    @endif
</section>

<dialog id="edit-user-dialog" class="w-[min(460px,94vw)] rounded-2xl border border-white/15 bg-[#0b1220] p-0 text-gray-100 shadow-[0_0_60px_rgba(0,0,0,0.7)] backdrop:bg-black/75">
    <form id="edit-user-form" method="POST" class="rounded-2xl p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="_modal" value="edit">
        <input type="hidden" name="_user_id" value="{{ old('_user_id') }}">
        <h2 class="font-display text-xl italic font-bold text-white">Editar usuario</h2>
        <p id="edit-user-email" class="mt-1 text-xs text-gray-500"></p>
        <div class="mt-5 flex flex-col gap-4">
            <div>
                <label for="user-name" class="mb-1.5 block text-sm font-semibold text-gray-300">Nombre <span class="text-red-400">*</span></label>
                <input id="user-name" name="name" value="{{ old('name') }}" type="text" maxlength="255" required aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition focus:border-blue-400/50">
                @error('name')<p class="mt-1 text-xs font-semibold text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="user-email" class="mb-1.5 block text-sm font-semibold text-gray-300">Correo electrónico <span class="text-red-400">*</span></label>
                <input id="user-email" name="email" value="{{ old('email') }}" type="email" maxlength="255" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition focus:border-blue-400/50">
                @error('email')<p class="mt-1 text-xs font-semibold text-red-400">{{ $message }}</p>@enderror
                <p class="mt-1 text-[11px] text-gray-500">Si cambias el correo, se marcará como no verificado.</p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" data-close-dialog class="rounded-xl border border-white/15 px-4 py-2.5 text-sm text-gray-300 transition hover:border-white/40">Cancelar</button>
            <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500">Guardar cambios</button>
        </div>
    </form>
</dialog>

<dialog id="roles-user-dialog" class="w-[min(480px,94vw)] rounded-2xl border border-white/15 bg-[#0b1220] p-0 text-gray-100 shadow-[0_0_60px_rgba(0,0,0,0.7)] backdrop:bg-black/75">
    <form id="roles-user-form" method="POST" class="rounded-2xl p-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="_modal" value="roles">
        <input type="hidden" name="_user_id" value="{{ old('_user_id') }}">
        <h2 class="font-display text-xl italic font-bold text-white">Gestionar roles</h2>
        <p id="roles-user-identity" class="mt-1 text-xs text-gray-500"></p>
        <div class="mt-5 grid grid-cols-2 gap-2">
            @foreach ($roles as $role)
                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-white/10 px-3 py-2.5 text-sm text-gray-400 transition hover:border-white/25 hover:text-white has-[:checked]:border-blue-400/60 has-[:checked]:bg-blue-500/15 has-[:checked]:text-white">
                    <input type="checkbox" name="roles[]" value="{{ $role }}" @checked(in_array($role, old('roles', []), true)) class="h-4 w-4 rounded accent-blue-500">
                    <span class="capitalize">{{ $role }}</span>
                </label>
            @endforeach
        </div>
        @error('roles')<p class="mt-2 text-xs font-semibold text-red-400">{{ $message }}</p>@enderror
        @error('roles.*')<p class="mt-2 text-xs font-semibold text-red-400">{{ $message }}</p>@enderror
        <p class="mt-4 text-[11px] leading-relaxed text-gray-500">Solo un superadministrador puede modificar roles de administración, y nadie puede modificar los propios.</p>
        @if (session('error'))
            <p class="mt-3 text-xs font-semibold text-red-400">{{ session('error') }}</p>
        @endif
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" data-close-dialog class="rounded-xl border border-white/15 px-4 py-2.5 text-sm text-gray-300 transition hover:border-white/40">Cancelar</button>
            <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-500">Guardar roles</button>
        </div>
    </form>
</dialog>

<dialog id="delete-user-dialog" class="w-[min(460px,94vw)] rounded-2xl border border-white/15 bg-[#0b1220] p-0 text-gray-100 shadow-[0_0_60px_rgba(0,0,0,0.7)] backdrop:bg-black/75">
    <form id="delete-user-form" method="POST" class="rounded-2xl p-6">
        @csrf
        @method('DELETE')
        <input type="hidden" name="_modal" value="delete">
        <input type="hidden" name="_user_id" value="{{ old('_user_id') }}">
        <div class="flex items-start gap-3">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-red-500/15 text-red-300 ring-1 ring-red-400/30" aria-hidden="true">!</span>
            <div>
                <h2 class="font-display text-xl italic font-bold text-white">Eliminar usuario</h2>
                <p class="mt-1 text-sm text-gray-400">Esta acción es permanente. Escribe el nombre del usuario para confirmar.</p>
            </div>
        </div>
        <p id="delete-user-name" class="mt-4 rounded-lg bg-white/5 px-3 py-2 font-semibold text-white"></p>
        <label for="confirm-user-name" class="mt-4 mb-1.5 block text-sm font-semibold text-gray-300">Nombre de confirmación</label>
        <input id="confirm-user-name" name="confirm_name" value="{{ old('confirm_name') }}" type="text" required aria-invalid="{{ $errors->has('confirm_name') ? 'true' : 'false' }}" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none transition focus:border-red-400/50">
        @error('confirm_name')<p class="mt-1 text-xs font-semibold text-red-400">{{ $message }}</p>@enderror
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" data-close-dialog class="rounded-xl border border-white/15 px-4 py-2.5 text-sm text-gray-300 transition hover:border-white/40">Cancelar</button>
            <button type="submit" class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">Eliminar cuenta</button>
        </div>
    </form>
</dialog>

<script>
    (() => {
        const dialogs = {
            edit: document.getElementById('edit-user-dialog'),
            roles: document.getElementById('roles-user-dialog'),
            delete: document.getElementById('delete-user-dialog'),
        };
        const forms = {
            edit: document.getElementById('edit-user-form'),
            roles: document.getElementById('roles-user-form'),
            delete: document.getElementById('delete-user-form'),
        };
        const updateUrl = @js(route('admin.usuarios.update', ['user' => 'USER_ID']));
        const rolesUrl = @js(route('admin.usuarios.roles', ['user' => 'USER_ID']));
        const deleteUrl = @js(route('admin.usuarios.destroy', ['user' => 'USER_ID']));
        const decodeUser = (encoded) => JSON.parse(
            new TextDecoder().decode(Uint8Array.from(atob(encoded), (character) => character.charCodeAt(0))),
        );

        const openDialog = (kind, button) => {
            const row = decodeUser(button.dataset.user);
            const dialog = dialogs[kind];
            const form = forms[kind];
            form.action = (kind === 'roles' ? rolesUrl : kind === 'delete' ? deleteUrl : updateUrl)
                .replace('USER_ID', encodeURIComponent(row.id));
            form.elements.namedItem('_user_id').value = row.id;

            if (kind === 'edit') {
                document.getElementById('edit-user-email').textContent = row.email;
                document.getElementById('user-name').value = row.name;
                document.getElementById('user-email').value = row.email;
            } else if (kind === 'roles') {
                document.getElementById('roles-user-identity').textContent = `${row.name} · ${row.email}`;
                form.querySelectorAll('input[name="roles[]"]').forEach((checkbox) => {
                    checkbox.checked = row.roles.includes(checkbox.value);
                });
            } else {
                document.getElementById('delete-user-name').textContent = row.name;
                document.getElementById('confirm-user-name').value = '';
                document.getElementById('confirm-user-name').dataset.expectedName = row.name;
            }

            dialog.showModal();
        };

        document.querySelectorAll('[data-open-modal]').forEach((button) => {
            button.addEventListener('click', () => openDialog(button.dataset.openModal, button));
        });
        document.querySelectorAll('[data-close-dialog]').forEach((button) => {
            button.addEventListener('click', () => button.closest('dialog').close());
        });
        Object.values(dialogs).forEach((dialog) => {
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        });
        document.getElementById('delete-user-form').addEventListener('submit', (event) => {
            const input = document.getElementById('confirm-user-name');
            if (input.value !== input.dataset.expectedName) {
                event.preventDefault();
                input.setCustomValidity('El nombre de confirmación debe coincidir exactamente.');
                input.reportValidity();
            }
        });
        document.getElementById('confirm-user-name').addEventListener('input', (event) => event.target.setCustomValidity(''));

        @if ($modal && $modalUserId)
            const oldModalRow = @js($userRows->firstWhere('id', (int) $modalUserId));
            if (oldModalRow && dialogs[@js($modal)]) {
                const fakeButton = { dataset: { user: btoa(unescape(encodeURIComponent(JSON.stringify(oldModalRow)))) } };
                openDialog(@js($modal), fakeButton);
                @if ($modal === 'edit')
                    document.getElementById('user-name').value = @js(old('name'));
                    document.getElementById('user-email').value = @js(old('email'));
                @elseif ($modal === 'roles')
                    const selectedRoles = @js(old('roles', []));
                    forms.roles.querySelectorAll('input[name="roles[]"]').forEach((checkbox) => {
                        checkbox.checked = selectedRoles.includes(checkbox.value);
                    });
                @elseif ($modal === 'delete')
                    document.getElementById('confirm-user-name').value = @js(old('confirm_name'));
                @endif
            }
        @endif
    })();
</script>
@endsection
