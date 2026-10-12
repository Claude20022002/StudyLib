<?php

declare(strict_types=1);

namespace Tests\Feature\Landing;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_is_accessible_to_guests(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Tous vos cours, examens, stages et projets')
            ->assertSee('Réservé à la communauté HESTIM')
            ->assertSee('Se connecter avec mon email @hestim.ma');
    }

    public function test_authenticated_users_land_on_their_dashboard(): void
    {
        $user = User::factory()->create(['email' => 'etudiant@hestim.ma']);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_landing_page_includes_feature_and_trust_sections(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Pourquoi StudyLib', false)
            ->assertSee('Ressources centralisées', false)
            ->assertSee('Confiance &amp; sécurité', false)
            ->assertSee('Accès vérifié uniquement', false);
    }
}
