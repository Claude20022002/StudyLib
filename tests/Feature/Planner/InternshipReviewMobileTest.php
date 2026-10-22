<?php

declare(strict_types=1);

namespace Tests\Feature\Planner;

use App\Models\Company;
use App\Models\Filiere;
use App\Models\InternshipReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\IssuesPlannerTokens;
use Tests\TestCase;

/**
 * Écran « Stages » de l'application : tous les retours publiés (filtrables), les miens (publiés ou
 * non), les valeurs des filtres. Jeton Planner en Bearer, comme le reste de l'API mobile.
 */
class InternshipReviewMobileTest extends TestCase
{
    use IssuesPlannerTokens, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakePlanner();
    }

    private function en(string $token): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    }

    private function retour(array $champs = [], array $entreprise = []): InternshipReview
    {
        // Les champs donnés l'emportent (consent_at => null : retour non publié)
        return InternshipReview::factory()->create($champs + ['consent_at' => now(), 'company_id' => Company::factory()->create($entreprise)->id]);
    }

    public function test_all_published_reviews_are_listed_and_filtered(): void
    {
        $iiia = Filiere::factory()->create(['code' => 'IIIA']);
        $this->retour(['position' => 'Développeur web', 'year_done' => 2025, 'is_paid' => true, 'filiere_id' => $iiia->id], ['name' => 'OCP', 'city' => 'Casablanca', 'sector' => 'Industrie']);
        $this->retour(['position' => 'Analyste de données', 'year_done' => 2024, 'is_paid' => false], ['name' => 'Attijariwafa', 'city' => 'Rabat', 'sector' => 'Finance']);
        $this->retour(['consent_at' => null, 'position' => 'Stage caché'], ['name' => 'Secret', 'city' => 'Casablanca']);
        $etudiant = $this->plannerToken();

        $tous = $this->en($etudiant)->getJson('/api/internship-reviews/mobile')->assertOk();
        $this->assertSame(2, $tous->json('total'));
        $this->assertNotContains('Stage caché', array_column($tous->json('data'), 'position'));

        $cas = fn (string $requete) => array_column($this->en($etudiant)->getJson('/api/internship-reviews/mobile?'.$requete)->assertOk()->json('data'), 'company');
        $this->assertSame(['OCP'], $cas('city=Casablanca'));
        $this->assertSame(['Attijariwafa'], $cas('sector=Finance'));
        $this->assertSame(['OCP'], $cas('year_done=2025'));
        $this->assertSame(['Attijariwafa'], $cas('is_paid=0'));
        $this->assertSame(['OCP'], $cas('filiere=IIIA'));
        $this->assertSame(['Attijariwafa'], $cas('q=donn'));
        $this->assertSame(['OCP'], $cas('q=ocp'));
        // % et _ restent des caractères ordinaires
        $this->assertSame([], $cas('q=%25'));
        $this->en($etudiant)->getJson('/api/internship-reviews/mobile?year_done=1500')->assertUnprocessable();
    }

    public function test_mine_lists_my_reviews_even_unpublished(): void
    {
        $this->en($this->plannerToken())->getJson('/api/internship-reviews/miens')->assertOk();
        $moi = User::query()->where('planner_id', '42')->firstOrFail();
        $this->retour(['user_id' => $moi->id, 'position' => 'Mon stage publié']);
        $this->retour(['user_id' => $moi->id, 'position' => 'Mon stage en attente', 'consent_at' => null]);
        $this->retour(['position' => 'Le stage d’un autre']);

        $miens = $this->en($this->plannerToken())->getJson('/api/internship-reviews/miens')->assertOk()->json('data');
        $this->assertEqualsCanonicalizing(['Mon stage publié', 'Mon stage en attente'], array_column($miens, 'position'));
        $this->assertEqualsCanonicalizing([true, false], array_column($miens, 'publie'));
    }

    public function test_filter_values_and_authentication(): void
    {
        $this->retour([], ['city' => 'Tanger', 'sector' => 'IT']);
        $this->en($this->plannerToken())->getJson('/api/internship-reviews/filtres')->assertOk()
            ->assertJson(fn ($json) => $json->where('cities', ['Tanger'])->where('sectors', ['IT'])->has('years')->etc());
        $this->app['auth']->forgetGuards();
        $this->withoutToken()->getJson('/api/internship-reviews/mobile')->assertUnauthorized();
        $this->withoutToken()->getJson('/api/internship-reviews/miens')->assertUnauthorized();
    }
}
