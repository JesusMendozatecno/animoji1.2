<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 👥 Pruebas de la gestión de usuarios y administradores.
 *
 * Se centran en las reglas de seguridad del controlador: quién puede ver,
 * quién puede otorgar roles privilegiados y la protección del último
 * superadministrador del sistema.
 */
class UsuariosManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** Crea un usuario verificado con el rol indicado. */
    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole($role);

        return $user;
    }

    // ─────────────────────────────────────────────
    // Autorización
    // ─────────────────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/usuarios')->assertRedirect('/login');
    }

    public function test_plain_user_cannot_see_the_user_list(): void
    {
        $this->actingAs($this->makeUser('user'))
            ->get('/admin/usuarios')
            ->assertForbidden();
    }

    public function test_superadmin_can_see_the_user_list(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/usuarios')
            ->assertOk()
            ->assertViewIs('admin.usuarios')
            ->assertViewHas('items', fn ($items) => $items->count() > 0);
    }

    public function test_password_is_never_exposed_in_the_list(): void
    {
        $this->makeUser('user');

        $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin/usuarios')
            ->assertOk()
            ->assertViewHas('items', function ($items): bool {
                $row = $items->first();

                return ! array_key_exists('password', $row)
                    && ! array_key_exists('remember_token', $row);
            });
    }

    public function test_user_name_and_email_are_escaped_in_the_html_list(): void
    {
        $this->makeUser('user')->update([
            'name' => '<script>alert("xss")</script>',
            'email' => 'safe@example.test',
        ]);

        $response = $this->actingAs($this->makeUser('superadmin'))->get('/admin/usuarios');

        $response
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    // ─────────────────────────────────────────────
    // Edición de datos
    // ─────────────────────────────────────────────

    public function test_superadmin_can_update_a_user_name(): void
    {
        $target = $this->makeUser('user');

        $this->actingAs($this->makeUser('superadmin'))
            ->put("/admin/usuarios/{$target->id}", [
                'name' => 'Nombre Nuevo',
                'email' => $target->email,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Nombre Nuevo']);
    }

    public function test_changing_the_email_clears_the_verification(): void
    {
        $target = $this->makeUser('user');

        $this->actingAs($this->makeUser('superadmin'))
            ->put("/admin/usuarios/{$target->id}", [
                'name' => $target->name,
                'email' => 'nuevo-correo@animoji.test',
            ])
            ->assertSessionHas('success');

        $this->assertNull($target->fresh()->email_verified_at);
    }

    public function test_duplicate_email_is_rejected_in_spanish(): void
    {
        $this->makeUser('user');
        $target = $this->makeUser('user');

        $this->actingAs($this->makeUser('superadmin'))
            ->put("/admin/usuarios/{$target->id}", [
                'name' => $target->name,
                'email' => User::query()->where('id', '!=', $target->id)->first()->email,
            ])
            ->assertSessionHasErrors('email');
    }

    // ─────────────────────────────────────────────
    // Roles: reglas de seguridad
    // ─────────────────────────────────────────────

    public function test_nobody_can_change_their_own_roles(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->put("/admin/usuarios/{$admin->id}/roles", ['roles' => ['user']])
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->hasRole('superadmin'));
    }

    public function test_non_superadmin_cannot_grant_admin(): void
    {
        $editor = $this->makeUser('editor');
        $target = $this->makeUser('user');

        // editor no tiene roles.manage → 403
        $this->actingAs($editor)
            ->put("/admin/usuarios/{$target->id}/roles", ['roles' => ['admin']])
            ->assertForbidden();
    }

    public function test_admin_cannot_grant_superadmin(): void
    {
        $admin = $this->makeUser('admin');
        $target = $this->makeUser('user');

        $this->actingAs($admin)
            ->put("/admin/usuarios/{$target->id}/roles", ['roles' => ['superadmin']])
            ->assertForbidden();

        $this->assertFalse($target->fresh()->hasRole('superadmin'));
    }

    public function test_superadmin_can_grant_admin(): void
    {
        $boss = $this->makeUser('superadmin');
        $target = $this->makeUser('user');

        $this->actingAs($boss)
            ->put("/admin/usuarios/{$target->id}/roles", ['roles' => ['admin']])
            ->assertSessionHas('success');

        $this->assertTrue($target->fresh()->hasRole('admin'));
    }

    public function test_last_superadmin_cannot_be_demoted(): void
    {
        $boss = $this->makeUser('superadmin'); // único superadmin

        $this->actingAs($boss)
            ->put("/admin/usuarios/{$boss->id}/roles", ['roles' => ['admin']])
            ->assertSessionHas('error');

        // La regla de "no autoeditarse" también protege aquí:
        $this->assertTrue($boss->fresh()->hasRole('superadmin'));
    }

    public function test_superadmin_role_requires_at_least_one_value(): void
    {
        $boss = $this->makeUser('superadmin');
        $target = $this->makeUser('user');

        $this->actingAs($boss)
            ->put("/admin/usuarios/{$target->id}/roles", ['roles' => []])
            ->assertSessionHasErrors('roles');
    }

    // ─────────────────────────────────────────────
    // Borrado
    // ─────────────────────────────────────────────

    public function test_superadmin_can_delete_another_user_with_confirmation(): void
    {
        $target = $this->makeUser('user');

        $this->actingAs($this->makeUser('superadmin'))
            ->delete("/admin/usuarios/{$target->id}", ['confirm_name' => $target->name])
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_deletion_requires_the_exact_name_confirmation(): void
    {
        $target = $this->makeUser('user');

        $this->actingAs($this->makeUser('superadmin'))
            ->delete("/admin/usuarios/{$target->id}", ['confirm_name' => 'otro nombre'])
            ->assertSessionHasErrors('confirm_name');

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }

    public function test_nobody_can_delete_themselves_from_this_screen(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->delete("/admin/usuarios/{$admin->id}", ['confirm_name' => $admin->name])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_the_last_superadmin_cannot_be_deleted(): void
    {
        $boss = $this->makeUser('superadmin');
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->delete("/admin/usuarios/{$boss->id}", ['confirm_name' => $boss->name])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $boss->id]);
    }

    public function test_plain_user_cannot_delete_anyone(): void
    {
        $target = $this->makeUser('user');

        $this->actingAs($this->makeUser('user'))
            ->delete("/admin/usuarios/{$target->id}", ['confirm_name' => $target->name])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $target->id]);
    }
}
