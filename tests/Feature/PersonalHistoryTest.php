<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Chapter;
use App\Models\Episode;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_section_history(): void
    {
        $this->get(route('anime.seguir'))->assertRedirect(route('login'));
    }

    public function test_anime_history_renders_alias_and_legacy_episode_progress_with_continue_links(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Anime para continuar', 'slug' => 'anime-continuar']);
        $episode = $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 2,
            'title' => 'La continuación',
        ]);
        $legacyEpisode = $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 3,
            'title' => 'Progreso antiguo',
        ]);
        $user->progress()->create([
            'progressable_type' => $episode->getMorphClass(),
            'progressable_id' => $episode->id,
            'position' => 300,
            'percentage' => 40,
        ]);
        $user->progress()->create([
            'progressable_type' => Episode::class,
            'progressable_id' => $legacyEpisode->id,
            'position' => 450,
            'percentage' => 75,
        ]);

        $this->actingAs($user)
            ->get(route('anime.seguir'))
            ->assertOk()
            ->assertViewIs('personal.seguir')
            ->assertSee('Anime para continuar')
            ->assertSee('T1 · E2')
            ->assertSee('T1 · E3')
            ->assertViewHas('items', fn ($items) => $items->count() === 2
                && $items->contains(fn ($item) => $item['continue_url'] === route('anime.watch', [$anime->slug, $episode->id])));
    }

    public function test_manga_history_renders_chapter_continue_link(): void
    {
        $user = User::factory()->create();
        $manga = Manga::query()->create(['title' => 'Manga para continuar', 'slug' => 'manga-continuar', 'author' => 'Autora']);
        $chapter = $manga->chapters()->create([
            'chapter_number' => 4,
            'title' => 'Regreso',
        ]);
        $user->progress()->create([
            'progressable_type' => $chapter->getMorphClass(),
            'progressable_id' => $chapter->id,
            'position' => 12,
            'percentage' => 60,
        ]);

        $this->actingAs($user)
            ->get(route('manga.seguir'))
            ->assertOk()
            ->assertViewIs('personal.seguir')
            ->assertSee('Manga para continuar')
            ->assertSee('Capítulo 4')
            ->assertViewHas('items', fn ($items) => $items->first()['continue_url'] === route('manga.read', [$manga->slug, $chapter->id]));
    }

    public function test_user_can_save_episode_progress_using_its_morph_alias(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Anime con progreso', 'slug' => 'anime-progreso']);
        $episode = $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('personal.progreso'), [
                'section' => 'anime',
                'id' => $anime->id,
                'target' => 'episode',
                'target_id' => $episode->id,
                'position' => 120,
                'percentage' => 25,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('media_progress', [
            'user_id' => $user->id,
            'progressable_type' => 'episode',
            'progressable_id' => $episode->id,
            'position' => 120,
            'percentage' => '25.00',
        ]);
    }
}
