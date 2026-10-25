<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_anime_catalog_renders_blade_cards_and_statistics(): void
    {
        Anime::query()->create([
            'title' => 'Anime de portada',
            'slug' => 'anime-portada',
            'studio' => 'Estudio',
            'year' => 2025,
            'rating' => 4.5,
        ]);

        $this->get(route('anime.index'))
            ->assertOk()
            ->assertViewIs('catalog.index')
            ->assertSee('Anime de portada')
            ->assertSee('Estudio')
            ->assertViewHas('items', fn ($items) => $items->count() === 1
                && $items->first()['url'] === route('anime.show', 'anime-portada'));
    }

    public function test_manga_catalog_renders_manga_metadata(): void
    {
        Manga::query()->create([
            'title' => 'Manga de portada',
            'slug' => 'manga-portada',
            'author' => 'Autora',
            'artist' => 'Artista',
            'rating' => 4.2,
        ]);

        $this->get(route('manga.index'))
            ->assertOk()
            ->assertSee('Manga de portada')
            ->assertSee('Autora')
            ->assertViewHas('section', 'manga');
    }

    public function test_music_catalog_renders_queue_player_for_tracks_with_a_source(): void
    {
        $track = MusicTrack::query()->create([
            'title' => 'Canción de portada',
            'slug' => 'cancion-portada',
            'artist' => 'Artista',
            'audio_url' => '/audio/cancion.mp3',
        ]);

        $this->get(route('music.index'))
            ->assertOk()
            ->assertSee('Canción de portada')
            ->assertSee('data-play-track="'.$track->id.'"', false)
            ->assertSee('catalog-music-audio')
            ->assertViewHas('section', 'musica')
            ->assertViewHas('musicPlayerTracks', fn ($tracks) => $tracks->first()['id'] === $track->id
                && $tracks->first()['url'] === '/audio/cancion.mp3');
    }

    public function test_music_player_receives_saved_track_position_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $track = MusicTrack::query()->create([
            'title' => 'Canción con progreso',
            'slug' => 'cancion-con-progreso',
            'artist' => 'Artista',
            'audio_url' => '/audio/progreso.mp3',
        ]);
        $user->progress()->create([
            'progressable_type' => 'music',
            'progressable_id' => $track->id,
            'position' => 42,
            'percentage' => 25,
        ]);

        $this->actingAs($user)
            ->get(route('music.index'))
            ->assertOk()
            ->assertViewHas('musicPlayerTracks', fn ($tracks) => $tracks->first()['position'] === 42);
    }

    public function test_game_catalog_renders_platform_and_release_year(): void
    {
        Game::query()->create([
            'title' => 'Juego de portada',
            'slug' => 'juego-portada',
            'platforms' => ['Nintendo Switch'],
            'release_year' => 2024,
            'rating' => 4.8,
        ]);

        $this->get(route('games.index'))
            ->assertOk()
            ->assertSee('Juego de portada')
            ->assertSee('Nintendo Switch')
            ->assertSee('2024')
            ->assertViewHas('section', 'juegos');
    }

    public function test_guest_catalog_shows_sign_in_instead_of_an_account_menu(): void
    {
        $this->get(route('anime.index'))
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertDontSee('data-account-menu-items', false);
    }

    public function test_catalog_escapes_a_title_containing_html(): void
    {
        Anime::query()->create([
            'title' => '<script>alert("xss")</script>',
            'slug' => 'titulo-inseguro',
        ]);

        $this->get(route('anime.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_public_and_authenticated_catalogs_return_to_their_appropriate_home(): void
    {
        $catalogRoutes = ['anime.index', 'music.index', 'manga.index', 'games.index'];

        foreach ($catalogRoutes as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('href="'.route('dashboard').'"', false)
                ->assertSee('data-sidebar-back', false)
                ->assertSee('data-sidebar-back-history="false"', false);
        }

        $user = User::factory()->create();

        foreach ($catalogRoutes as $route) {
            $response = $this->actingAs($user)->get(route($route));

            $response
                ->assertOk()
                ->assertSee('href="'.route('dashboard').'"', false)
                ->assertSee('data-sidebar-back', false)
                ->assertSee('data-sidebar-back-history="false"', false);

            $this->assertAuthenticatedAs($user);
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
    }
}
