<?php

namespace Tests\Feature\Admin;

use App\Models\RouteOverride;
use App\Models\SystemImage;
use App\Models\User;
use App\Support\Navigation;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomizationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/personalizacion')->assertRedirect('/login');
    }

    public function test_regular_user_cannot_manage_customizations(): void
    {
        $this->actingAs($this->makeUser('user'))
            ->get('/admin/personalizacion')
            ->assertForbidden();
    }

    public function test_superadmin_can_view_image_and_navigation_customizations(): void
    {
        $admin = $this->makeUser('superadmin');
        RouteOverride::create(['key' => 'header.logo', 'path' => '/dashboard']);

        $this->actingAs($admin)
            ->get('/admin/personalizacion')
            ->assertOk()
            ->assertViewIs('admin.personalizacion')
            ->assertSee('Zonas de imagen del sistema')
            ->assertSee('Imagen por defecto');

        $this->get('/admin/personalizacion?tab=direcciones')
            ->assertOk()
            ->assertViewIs('admin.personalizacion')
            ->assertViewHas('customizing', false)
            ->assertSee('Direcciones')
            ->assertSee('Panel de usuario (Mi Biblioteca)')
            ->assertSee('/dashboard')
            ->assertViewHas('navOverrides', fn ($overrides) => $overrides['header.logo'] === '/dashboard');
    }

    public function test_navigation_destinations_include_an_option_to_disable_links(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->get('/admin/personalizacion?tab=direcciones')
            ->assertOk()
            ->assertSee('Ninguna dirección — deja el elemento sin enlace');
    }

    public function test_superadmin_can_toggle_image_customization_mode(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->patch('/admin/personalizacion/toggle')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'customizing' => true,
        ]);
    }

    public function test_superadmin_can_change_and_restore_a_navigation_destination(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->put('/admin/personalizacion/direcciones/header.logo', ['path' => '/dashboard'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('route_overrides', [
            'key' => 'header.logo',
            'path' => '/dashboard',
        ]);

        $this->put('/admin/personalizacion/direcciones/header.logo', ['path' => null])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('route_overrides', ['key' => 'header.logo']);
    }

    public function test_superadmin_can_remove_a_navigation_link_and_restore_its_default(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->put('/admin/personalizacion/direcciones/index.categoria_anime', [
                'path' => Navigation::NO_DESTINATION,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'El botón se guardó sin dirección.');

        $this->assertDatabaseHas('route_overrides', [
            'key' => 'index.categoria_anime',
            'path' => Navigation::NO_DESTINATION,
        ]);

        $disabledHome = $this->get('/')->assertOk()->getContent();
        $this->assertSame(
            1,
            preg_match('/<a\b(?=[^>]*class="clip-tech group)([^>]*)>.*?<h2[^>]*>ANIME<\/h2>/s', $disabledHome, $matches),
        );
        $this->assertSame(0, preg_match('/\bhref\s*=/i', $matches[1]));

        $this->put('/admin/personalizacion/direcciones/index.categoria_anime', ['path' => ''])
            ->assertRedirect()
            ->assertSessionHas('success', 'Se restauró el destino original del botón.');

        $this->assertDatabaseMissing('route_overrides', ['key' => 'index.categoria_anime']);

        $restoredHome = $this->get('/')->assertOk()->getContent();
        $this->assertSame(
            1,
            preg_match('/<a\b(?=[^>]*class="clip-tech group)([^>]*)>.*?<h2[^>]*>ANIME<\/h2>/s', $restoredHome, $matches),
        );
        $this->assertSame(1, preg_match('/\bhref="[^"]*\/anime"/i', $matches[1]));
    }

    public function test_user_and_admin_menus_render_disabled_links_without_href(): void
    {
        RouteOverride::create([
            'key' => 'admin.menu.inicio',
            'path' => Navigation::NO_DESTINATION,
        ]);
        $adminPage = $this->actingAs($this->makeUser('superadmin'))
            ->get('/admin')
            ->assertOk()
            ->getContent();

        $this->assertSame(
            1,
            preg_match('/<a\b(?=[^>]*aria-disabled="true")(?=[^>]*class="flex items-center gap-3 rounded-xl)([^>]*)>.*?Inicio\s*<\/a>/s', $adminPage, $matches),
        );
        $this->assertSame(0, preg_match('/\bhref\s*=/i', $matches[1]));

        RouteOverride::create([
            'key' => 'usuario.menu.anime',
            'path' => Navigation::NO_DESTINATION,
        ]);
        $userPage = $this->actingAs($this->makeUser('user'))
            ->get('/dashboard')
            ->assertOk()
            ->getContent();

        $this->assertSame(
            1,
            preg_match('/<a\b(?=[^>]*aria-disabled="true")(?=[^>]*class="flex items-center gap-3 rounded-xl)([^>]*)>.*?Anime\s*<span/s', $userPage, $matches),
        );
        $this->assertSame(0, preg_match('/\bhref\s*=/i', $matches[1]));
    }

    public function test_superadmin_cannot_set_an_unavailable_navigation_destination(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->put('/admin/personalizacion/direcciones/header.logo', ['path' => 'https://example.com'])
            ->assertSessionHasErrors('path');

        $this->assertDatabaseMissing('route_overrides', ['key' => 'header.logo']);
    }

    public function test_superadmin_can_set_and_restore_a_zone_image(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->put('/admin/personalizacion/zona/admin.sidebar', [
                'image_path' => 'images/index/hero-bg1.jpg',
                'pos_x' => 35,
                'pos_y' => 60,
                'scale' => 1.5,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('system_images', [
            'zone' => 'admin.sidebar',
            'image_path' => 'images/index/hero-bg1.jpg',
            'pos_x' => 35,
            'pos_y' => 60,
            'scale' => 1.5,
        ]);

        $this->put('/admin/personalizacion/zona/admin.sidebar', ['image_path' => null])
            ->assertRedirect()
            ->assertSessionHas('info');

        $this->assertDatabaseMissing('system_images', ['zone' => 'admin.sidebar']);
    }

    public function test_superadmin_cannot_set_an_image_path_outside_the_image_directory(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->put('/admin/personalizacion/zona/admin.sidebar', [
                'image_path' => 'images/../secret.jpg',
                'pos_x' => 50,
                'pos_y' => 50,
                'scale' => 1,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('system_images', ['zone' => 'admin.sidebar']);
    }

    public function test_unknown_image_zone_returns_not_found(): void
    {
        $this->actingAs($this->makeUser('superadmin'))
            ->put('/admin/personalizacion/zona/no-existe', ['image_path' => null])
            ->assertNotFound();

        $this->assertSame(0, SystemImage::query()->count());
    }
}
