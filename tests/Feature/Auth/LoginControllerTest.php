<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_panel_to_login(): void
    {
        $this->get(route('panel.boletos.index'))->assertRedirect(route('login'));
    }

    public function test_renders_login_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('¡Bienvenido!');
    }

    public function test_valid_credentials_log_in_and_redirect_to_panel(): void
    {
        $user = User::factory()->create(['password' => 'secreta123']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'secreta123'])
            ->assertRedirect(route('panel.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_rejects_wrong_password_with_spanish_message(): void
    {
        $user = User::factory()->create(['password' => 'secreta123']);

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Usuario o contraseña incorrectos.']);

        $this->assertGuest();
    }

    public function test_blocks_login_after_five_failed_attempts(): void
    {
        $user = User::factory()->create(['password' => 'secreta123']);

        foreach (range(1, 5) as $intento) {
            $this->post(route('login.store'), ['email' => $user->email, 'password' => 'incorrecta']);
        }

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'secreta123'])
            ->assertTooManyRequests();

        $this->assertGuest();
    }

    public function test_logout_ends_session_and_redirects_to_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
