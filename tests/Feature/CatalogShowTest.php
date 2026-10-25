<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_anime_detail_renders_published_episodes_and_saved_progress(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Anime detalle', 'slug' => 'anime-detalle']);
        $episode = $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 1,
            'title' => 'Primer episodio',
        ]);
        $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 2,
            'status' => 'draft',
        ]);
        $user->progress()->create([
            'progressable_type' => 'episode',
            'progressable_id' => $episode->id,
            'percentage' => 55,
            'position' => 120,
        ]);

        $this->actingAs($user)
            ->get(route('anime.show', $anime->slug))
            ->assertOk()
            ->assertViewIs('catalog.show')
            ->assertSee('Anime detalle')
            ->assertSee('T1 · E1')
            ->assertSee('55%')
            ->assertDontSee('T1 · E2');
    }

    public function test_manga_detail_renders_published_chapters_and_page_counts(): void
    {
        $manga = Manga::query()->create(['title' => 'Manga detalle', 'slug' => 'manga-detalle', 'author' => 'Autora']);
        $chapter = $manga->chapters()->create(['chapter_number' => 7, 'title' => 'La aventura']);
        $chapter->pages()->create(['page_number' => 1, 'image_path' => 'manga/page-1.jpg']);

        $this->get(route('manga.show', $manga->slug))
            ->assertOk()
            ->assertSee('Manga detalle')
            ->assertSee('Capítulo 7')
            ->assertSee('1 página');
    }

    public function test_music_detail_renders_track_metadata(): void
    {
        MusicTrack::query()->create([
            'title' => 'Canción detalle',
            'slug' => 'cancion-detalle',
            'artist' => 'Artista',
            'album' => 'Álbum',
            'duration' => 185,
        ]);

        $this->get(route('music.show', 'cancion-detalle'))
            ->assertOk()
            ->assertSee('Canción detalle')
            ->assertSee('Artista')
            ->assertSee('Álbum')
            ->assertSee('3:05');
    }

    public function test_game_detail_records_visit_with_the_morph_alias(): void
    {
        $user = User::factory()->create();
        $game = Game::query()->create(['title' => 'Juego detalle', 'slug' => 'juego-detalle']);

        $this->actingAs($user)
            ->get(route('games.show', $game->slug))
            ->assertOk()
            ->assertSee('Juego detalle');

        $this->assertDatabaseHas('media_progress', [
            'user_id' => $user->id,
            'progressable_type' => 'game',
            'progressable_id' => $game->id,
        ]);
    }

    public function test_anime_rating_uses_a_ten_point_scale_on_its_detail_page(): void
    {
        $anime = Anime::query()->create([
            'title' => 'Anime puntuación',
            'slug' => 'anime-puntuacion',
            'rating' => 8.7,
        ]);

        $this->get(route('anime.show', $anime->slug))
            ->assertOk()
            ->assertSee('★ 8.7 / 10');
    }

    public function test_game_rating_keeps_a_five_point_scale_on_its_detail_page(): void
    {
        $game = Game::query()->create([
            'title' => 'Juego puntuación',
            'slug' => 'juego-puntuacion',
            'rating' => 4.5,
        ]);

        $this->get(route('games.show', $game->slug))
            ->assertOk()
            ->assertSee('★ 4.5 / 5');
    }

    public function test_authenticated_user_sees_favorite_and_pending_states_on_detail_page(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Estado personal', 'slug' => 'estado-personal']);
        $user->favorites()->create([
            'favoritable_type' => 'anime',
            'favoritable_id' => $anime->id,
        ]);
        $user->pendingItems()->create([
            'section' => 'anime',
            'pendingable_type' => 'anime',
            'pendingable_id' => $anime->id,
        ]);

        $this->actingAs($user)
            ->get(route('anime.show', $anime->slug))
            ->assertOk()
            ->assertSee('❤️ En favoritos')
            ->assertSee('⏳ En pendientes');
    }
}
