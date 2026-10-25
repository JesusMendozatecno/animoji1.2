<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertStatus(200)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('Volver al inicio público');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $response
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHas('status', 'Tu cuenta se creó correctamente. Inicia sesión para continuar.');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Tu cuenta se creó correctamente. Inicia sesión para continuar.')
            ->assertSee('Volver al inicio público');

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasAnyRole(['admin', 'superadmin']));
    }

    public function test_registration_displays_password_confirmation_error(): void
    {
        app()->setLocale('en');

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $this->assertGuest();
        $response
            ->assertRedirect('/register')
            ->assertSessionHasErrors([
                'password' => 'La confirmación de la contraseña no coincide.',
            ]);
    }
}
