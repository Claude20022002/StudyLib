<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Filiere;
use App\Models\User;
use Database\Seeders\FiliereSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FiliereSeeder::class);
    }

    public function test_register_page_is_accessible_to_guests(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Activez votre accès HESTIM')
            ->assertSee('Génie Informatique');
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $filiere = Filiere::query()->where('code', 'GI')->firstOrFail();

        $this->post(route('register.store'), [
            'name' => 'Étudiant Test',
            'email' => 'nouveau@hestim.ma',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'filiere_id' => $filiere->id,
            'year_level' => 2,
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'nouveau@hestim.ma',
            'name' => 'Étudiant Test',
            'filiere_id' => $filiere->id,
            'year_level' => 2,
        ]);
    }

    public function test_register_rejects_non_hestim_email(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Externe Test',
                'email' => 'externe@gmail.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existant@hestim.ma']);

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Doublon Test',
                'email' => 'existant@hestim.ma',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_from_register_page(): void
    {
        $user = User::factory()->create(['email' => 'auth@hestim.ma']);

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_register_is_rate_limited_after_too_many_attempts(): void
    {
        RateLimiter::clear('127.0.0.1');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->from(route('register'))
                ->post(route('register.store'), [
                    'name' => 'Rate Limit',
                    'email' => "limit{$attempt}@hestim.ma",
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ])
                ->assertRedirect(route('dashboard'));
        }

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'Rate Limit Blocked',
                'email' => 'blocked@hestim.ma',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertStatus(429);
    }
}
