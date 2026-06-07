<?php

declare(strict_types=1);

namespace Tests\Feature\Project;

use App\Enums\IdeaSource;
use App\Enums\StudyLevel;
use App\Livewire\ProjectIdeas\Index;
use App\Models\Filiere;
use App\Models\ProjectIdea;
use App\Models\User;
use Database\Seeders\FiliereSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectIdeasPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FiliereSeeder::class);
    }

    public function test_guest_is_redirected_from_project_ideas_page(): void
    {
        $this->get(route('project-ideas.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_project_ideas_page(): void
    {
        $user = User::factory()->create(['email' => 'projets@hestim.ma']);

        $this->actingAs($user)
            ->get(route('project-ideas.index'))
            ->assertOk()
            ->assertSee('Trouvez un projet pertinent', false)
            ->assertSeeLivewire('project-ideas.index');
    }

    public function test_project_ideas_page_lists_published_ideas(): void
    {
        $user = User::factory()->create(['email' => 'list-projets@hestim.ma']);
        $filiere = Filiere::query()->first();

        ProjectIdea::factory()->create([
            'user_id' => $user->id,
            'filiere_id' => $filiere?->id,
            'title' => 'Plateforme d\'entraide entre étudiants',
            'level' => StudyLevel::L3->value,
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee('Plateforme d\'entraide entre étudiants', false);
    }

    public function test_project_ideas_search_filters_by_title(): void
    {
        $user = User::factory()->create(['email' => 'search-projets@hestim.ma']);

        ProjectIdea::factory()->create([
            'user_id' => $user->id,
            'title' => 'Détecteur de plagiat de code',
        ]);
        ProjectIdea::factory()->create([
            'user_id' => $user->id,
            'title' => 'App mobile de suivi de chantier',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('search', 'plagiat')
            ->assertSee('Détecteur de plagiat de code')
            ->assertDontSee('App mobile de suivi de chantier');
    }

    public function test_user_can_open_project_detail_drawer(): void
    {
        $user = User::factory()->create(['email' => 'detail-projets@hestim.ma']);
        $idea = ProjectIdea::factory()->create([
            'user_id' => $user->id,
            'title' => 'Portfolio personnel animé',
            'description' => 'Site portfolio responsive avec animations soignées.',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('openDetail', $idea->id)
            ->assertSet('detailOpen', true)
            ->assertSee('Fiche projet')
            ->assertSee('Site portfolio responsive avec animations soignées.');
    }

    public function test_user_can_propose_project_idea(): void
    {
        $user = User::factory()->create(['email' => 'propose-projets@hestim.ma']);
        $filiere = Filiere::query()->first();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('proposeTitle', 'Optimisation de tournées')
            ->set('proposeDescription', 'Algorithme VRP pour une flotte de véhicules.')
            ->set('proposeLevel', StudyLevel::M2->value)
            ->set('proposeFiliereId', $filiere?->id ?? '')
            ->call('submitPropose')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('project_ideas', [
            'user_id' => $user->id,
            'title' => 'Optimisation de tournées',
            'source' => IdeaSource::Student->value,
        ]);
    }

    public function test_ai_generation_creates_project_ideas(): void
    {
        config([
            'services.claude.url' => 'https://api.anthropic.test/v1/messages',
            'services.claude.key' => 'test-key',
        ]);

        Http::fake([
            'https://api.anthropic.test/v1/messages' => Http::response([
                'model' => 'claude-test',
                'content' => [[
                    'type' => 'text',
                    'text' => json_encode([
                        ['title' => 'Projet IA Alpha', 'description' => 'Description alpha.'],
                        ['title' => 'Projet IA Beta', 'description' => 'Description beta.'],
                        ['title' => 'Projet IA Gamma', 'description' => 'Description gamma.'],
                    ]),
                ]],
                'usage' => ['output_tokens' => 120],
            ]),
        ]);

        $user = User::factory()->create(['email' => 'ai-projets@hestim.ma']);
        $filiere = Filiere::query()->first();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('aiFiliereId', $filiere?->id)
            ->set('aiLevel', StudyLevel::L3->value)
            ->set('aiInterests', 'développement web')
            ->call('generateAiIdeas')
            ->assertHasNoErrors()
            ->assertSee('Projet IA Alpha');

        $this->assertDatabaseHas('project_ideas', [
            'title' => 'Projet IA Alpha',
            'source' => IdeaSource::Ai->value,
        ]);
    }

    public function test_web_store_redirects_with_success_message(): void
    {
        $user = User::factory()->create(['email' => 'post-projets@hestim.ma']);
        $filiere = Filiere::query()->first();

        $this->actingAs($user)
            ->post(route('project-ideas.store'), [
                'title' => 'Tableau de bord IoT',
                'description' => 'Capteurs connectés et dashboard temps réel.',
                'level' => StudyLevel::M1->value,
                'filiere_id' => $filiere?->id,
                'repo_url' => 'https://github.com/example/iot-dashboard',
            ])
            ->assertRedirect(route('project-ideas.index'))
            ->assertSessionHas('success');
    }
}
