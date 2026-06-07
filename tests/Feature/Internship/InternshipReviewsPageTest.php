<?php

declare(strict_types=1);

namespace Tests\Feature\Internship;

use App\Livewire\InternshipReviews\Index;
use App\Models\Company;
use App\Models\Filiere;
use App\Models\InternshipReview;
use App\Models\User;
use Database\Seeders\FiliereSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InternshipReviewsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FiliereSeeder::class);
    }

    public function test_guest_is_redirected_from_internship_reviews_page(): void
    {
        $this->get(route('internship-reviews.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_internship_reviews_page(): void
    {
        $user = User::factory()->create(['email' => 'stages@hestim.ma']);

        $this->actingAs($user)
            ->get(route('internship-reviews.index'))
            ->assertOk()
            ->assertSee('Partager mon retour de stage', false)
            ->assertSeeLivewire('internship-reviews.index');
    }

    public function test_stages_page_lists_companies_with_reviews(): void
    {
        $user = User::factory()->create(['email' => 'list-stages@hestim.ma']);
        $company = Company::factory()->create([
            'name' => 'Atlas Software',
            'city' => 'Casablanca',
            'sector' => 'IT',
        ]);
        InternshipReview::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'filiere_id' => Filiere::query()->first()?->id,
            'rating' => 5,
            'description' => 'Excellent encadrement et missions concrètes.',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee('Atlas Software')
            ->assertSee('1 avis');
    }

    public function test_stages_search_filters_companies(): void
    {
        $user = User::factory()->create(['email' => 'search-stages@hestim.ma']);
        $visible = Company::factory()->create(['name' => 'NovaTel', 'city' => 'Rabat']);
        $hidden = Company::factory()->create(['name' => 'BuildPro', 'city' => 'Marrakech']);

        InternshipReview::factory()->create([
            'user_id' => $user->id,
            'company_id' => $visible->id,
            'rating' => 4,
        ]);
        InternshipReview::factory()->create([
            'user_id' => $user->id,
            'company_id' => $hidden->id,
            'rating' => 3,
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('search', 'NovaTel')
            ->assertSee('NovaTel')
            ->assertDontSee('BuildPro');
    }

    public function test_user_can_open_company_detail_drawer(): void
    {
        $user = User::factory()->create(['email' => 'detail-stages@hestim.ma']);
        $company = Company::factory()->create(['name' => 'DataMind']);
        InternshipReview::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'position' => 'Stagiaire data',
            'description' => 'Pipeline de données et dashboards.',
            'rating' => 5,
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('openDetail', $company->id)
            ->assertSet('detailOpen', true)
            ->assertSee('Fiche entreprise')
            ->assertSee('Pipeline de données et dashboards.');
    }

    public function test_user_can_submit_internship_review(): void
    {
        $user = User::factory()->create(['email' => 'submit-stages@hestim.ma']);
        $filiere = Filiere::query()->first();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('shareCompanyName', 'CapFinance')
            ->set('shareCompanyCity', 'Casablanca')
            ->set('shareCompanySector', 'Finance')
            ->set('shareFiliereId', $filiere?->id ?? '')
            ->set('sharePosition', 'Analyste junior')
            ->set('shareDescription', 'Reporting Excel et automatisation.')
            ->set('shareRating', 4)
            ->set('shareYearLevel', '3')
            ->set('shareYearDone', '2024')
            ->call('submitShare')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('internship_reviews', [
            'user_id' => $user->id,
            'position' => 'Analyste junior',
            'rating' => 4,
        ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'CapFinance',
            'city' => 'Casablanca',
        ]);
    }

    public function test_web_store_redirects_with_success_message(): void
    {
        $user = User::factory()->create(['email' => 'post-stages@hestim.ma']);
        $filiere = Filiere::query()->first();

        $this->actingAs($user)
            ->post(route('internship-reviews.store'), [
                'company_name' => 'VoltEdge',
                'company_city' => 'Tanger',
                'company_sector' => 'Industrie',
                'filiere_id' => $filiere?->id,
                'position' => 'Technicien',
                'description' => 'Tests électroniques.',
                'rating' => 4,
                'year_level' => 3,
                'year_done' => 2023,
                'is_paid' => true,
            ])
            ->assertRedirect(route('internship-reviews.index'))
            ->assertSessionHas('success');
    }
}
