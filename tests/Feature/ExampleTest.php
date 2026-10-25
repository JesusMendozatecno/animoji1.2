<?php

namespace Tests\Feature;

use App\Models\RouteOverride;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_home_page_renders_with_the_public_navigation_and_categories(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Tu mundo del anime, en un solo lugar.')
            ->assertSee('7 CAPITALES')
            ->assertSee('VIDEOJUEGOS')
            ->assertSee('Registrarme');
    }

    public function test_home_page_uses_custom_navigation_destinations(): void
    {
        RouteOverride::create([
            'key' => 'index.categoria_anime',
            'path' => '/manga',
        ]);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('href="/manga"', false)
            ->assertSee('clip-tech group', false);
    }
}
