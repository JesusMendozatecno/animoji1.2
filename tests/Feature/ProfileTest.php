<?php

namespace Tests\Feature;

use App\Models\Anime;
use App\Models\Episode;
use App\Models\HelpManual;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response
            ->assertOk()
            ->assertSee('action="'.route('profile.update').'"', false)
            ->assertSee('action="'.route('password.update', ['section' => 'password']).'"', false)
            ->assertSee('action="'.route('profile.destroy', ['section' => 'delete']).'"', false)
            ->assertSee('action="'.route('profile.avatar', ['section' => 'cuenta']).'"', false)
            ->assertSee('data-profile-tab="cuenta" data-profile-active="true"', false)
            ->assertSee('data-profile-tab="password"', false)
            ->assertSee('data-profile-tab="delete"', false)
            ->assertSee('data-profile-tab="preferencias"', false)
            ->assertSee('data-profile-tab="documentacion"', false)
            ->assertSee('data-profile-tab="historial"', false)
            ->assertSee('data-sidebar-back', false)
            ->assertSee('href="'.route('dashboard').'" data-sidebar-back data-sidebar-back-history="false"', false);

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_profile_page_can_open_each_section_without_leaving_the_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile?section=preferencias');

        $response
            ->assertOk()
            ->assertSee('data-profile-tab="preferencias" data-profile-active="true"', false)
            ->assertSee('data-profile-tab="cuenta" data-profile-active="false"', false)
            ->assertSee('id="preferencias" data-profile-panel="preferencias"', false)
            ->assertSee('id="documentacion" data-profile-panel="documentacion" ', false)
            ->assertSee('id="historial" data-profile-panel="historial" ', false);
    }

    public function test_avatar_validation_error_does_not_open_the_avatar_modal_automatically(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile?section=cuenta')
            ->followingRedirects()
            ->post('/profile/avatar', [
                '_profile_section' => 'cuenta',
            ]);

        $response
            ->assertOk()
            ->assertSee('Selecciona una imagen para tu foto de perfil.')
            ->assertSee('id="avatar-modal" class="fixed inset-0 z-[95] hidden', false)
            ->assertDontSee("if (document.querySelector('#avatar-form p.text-red-400')) open();", false);
    }

    public function test_password_validation_error_returns_to_the_password_section(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile?section=password')
            ->put('/password', [
                '_profile_section' => 'password',
                'current_password' => 'incorrect-password',
                'password' => 'valid-password',
                'password_confirmation' => 'valid-password',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect('/profile?section=password');

        $this->get('/profile?section=password')
            ->assertOk()
            ->assertSee('data-profile-tab="password" data-profile-active="true"', false);
    }

    public function test_profile_page_shows_only_published_manuals_and_the_users_own_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $anime = Anime::query()->create([
            'title' => 'Anime propio',
            'slug' => 'anime-propio',
        ]);
        $otherAnime = Anime::query()->create([
            'title' => 'Anime ajeno',
            'slug' => 'anime-ajeno',
        ]);
        $episode = Episode::query()->create([
            'anime_id' => $anime->id,
            'episode_number' => 2,
            'title' => 'Episodio especial',
        ]);
        $user->progress()->create([
            'progressable_type' => $anime->getMorphClass(),
            'progressable_id' => $anime->id,
            'percentage' => 45,
        ]);
        $user->progress()->create([
            'progressable_type' => $episode->getMorphClass(),
            'progressable_id' => $episode->id,
            'percentage' => 75,
        ]);
        $otherUser->progress()->create([
            'progressable_type' => $otherAnime->getMorphClass(),
            'progressable_id' => $otherAnime->id,
            'percentage' => 90,
        ]);
        HelpManual::query()->create([
            'title' => 'Guía publicada',
            'summary' => 'Resumen visible',
            'content' => 'Contenido de la guía',
            'status' => 'published',
        ]);
        HelpManual::query()->create([
            'title' => 'Guía en borrador',
            'status' => 'draft',
        ]);
        HelpManual::query()->create([
            'title' => 'Guía programada',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/profile?section=historial');

        $response
            ->assertOk()
            ->assertSee('Guía publicada')
            ->assertDontSee('Guía en borrador')
            ->assertDontSee('Guía programada')
            ->assertSee('Anime propio')
            ->assertDontSee('Anime ajeno')
            ->assertSee('E2 — Episodio especial')
            ->assertSee('45% completado');
    }

    public function test_profile_theme_can_be_changed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile/theme', [
                'index_theme' => 'morado',
            ]);

        $response
            ->assertRedirect('/profile?section=preferencias')
            ->assertSessionHas('profile_section', 'preferencias');
        $this->assertSame('morado', $user->refresh()->index_theme);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile?section=delete')
            ->delete('/profile', [
                '_profile_section' => 'delete',
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile?section=delete');

        $this->assertNotNull($user->fresh());
    }
}
