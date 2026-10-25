<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\MusicTrack;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_section_favorites(): void
    {
        $this->get(route('anime.favoritos'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_own_favorites_in_blade_for_each_catalog_section(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Anime favorito', 'slug' => 'anime-favorito', 'studio' => 'Estudio']);
        $manga = Manga::query()->create(['title' => 'Manga favorito', 'slug' => 'manga-favorito', 'author' => 'Autora']);
        $music = MusicTrack::query()->create(['title' => 'Canción favorita', 'slug' => 'cancion-favorita', 'artist' => 'Artista']);
        $game = Game::query()->create(['title' => 'Juego favorito', 'slug' => 'juego-favorito', 'release_year' => 2025]);

        foreach ([$anime, $manga, $music, $game] as $item) {
            $user->favorites()->create([
                'favoritable_type' => $item->getMorphClass(),
                'favoritable_id' => $item->id,
            ]);
        }

        $this->actingAs($user)
            ->get(route('anime.favoritos'))
            ->assertOk()
            ->assertViewIs('personal.favoritos')
            ->assertSee('Mis favoritos de anime')
            ->assertSee('Anime favorito')
            ->assertDontSee('Manga favorito')
            ->assertSee('Estudio')
            ->assertViewHas('items', fn ($items) => $items->total() === 1);

        $this->get(route('manga.favoritos'))
            ->assertOk()
            ->assertSee('Mis mangas favoritos')
            ->assertSee('Manga favorito')
            ->assertSee('Autora');

        $this->get(route('musica.favoritos'))
            ->assertOk()
            ->assertSee('Mis canciones favoritas')
            ->assertSee('Canción favorita')
            ->assertSee('Artista');

        $this->get(route('juegos.favoritos'))
            ->assertOk()
            ->assertSee('Mis videojuegos favoritos')
            ->assertSee('Juego favorito')
            ->assertSee('2025');
    }

    public function test_user_does_not_see_another_users_favorites(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Favorito privado', 'slug' => 'favorito-privado']);
        $otherUser->favorites()->create([
            'favoritable_type' => $anime->getMorphClass(),
            'favoritable_id' => $anime->id,
        ]);

        $this->actingAs($user)
            ->get(route('anime.favoritos'))
            ->assertOk()
            ->assertSee('Aún no tienes favoritos')
            ->assertDontSee('Favorito privado')
            ->assertViewHas('items', fn ($items) => $items->total() === 0);
    }

    public function test_favorites_saved_with_model_class_names_remain_visible(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Favorito heredado', 'slug' => 'favorito-heredado']);
        $user->favorites()->create([
            'favoritable_type' => Anime::class,
            'favoritable_id' => $anime->id,
        ]);

        $this->actingAs($user)
            ->get(route('anime.favoritos'))
            ->assertOk()
            ->assertSee('Favorito heredado');
    }

    public function test_user_can_remove_a_favorite_from_the_blade_page(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Quitar favorito', 'slug' => 'quitar-favorito']);
        $favorite = $user->favorites()->create([
            'favoritable_type' => $anime->getMorphClass(),
            'favoritable_id' => $anime->id,
        ]);

        $this->actingAs($user)
            ->from(route('anime.favoritos'))
            ->post(route('personal.favoritos.toggle'), [
                'section' => 'anime',
                'id' => $anime->id,
            ])
            ->assertRedirect(route('anime.favoritos'));

        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function test_user_can_add_a_favorite_and_toggle_it_off_again(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Nuevo favorito', 'slug' => 'nuevo-favorito']);

        $this->actingAs($user)
            ->post(route('personal.favoritos.toggle'), [
                'section' => 'anime',
                'id' => $anime->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Añadido a tus favoritos ❤️');

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_type' => 'anime',
            'favoritable_id' => $anime->id,
        ]);

        $this->post(route('personal.favoritos.toggle'), [
            'section' => 'anime',
            'id' => $anime->id,
        ])->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'favoritable_type' => 'anime',
            'favoritable_id' => $anime->id,
        ]);
    }

    public function test_favorites_page_paginates_ten_items_and_preserves_page_query(): void
    {
        $user = User::factory()->create();

        for ($index = 1; $index <= 11; $index++) {
            $anime = Anime::query()->create([
                'title' => 'Anime favorito '.$index,
                'slug' => 'anime-favorito-'.$index,
            ]);
            $user->favorites()->create([
                'favoritable_type' => $anime->getMorphClass(),
                'favoritable_id' => $anime->id,
            ]);
        }

        $this->actingAs($user)
            ->get(route('anime.favoritos', ['page' => 2]))
            ->assertOk()
            ->assertViewHas('items', fn ($items) => $items->total() === 11
                && $items->currentPage() === 2
                && $items->count() === 1);
    }

    public function test_favorite_title_is_escaped_in_the_blade_list(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create([
            'title' => '<script>alert("xss")</script>',
            'slug' => 'titulo-xss',
        ]);
        $user->favorites()->create([
            'favoritable_type' => $anime->getMorphClass(),
            'favoritable_id' => $anime->id,
        ]);

        $this->actingAs($user)
            ->get(route('anime.favoritos'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }
}
