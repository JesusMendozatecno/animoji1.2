<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_section_lists(): void
    {
        $this->get(route('anime.listas'))->assertRedirect(route('login'));
    }

    public function test_user_only_sees_their_own_lists_for_the_requested_section(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $user->lists()->create(['section' => 'anime', 'name' => 'Mis animes']);
        $user->lists()->create(['section' => 'manga', 'name' => 'Mis mangas']);
        $otherUser->lists()->create(['section' => 'anime', 'name' => 'Lista privada ajena']);

        $this->actingAs($user)
            ->get(route('anime.listas'))
            ->assertOk()
            ->assertViewIs('personal.listas')
            ->assertSee('Mis animes')
            ->assertDontSee('Mis mangas')
            ->assertDontSee('Lista privada ajena');
    }

    public function test_user_can_close_the_selected_list_panel(): void
    {
        $user = User::factory()->create();
        $user->lists()->create(['section' => 'anime', 'name' => 'Lista abierta']);

        $this->actingAs($user)
            ->get(route('anime.listas', ['list' => 0]))
            ->assertOk()
            ->assertViewHas('selectedId', null);
    }

    public function test_user_can_create_a_list_and_duplicate_names_are_rejected(): void
    {
        $user = User::factory()->create();
        $list = $user->lists()->create(['section' => 'anime', 'name' => 'Para ver']);

        $this->actingAs($user)
            ->from(route('anime.listas'))
            ->post(route('anime.listas.store'), ['name' => 'Para ver'])
            ->assertRedirect(route('anime.listas'))
            ->assertSessionHas('error', 'Ya tienes una lista con ese nombre en esta sección.');

        $this->assertDatabaseCount('user_lists', 1);
        $this->assertModelExists($list);
    }

    public function test_user_can_create_a_list_in_the_requested_section(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('anime.listas.store'), ['name' => 'Favoritos de temporada'])
            ->assertRedirect()
            ->assertSessionHas('success', 'Lista creada correctamente.');

        $this->assertDatabaseHas('user_lists', [
            'user_id' => $user->id,
            'section' => 'anime',
            'name' => 'Favoritos de temporada',
        ]);
    }

    public function test_user_can_add_catalog_content_to_a_list_using_the_morph_alias(): void
    {
        $user = User::factory()->create();
        $list = $user->lists()->create(['section' => 'anime', 'name' => 'Favoritos']);
        $anime = Anime::query()->create(['title' => 'Anime de lista', 'slug' => 'anime-de-lista']);

        $this->actingAs($user)
            ->post(route('listas.items.store', $list), ['id' => $anime->id])
            ->assertRedirect()
            ->assertSessionHas('success', 'Añadido a la lista.');

        $this->assertDatabaseHas('list_items', [
            'user_list_id' => $list->id,
            'itemable_type' => 'anime',
            'itemable_id' => $anime->id,
            'position' => 1,
        ]);
    }

    public function test_user_can_search_for_items_from_the_blade_lists_page(): void
    {
        $user = User::factory()->create();
        $list = $user->lists()->create(['section' => 'anime', 'name' => 'Por ver']);
        Anime::query()->create(['title' => 'Búsqueda de anime', 'slug' => 'busqueda-de-anime', 'studio' => 'Estudio']);

        $this->actingAs($user)
            ->get(route('anime.listas', ['list' => $list->id, 'q' => 'Búsqueda']))
            ->assertOk()
            ->assertSee('Búsqueda de anime')
            ->assertSee('Estudio')
            ->assertViewHas('searchResults', fn ($items) => $items->count() === 1);
    }

    public function test_catalog_json_search_returns_results_for_existing_vue_clients(): void
    {
        $user = User::factory()->create();
        Anime::query()->create(['title' => 'Resultado JSON', 'slug' => 'resultado-json']);

        $this->actingAs($user)
            ->getJson(route('personal.buscar', ['section' => 'anime', 'q' => 'Resultado']))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Resultado JSON']);
    }

    public function test_user_can_rename_a_list(): void
    {
        $user = User::factory()->create();
        $list = $user->lists()->create(['section' => 'anime', 'name' => 'Nombre anterior']);

        $this->actingAs($user)
            ->put(route('listas.update', $list), ['name' => 'Nombre nuevo'])
            ->assertRedirect()
            ->assertSessionHas('success', 'La lista se renombró correctamente.');

        $this->assertDatabaseHas('user_lists', [
            'id' => $list->id,
            'name' => 'Nombre nuevo',
        ]);

    }

    public function test_user_can_delete_a_list_and_its_items(): void
    {
        $user = User::factory()->create();
        $list = $user->lists()->create(['section' => 'anime', 'name' => 'Lista para borrar']);
        $anime = Anime::query()->create(['title' => 'Contenido de lista', 'slug' => 'contenido-lista']);
        $listItem = $list->items()->create([
            'itemable_type' => $anime->getMorphClass(),
            'itemable_id' => $anime->id,
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('listas.destroy', $list))
            ->assertRedirect()
            ->assertSessionHas('success', 'La lista se eliminó correctamente.');

        $this->assertDatabaseMissing('user_lists', ['id' => $list->id]);
        $this->assertDatabaseMissing('list_items', ['id' => $listItem->id]);
    }

    public function test_user_can_remove_an_item_from_a_list(): void
    {
        $user = User::factory()->create();
        $list = $user->lists()->create(['section' => 'anime', 'name' => 'Lista']);
        $anime = Anime::query()->create(['title' => 'Elemento removible', 'slug' => 'elemento-removible']);
        $listItem = $list->items()->create([
            'itemable_type' => $anime->getMorphClass(),
            'itemable_id' => $anime->id,
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('listas.items.destroy', [$list, $listItem->id]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Elemento retirado de la lista.');

        $this->assertDatabaseMissing('list_items', ['id' => $listItem->id]);
    }

    public function test_user_cannot_add_content_from_a_different_section_to_a_list(): void
    {
        $user = User::factory()->create();
        $list = $user->lists()->create(['section' => 'anime', 'name' => 'Solo anime']);
        $manga = \App\Models\Manga::query()->create(['title' => 'Manga ajeno a la sección', 'slug' => 'manga-ajeno']);

        $this->actingAs($user)
            ->post(route('listas.items.store', $list), ['id' => $manga->id])
            ->assertRedirect()
            ->assertSessionHas('error', 'El contenido solicitado no existe en esta sección.');

        $this->assertDatabaseCount('list_items', 0);
    }

    public function test_user_cannot_manage_another_users_list(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $list = $owner->lists()->create(['section' => 'anime', 'name' => 'Lista privada']);

        $this->actingAs($otherUser)
            ->delete(route('listas.destroy', $list))
            ->assertForbidden();

        $this->assertModelExists($list);
    }

    public function test_list_name_is_escaped_in_the_blade_page(): void
    {
        $user = User::factory()->create();
        $user->lists()->create([
            'section' => 'anime',
            'name' => '<script>alert("xss")</script>',
        ]);

        $this->actingAs($user)
            ->get(route('anime.listas'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_list_page_displays_legacy_class_name_morph_records(): void
    {
        $user = User::factory()->create();
        $list = $user->lists()->create(['section' => 'anime', 'name' => 'Lista antigua']);
        $anime = Anime::query()->create(['title' => 'Registro heredado', 'slug' => 'registro-heredado']);
        $list->items()->create([
            'itemable_type' => Anime::class,
            'itemable_id' => $anime->id,
            'position' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('anime.listas', ['list' => $list->id]))
            ->assertOk()
            ->assertSee('Registro heredado');
    }
}
