<?php

declare(strict_types=1);

namespace Tests\Feature\Document;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Livewire\Documents\Show;
use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\FiliereSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentShowPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FiliereSeeder::class);
    }

    public function test_guest_is_redirected_from_document_show(): void
    {
        $document = Document::factory()->create([
            'module_id' => Module::factory()->create()->id,
            'status' => DocumentStatus::Approved,
        ]);

        $this->get(route('documents.show', $document))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_approved_document(): void
    {
        $user = User::factory()->create(['email' => 'show@hestim.ma']);
        $module = Module::factory()->create(['name' => 'Bases de données']);
        $document = Document::factory()->create([
            'module_id' => $module->id,
            'title' => 'Modélisation relationnelle',
            'status' => DocumentStatus::Approved,
            'type' => DocumentType::Cours,
        ]);

        $this->actingAs($user)
            ->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Modélisation relationnelle', false)
            ->assertSee('Bases de données', false)
            ->assertSeeLivewire('documents.show');
    }

    public function test_show_page_displays_breadcrumb(): void
    {
        $user = User::factory()->create(['email' => 'crumb@hestim.ma']);
        $module = Module::factory()->create(['name' => 'Algorithmique']);
        $document = Document::factory()->create([
            'module_id' => $module->id,
            'title' => 'Tri rapide',
            'status' => DocumentStatus::Approved,
        ]);

        $this->actingAs($user)
            ->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Bibliothèque', false)
            ->assertSee('Algorithmique', false)
            ->assertSee('Tri rapide', false);
    }

    public function test_user_can_rate_document_from_show_page(): void
    {
        $user = User::factory()->create(['email' => 'rate@hestim.ma']);
        $document = Document::factory()->create([
            'module_id' => Module::factory()->create()->id,
            'status' => DocumentStatus::Approved,
            'type' => DocumentType::Cours,
            'avg_rating' => 0,
            'ratings_count' => 0,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class, ['document' => $document])
            ->call('openRateModal')
            ->call('setRateScore', 5)
            ->call('submitRating')
            ->assertHasNoErrors()
            ->assertSet('userRatingScore', 5);

        $document->refresh();

        $this->assertSame(5.0, $document->avg_rating);
        $this->assertSame(1, $document->ratings_count);
    }

    public function test_similar_documents_appear_on_show_page(): void
    {
        $user = User::factory()->create(['email' => 'similar@hestim.ma']);
        $module = Module::factory()->create();
        $main = Document::factory()->create([
            'module_id' => $module->id,
            'title' => 'Document principal',
            'status' => DocumentStatus::Approved,
        ]);
        Document::factory()->create([
            'module_id' => $module->id,
            'title' => 'Document similaire',
            'status' => DocumentStatus::Approved,
            'avg_rating' => 4.5,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class, ['document' => $main])
            ->assertSee('Document similaire')
            ->assertSee('Documents similaires');
    }

    public function test_owner_can_view_pending_document(): void
    {
        $user = User::factory()->create(['email' => 'owner@hestim.ma']);
        $document = Document::factory()->pending()->create([
            'user_id' => $user->id,
            'module_id' => Module::factory()->create()->id,
            'title' => 'Mon brouillon visible',
        ]);

        $this->actingAs($user)
            ->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Mon brouillon visible', false)
            ->assertSee('En modération', false);
    }

    public function test_recommended_badge_shows_for_same_filiere(): void
    {
        $filiere = Filiere::query()->where('code', 'GI')->firstOrFail();
        $user = User::factory()->create([
            'email' => 'rec@hestim.ma',
            'filiere_id' => $filiere->id,
            'year_level' => 3,
        ]);
        $module = Module::factory()->create(['filiere_id' => $filiere->id, 'semester' => 5]);
        $document = Document::factory()->create([
            'module_id' => $module->id,
            'status' => DocumentStatus::Approved,
        ]);

        Livewire::actingAs($user)
            ->test(Show::class, ['document' => $document])
            ->assertSee('Recommandé pour votre niveau');
    }
}
