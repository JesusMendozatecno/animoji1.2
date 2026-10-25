<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Models\RouteOverride;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColeccionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_the_collection(): void
    {
        $this->get(route('coleccion'))->assertRedirect(route('login'));
    }

    public function test_verified_user_sees_collection_stats_and_categories(): void
    {
        $user = User::factory()->create();
        Anime::query()->create(['title' => 'Anime de colección', 'slug' => 'anime-coleccion']);
        Manga::query()->create(['title' => 'Manga de colección', 'slug' => 'manga-coleccion']);
        MusicTrack::query()->create(['title' => 'Música de colección', 'slug' => 'musica-coleccion', 'artist' => 'Artista']);
        Game::query()->create(['title' => 'Juego de colección', 'slug' => 'juego-coleccion']);

        $this->actingAs($user)
            ->get(route('coleccion'))
            ->assertOk()
            ->assertViewIs('coleccion')
            ->assertSee('MI')
            ->assertSee('COLECCIÓN')
            ->assertSee('Resumen de tu colección')
            ->assertSee('Anime de colección')
            ->assertSee('Manga de colección')
            ->assertSee('Música de colección')
            ->assertSee('Juego de colección')
            ->assertSee('Mi colección')
            ->assertSee('data-sidebar-back', false)
            ->assertSee('href="'.route('dashboard').'" data-sidebar-back data-sidebar-back-history="false"', false)
            ->assertViewHas('stats', [
                'anime' => 1,
                'manga' => 1,
                'music' => 1,
                'games' => 1,
            ]);
    }

    public function test_collection_orders_recent_items_across_catalogs_by_creation_time(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create([
            'title' => 'Anime antiguo',
            'slug' => 'anime-antiguo',
        ]);
        $anime->forceFill(['created_at' => '2026-01-01 10:00:00'])->save();
        $game = Game::query()->create([
            'title' => 'Juego reciente',
            'slug' => 'juego-reciente',
        ]);
        $game->forceFill(['created_at' => '2026-02-01 10:00:00'])->save();
        $manga = Manga::query()->create([
            'title' => 'Manga más reciente',
            'slug' => 'manga-reciente',
        ]);
        $manga->forceFill(['created_at' => '2026-03-01 10:00:00'])->save();

        $this->actingAs($user)
            ->get(route('coleccion'))
            ->assertOk()
            ->assertViewHas('recent', fn (array $recent) => $recent[0]['title'] === 'Manga más reciente'
                && $recent[1]['title'] === 'Juego reciente'
                && $recent[2]['title'] === 'Anime antiguo');
    }

    public function test_collection_uses_custom_sidebar_destination(): void
    {
        $user = User::factory()->create();
        RouteOverride::query()->create([
            'key' => 'usuario.menu.coleccion',
            'path' => '/dashboard',
        ]);

        $this->actingAs($user)
            ->get(route('coleccion'))
            ->assertOk()
            ->assertSee('href="/dashboard"', false)
            ->assertSee('aria-current="page"', false);
    }

    public function test_collection_escapes_catalog_titles_in_recent_items(): void
    {
        $user = User::factory()->create();
        Anime::query()->create([
            'title' => '<script>alert("xss")</script>',
            'slug' => 'titulo-seguro',
        ]);

        $this->actingAs($user)
            ->get(route('coleccion'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_collection_shows_empty_state_when_catalogs_have_no_items(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('coleccion'))
            ->assertOk()
            ->assertSee('Aún no hay títulos en el catálogo.')
            ->assertSee('Sin elementos recientes.');
    }
}
