<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // =====================
    // REGISTRATION TESTS
    // =====================

    public function test_registration_page_loads(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    public function test_user_can_register_with_valid_details(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_registration_requires_a_name(): void
    {
        $this->post('/register', [
            'name'                  => '',
            'email'                 => 'john@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['name']);

        $this->assertGuest();
    }

    public function test_registration_requires_a_valid_email(): void
    {
        $this->post('/register', [
            'name'                  => 'John Doe',
            'email'                 => 'not-a-valid-email',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_registration_requires_a_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/register', [
            'name'                  => 'John Doe',
            'email'                 => 'taken@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_registration_requires_password_confirmation_to_match(): void
    {
        $this->post('/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password',
            'password_confirmation' => 'different-password',
        ])->assertSessionHasErrors(['password']);

        $this->assertGuest();
    }

    public function test_registration_requires_minimum_password_length(): void
    {
        $this->post('/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => '123',
            'password_confirmation' => '123',
        ])->assertSessionHasErrors(['password']);

        $this->assertGuest();
    }

    // =====================
    // LOGIN TESTS
    // =====================

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_user_cannot_login_with_nonexistent_email(): void
    {
        $this->post('/login', [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_blocked_user_cannot_login(): void
    {
        $user = User::factory()->blocked()->create();

        $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_blocked_user_sees_blocked_error_message(): void
    {
        $user = User::factory()->blocked()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'Your account has been blocked. Please contact support.',
        ]);
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/login')->assertRedirect();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}