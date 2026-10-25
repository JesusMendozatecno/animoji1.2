<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalPendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_pending_item_with_the_morph_alias(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Pendiente nuevo', 'slug' => 'pendiente-nuevo']);

        $this->actingAs($user)
            ->post(route('personal.pendientes.toggle'), [
                'section' => 'anime',
                'id' => $anime->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Añadido a tus pendientes ⏳');

        $this->assertDatabaseHas('pending_items', [
            'user_id' => $user->id,
            'section' => 'anime',
            'pendingable_type' => 'anime',
            'pendingable_id' => $anime->id,
        ]);
    }

    public function test_user_can_remove_a_pending_item_saved_with_a_model_class_name(): void
    {
        $user = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Pendiente heredado', 'slug' => 'pendiente-heredado']);
        $pending = $user->pendingItems()->create([
            'section' => 'anime',
            'pendingable_type' => Anime::class,
            'pendingable_id' => $anime->id,
        ]);

        $this->actingAs($user)
            ->post(route('personal.pendientes.toggle'), [
                'section' => 'anime',
                'id' => $anime->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Retirado de tus pendientes.');

        $this->assertDatabaseMissing('pending_items', ['id' => $pending->id]);
    }

    public function test_pending_page_renders_owned_section_items_and_legacy_morph_types(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $anime = Anime::query()->create(['title' => 'Pendiente antiguo', 'slug' => 'pendiente-antiguo']);
        $otherAnime = Anime::query()->create(['title' => 'Pendiente de otra persona', 'slug' => 'pendiente-ajeno']);
        $user->pendingItems()->create([
            'section' => 'anime',
            'pendingable_type' => Anime::class,
            'pendingable_id' => $anime->id,
        ]);
        $otherUser->pendingItems()->create([
            'section' => 'anime',
            'pendingable_type' => $otherAnime->getMorphClass(),
            'pendingable_id' => $otherAnime->id,
        ]);

        $this->actingAs($user)
            ->get(route('anime.pendientes'))
            ->assertOk()
            ->assertViewIs('personal.pendientes')
            ->assertSee('Pendiente antiguo')
            ->assertDontSee('Pendiente de otra persona')
            ->assertSee('1/10');
    }
}
