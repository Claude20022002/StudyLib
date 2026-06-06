<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Bon retour');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'etudiant@hestim.ma',
            'password' => 'password',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'etudiant@hestim.ma',
            'password' => 'password',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'etudiant@hestim.ma',
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create(['email' => 'auth@hestim.ma']);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_is_rate_limited_after_too_many_attempts(): void
    {
        User::factory()->create([
            'email' => 'limit@hestim.ma',
            'password' => 'password',
        ]);

        RateLimiter::clear(Str::transliterate(Str::lower('limit@hestim.ma').'|127.0.0.1'));
        RateLimiter::clear('limit@hestim.ma|127.0.0.1');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('login'))
                ->post(route('login.store'), [
                    'email' => 'limit@hestim.ma',
                    'password' => 'wrong-password',
                ])
                ->assertRedirect(route('login'));
        }

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => 'limit@hestim.ma',
                'password' => 'wrong-password',
            ])
            ->assertStatus(429);
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::factory()->create(['email' => 'logout@hestim.ma']);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
