<?php

namespace Tests\Feature\Admin;

use App\Models\Anime;
use App\Models\Report;
use App\Models\RouteOverride;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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
        $user->assignRole($role);

        return $user;
    }

    public function test_guest_is_redirected_from_the_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_view_the_admin_dashboard(): void
    {
        $response = $this->actingAs($this->makeUser('user'))->get(route('admin.dashboard'));

        $response->assertForbidden();
    }

    public function test_admin_role_cannot_access_administration_or_gallery(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->get(route('admin.galeria'))
            ->assertForbidden();
    }

    public function test_admin_navigation_is_hidden_from_non_superadmins(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.dashboard'))
            ->assertDontSee(route('admin.galeria'));
    }

    public function test_account_menus_share_the_same_options_and_only_show_authorized_admin_links(): void
    {
        $commonRoutes = ['home', 'dashboard', 'anime.index', 'profile.edit'];

        foreach (['user', 'admin', 'superadmin'] as $role) {
            $user = $this->makeUser($role);
            $routes = $role === 'superadmin' ? [...$commonRoutes, 'admin.dashboard'] : $commonRoutes;

            foreach ($routes as $routeName) {
                $response = $this->actingAs($user)->get(route($routeName));

                $response->assertOk();
                $this->assertSame(1, preg_match(
                    '/<div data-account-menu-items>(.*?)<\/div>/s',
                    $response->getContent(),
                    $matches,
                ));

                $menu = $matches[1];
                foreach (['profile.edit', 'coleccion', 'dashboard', 'logout'] as $commonRoute) {
                    $this->assertStringContainsString(
                        $commonRoute === 'logout'
                            ? 'action="'.route($commonRoute).'"'
                            : 'href="'.route($commonRoute).'"',
                        $menu,
                    );
                }

                if ($role === 'superadmin') {
                    $this->assertStringContainsString('href="'.route('admin.dashboard').'"', $menu);
                    $this->assertStringContainsString('href="'.route('admin.galeria').'"', $menu);
                } else {
                    $this->assertStringNotContainsString('href="'.route('admin.dashboard').'"', $menu);
                    $this->assertStringNotContainsString('href="'.route('admin.galeria').'"', $menu);
                }
            }
        }
    }

    public function test_superadmin_sees_admin_access_in_the_account_menu_but_not_a_panel_admin_sidebar_option(): void
    {
        $admin = $this->makeUser('superadmin');

        $this->actingAs($admin)
            ->get(route('anime.index'))
            ->assertOk()
            ->assertDontSee('Panel Admin')
            ->assertSee('data-account-menu-items', false)
            ->assertSee('href="'.route('admin.dashboard').'"', false);
    }

    public function test_superadmin_sees_dashboard_statistics_and_admin_navigation(): void
    {
        $admin = $this->makeUser('superadmin');
        $this->makeUser('admin');
        $anime = Anime::query()->create([
            'title' => 'Anime administrativo',
            'slug' => 'anime-administrativo',
        ]);
        Report::query()->create([
            'user_id' => $admin->id,
            'reportable_type' => Anime::class,
            'reportable_id' => $anime->id,
            'reason' => 'Contenido incorrecto',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertSee('data-sidebar-back', false)
            ->assertSee('href="'.route('dashboard').'" data-sidebar-back data-sidebar-back-history="false"', false)
            ->assertSee('Panel Administrativo')
            ->assertSee('Anime')
            ->assertSee('Usuarios &amp; Roles', false)
            ->assertViewHas('stats', fn (array $stats) => $stats['anime'] === 1
                && $stats['users'] === 2
                && $stats['admins'] === 1
                && $stats['pendingReports'] === 1);

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_superadmin_dashboard_uses_custom_sidebar_destination(): void
    {
        $admin = $this->makeUser('superadmin');
        RouteOverride::query()->create([
            'key' => 'admin.menu.biblioteca',
            'path' => '/admin/usuarios',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSee('href="/admin/usuarios"', false)
            ->assertSee('Biblioteca');
    }
}
