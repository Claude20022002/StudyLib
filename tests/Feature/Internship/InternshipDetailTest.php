<?php

declare(strict_types=1);

namespace Tests\Feature\Internship;

use App\Livewire\InternshipReviews\Index;
use App\Models\Company;
use App\Models\InternshipReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Fiche détaillée d'une entreprise d'accueil : elle doit s'ouvrir aussi pour un retour ajouté par
 * l'administration (sans auteur), comme les retours de démonstration.
 */
class InternshipDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_detail_opens_with_reviews_with_and_without_author(): void
    {
        $lecteur = User::factory()->create(['email' => 'stages@hestim.ma']);
        $entreprise = Company::factory()->create(['name' => 'HESTIM FabLab']);
        InternshipReview::factory()->create(['company_id' => $entreprise->id, 'user_id' => null, 'consent_at' => now(), 'position' => 'Stage en robotique']);
        InternshipReview::factory()->create(['company_id' => $entreprise->id, 'consent_at' => now(), 'position' => 'Stage de développement']);

        $this->actingAs($lecteur);
        Livewire::test(Index::class)
            ->call('openDetail', $entreprise->id)
            ->assertOk()
            ->assertSee('Stage en robotique')
            ->assertSee('Stage de développement');
    }
}
