<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\RouteOverride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_the_user_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_verified_user_sees_the_blade_dashboard_and_recent_anime(): void
    {
        $user = User::factory()->create();
        Anime::query()->create([
            'title' => 'Anime de prueba',
            'slug' => 'anime-de-prueba',
            'year' => 2025,
            'status' => 'ongoing',
            'rating' => 8.5,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertViewIs('dashboard')
            ->assertSee('Presentación')
            ->assertSee('Anime de prueba')
            ->assertSee('8.5')
            ->assertSee('href="'.route('home').'" data-sidebar-back data-sidebar-back-history="false"', false)
            ->assertViewHas('stats', fn (array $stats) => $stats['anime'] === 1);
    }

    public function test_dashboard_uses_custom_sidebar_destinations(): void
    {
        $user = User::factory()->create();
        RouteOverride::query()->create([
            'key' => 'usuario.menu.anime',
            'path' => '/manga',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertSee('href="/manga"', false)
            ->assertSee('Anime');
    }
}
