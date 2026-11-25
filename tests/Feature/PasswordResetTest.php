<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $this->get('/forgot-password')->assertStatus(200);
    }

    public function test_password_can_be_reset_with_registered_email(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('passwordbaru', $user->fresh()->password));
    }

    public function test_password_cannot_be_reset_with_unregistered_email(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'tidakada@example.com',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_must_be_confirmed_and_min_8_chars(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', [
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');

        $this->post('/forgot-password', [
            'email' => $user->email,
            'password' => 'passwordbaru',
            'password_confirmation' => 'beda12345',
        ])->assertSessionHasErrors('password');
    }
}