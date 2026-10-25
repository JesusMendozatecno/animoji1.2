<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Support\Navigation;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * 👥 GESTIÓN DE USUARIOS Y ADMINISTRADORES
 *
 * Reglas de seguridad aplicadas (verificadas en el SERVIDOR):
 *  - Solo usuarios con permiso users.view / users.manage (admin, superadmin).
 *  - Ningún usuario puede modificarse sus propios roles.
 *  - Solo un superadmin puede otorgar o retirar el rol superadmin.
 *  - Solo un superadmin puede otorgar el rol admin.
 *  - No se puede eliminar el último superadministrador.
 *  - Nunca se exponen contraseñas ni tokens.
 */
class UsuariosController extends Controller
{
    public const PER_PAGE = 10;

    /** Roles gestionables desde esta pantalla. */
    public const ASSIGNABLE_ROLES = ['superadmin', 'admin', 'editor', 'moderator', 'support', 'creator', 'user'];

    /**
     * Listado paginado con búsqueda por nombre o correo.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('users.view') ?? false, 403, 'No tienes permisos para realizar esta acción.');

        $query = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $builder = User::query()->with('roles:id,name');

        if ($query !== '') {
            $builder->where(function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%')
                    ->orWhere('email', 'like', '%'.$query.'%');
            });
        }

        $paginator = $builder
            ->orderBy('name')
            ->paginate(self::PER_PAGE, ['*'], 'page', $page)
            ->withQueryString();

        // Nunca exponer password/remember_token: se serializan campos concretos
        $items = collect($paginator->items())->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'email_verified_at' => $u->email_verified_at?->toIso8601String(),
            'created_at' => $u->created_at?->toIso8601String(),
            'avatar' => $u->avatar ? '/'.$u->avatar : null,
            'roles' => $u->roles->pluck('name')->values()->all(),
        ]);

        $paginator->setCollection($items);

        return view('admin.usuarios', [
            'user' => $request->user(),
            'items' => $paginator,
            'filters' => ['q' => $query],
            'roles' => self::ASSIGNABLE_ROLES,
            'navigationOverrides' => Navigation::map(),
            'stats' => [
                'total' => User::query()->count(),
                'admins' => User::role('admin')->count(),
                'superadmins' => User::role('superadmin')->count(),
                'verified' => User::query()->whereNotNull('email_verified_at')->count(),
            ],
        ]);
    }

    /**
     * Actualiza los datos permitidos de un usuario (nombre y correo).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()?->can('users.manage') ?? false, 403, 'No tienes permisos para realizar esta acción.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Introduce un correo electrónico válido.',
            'email.unique' => 'Este registro ya existe: ese correo ya está en uso.',
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', 'La información se actualizó correctamente.');
    }

    /**
     * Asigna (o retira) roles de un usuario con todas las restricciones.
     */
    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        abort_unless($actor?->can('roles.manage') ?? false, 403, 'No tienes permisos para realizar esta acción.');

        // 1) Nadie se modifica sus propios roles
        if ($actor->id === $user->id) {
            return back()->with('error', 'No puedes modificar tus propios roles.');
        }

        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::in(self::ASSIGNABLE_ROLES)],
        ], [
            'roles.required' => 'Selecciona al menos un rol.',
            'roles.*.in' => 'El rol seleccionado no es válido.',
        ]);

        $newRoles = array_values(array_unique($validated['roles']));
        $currentRoles = $user->roles->pluck('name')->all();
        $actorIsSuperadmin = $actor->hasRole('superadmin');

        // 2) Solo un superadmin puede otorgar/retirar superadmin o admin
        $privileged = array_intersect($newRoles, ['superadmin', 'admin']);
        $hadPrivileged = array_intersect($currentRoles, ['superadmin', 'admin']);

        if ((count($privileged) > 0 || count($hadPrivileged) > 0) && ! $actorIsSuperadmin) {
            return back()->with('error', 'Solo un superadministrador puede modificar roles de administración.');
        }

        // 3) El rol superadmin exige superadmin explícito (ya cubierto arriba)
        // 4) Proteger el último superadmin: no se le puede retirar su rol
        if (in_array('superadmin', $currentRoles, true) && ! in_array('superadmin', $newRoles, true)) {
            $total = User::role('superadmin')->count();
            if ($total <= 1) {
                return back()->with('error', 'No se puede retirar el rol: es el último superadministrador del sistema.');
            }
        }

        DB::transaction(function () use ($user, $newRoles) {
            $user->syncRoles($newRoles);
        });

        return back()->with('success', 'Los roles se actualizaron correctamente.');
    }

    /**
     * Elimina una cuenta comprobando dependencias y el último superadmin.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();

        abort_unless($actor?->can('users.manage') ?? false, 403, 'No tienes permisos para realizar esta acción.');

        // Nunca autoeliminarse desde esta pantalla (usar /profile)
        if ($actor->id === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta desde aquí. Usa Cuenta de usuario.');
        }

        // Proteger el último superadmin
        if ($user->hasRole('superadmin') && User::role('superadmin')->count() <= 1) {
            return back()->with('error', 'No se puede eliminar: es el último superadministrador del sistema.');
        }

        // Confirmación explícita del elemento
        $request->validate([
            'confirm_name' => ['required', 'string', Rule::in([$user->name])],
        ], [
            'confirm_name.required' => 'Confirma el nombre del usuario para eliminarlo.',
            'confirm_name.in' => 'El nombre de confirmación no coincide.',
        ]);

        DB::transaction(function () use ($user) {
            // Comentario previo para la auditoría del borrado
            $user->delete();
        });

        return back()->with('success', 'El elemento se eliminó correctamente.');
    }
}
