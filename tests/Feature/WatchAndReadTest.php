<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WatchAndReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_anime_watch_page_renders_source_navigation_and_episode_list(): void
    {
        $anime = Anime::query()->create(['title' => 'Anime lector', 'slug' => 'anime-lector']);
        $first = $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 1,
            'title' => 'Inicio',
            'status' => 'published',
            'video_url' => 'https://media.example.test/episode-1.mp4',
        ]);
        $second = $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 2,
            'title' => 'Continuación',
            'status' => 'published',
            'video_url' => null,
        ]);

        $this->get(route('anime.watch', [$anime->slug, $second->id]))
            ->assertOk()
            ->assertViewIs('watch.anime')
            ->assertSee('Este episodio no tiene fuente de vídeo')
            ->assertSee(route('anime.watch', [$anime->slug, $first->id]))
            ->assertSee('Episodios · 2')
            ->assertSee('aria-current="page"', false);

        $this->get(route('anime.watch', [$anime->slug, $first->id]))
            ->assertOk()
            ->assertSee('https://media.example.test/episode-1.mp4');
    }

    public function test_anime_watch_page_hides_unpublished_and_unrelated_episodes(): void
    {
        $anime = Anime::query()->create(['title' => 'Anime publicado', 'slug' => 'anime-publicado']);
        $otherAnime = Anime::query()->create(['title' => 'Otro anime', 'slug' => 'otro-anime']);
        $episode = $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 1,
            'status' => 'published',
        ]);
        $unpublished = $anime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 2,
            'title' => 'No publicado',
            'status' => 'draft',
        ]);
        $unrelated = $otherAnime->episodes()->create([
            'season_number' => 1,
            'episode_number' => 1,
            'status' => 'published',
        ]);

        $this->get(route('anime.watch', [$anime->slug, $episode->id]))
            ->assertOk()
            ->assertDontSee('No publicado')
            ->assertDontSee(route('anime.watch', [$anime->slug, $unrelated->id]));

        $this->get(route('anime.watch', [$anime->slug, $unpublished->id]))->assertNotFound();
        $this->get(route('anime.watch', [$anime->slug, $unrelated->id]))->assertNotFound();
    }

    public function test_manga_reader_renders_real_pages_chapter_navigation_and_saved_progress(): void
    {
        $user = User::factory()->create();
        $manga = Manga::query()->create(['title' => 'Manga lector', 'slug' => 'manga-lector']);
        $previous = $manga->chapters()->create([
            'chapter_number' => 1,
            'title' => 'Antes',
            'status' => 'published',
        ]);
        $chapter = $manga->chapters()->create([
            'chapter_number' => 2,
            'title' => 'Actual',
            'status' => 'published',
        ]);
        $chapter->pages()->create(['page_number' => 1, 'image_path' => 'storage/manga/page-1.jpg']);
        $chapter->pages()->create(['page_number' => 2, 'image_path' => 'storage/manga/page-2.jpg']);
        $user->progress()->create([
            'progressable_type' => 'chapter',
            'progressable_id' => $chapter->id,
            'position' => 2,
            'percentage' => 50,
        ]);

        $this->actingAs($user)
            ->get(route('manga.read', [$manga->slug, $chapter->id]))
            ->assertOk()
            ->assertViewIs('watch.manga')
            ->assertSee('/storage/manga/page-1.jpg')
            ->assertSee('/storage/manga/page-2.jpg')
            ->assertSee('Pág. 1 / 2')
            ->assertSee(route('manga.read', [$manga->slug, $previous->id]))
            ->assertSee('Cap. 1')
            ->assertViewHas('userProgress', fn ($progress) => $progress['position'] === 2);
    }

    public function test_manga_reader_shows_empty_state_for_chapters_without_pages(): void
    {
        $manga = Manga::query()->create(['title' => 'Manga vacío', 'slug' => 'manga-vacio']);
        $chapter = $manga->chapters()->create([
            'chapter_number' => 1,
            'status' => 'published',
        ]);

        $this->get(route('manga.read', [$manga->slug, $chapter->id]))
            ->assertOk()
            ->assertSee('Este capítulo no tiene páginas disponibles')
            ->assertSee('Pág. 1 / —');
    }

    public function test_manga_reader_does_not_expose_unpublished_or_foreign_chapters(): void
    {
        $manga = Manga::query()->create(['title' => 'Manga visible', 'slug' => 'manga-visible']);
        $otherManga = Manga::query()->create(['title' => 'Manga ajeno', 'slug' => 'manga-ajeno']);
        $chapter = $manga->chapters()->create(['chapter_number' => 1, 'status' => 'published']);
        $unpublished = $manga->chapters()->create(['chapter_number' => 2, 'status' => 'draft']);
        $foreign = $otherManga->chapters()->create(['chapter_number' => 1, 'status' => 'published']);

        $this->get(route('manga.read', [$manga->slug, $chapter->id]))
            ->assertOk()
            ->assertDontSee(route('manga.read', [$manga->slug, $foreign->id]));

        $this->get(route('manga.read', [$manga->slug, $unpublished->id]))->assertNotFound();
        $this->get(route('manga.read', [$manga->slug, $foreign->id]))->assertNotFound();
    }
}
