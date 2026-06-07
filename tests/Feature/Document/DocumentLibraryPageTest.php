<?php

declare(strict_types=1);

namespace Tests\Feature\Document;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Livewire\Documents\Index;
use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use App\Services\DocumentService;
use Database\Seeders\FiliereSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DocumentLibraryPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FiliereSeeder::class);
        Storage::fake('minio');
    }

    public function test_guest_is_redirected_from_library(): void
    {
        $this->get(route('documents.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_library_page(): void
    {
        $user = User::factory()->create(['email' => 'biblio@hestim.ma']);

        $this->actingAs($user)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee('Trouvez n\'importe quel document', false)
            ->assertSeeLivewire('documents.index');
    }

    public function test_library_lists_approved_documents(): void
    {
        $user = User::factory()->create(['email' => 'list@hestim.ma']);
        $module = Module::factory()->create();
        $visible = Document::factory()->create([
            'module_id' => $module->id,
            'title' => 'Annales SQL avancé',
            'status' => DocumentStatus::Approved,
            'type' => DocumentType::Examen,
        ]);
        Document::factory()->pending()->create([
            'module_id' => $module->id,
            'title' => 'Brouillon caché',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee('Annales SQL avancé')
            ->assertDontSee('Brouillon caché');
    }

    public function test_library_search_filters_by_title(): void
    {
        $user = User::factory()->create(['email' => 'search@hestim.ma']);
        $module = Module::factory()->create();
        Document::factory()->create([
            'module_id' => $module->id,
            'title' => 'Cours Algorithmique',
            'status' => DocumentStatus::Approved,
        ]);
        Document::factory()->create([
            'module_id' => $module->id,
            'title' => 'TP Réseaux',
            'status' => DocumentStatus::Approved,
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('search', 'Algorithmique')
            ->assertSee('Cours Algorithmique')
            ->assertDontSee('TP Réseaux');
    }

    public function test_user_can_upload_document_from_library(): void
    {
        $user = User::factory()->create(['email' => 'upload@hestim.ma']);
        $module = Module::factory()->create();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('uploadFile', UploadedFile::fake()->create('fiche.pdf', 500, 'application/pdf'))
            ->set('uploadTitle', 'Fiche de révision')
            ->set('uploadModuleId', $module->id)
            ->set('uploadType', DocumentType::Cours->value)
            ->set('uploadYear', '2025')
            ->set('rightsAcknowledged', true)
            ->call('submitUpload')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('documents', [
            'title' => 'Fiche de révision',
            'user_id' => $user->id,
            'module_id' => $module->id,
            'status' => DocumentStatus::Pending->value,
        ]);
    }

    public function test_mine_mode_shows_user_pending_documents(): void
    {
        $user = User::factory()->create(['email' => 'mine@hestim.ma']);
        $module = Module::factory()->create();
        Document::factory()->pending()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'title' => 'Mon dépôt en attente',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('mine', true)
            ->assertSee('Mon dépôt en attente')
            ->assertSee('En modération');
    }

    public function test_document_service_browse_respects_filiere_filter(): void
    {
        $filiereA = Filiere::query()->where('code', 'GI')->firstOrFail();
        $filiereB = Filiere::query()->where('code', 'GC')->firstOrFail();
        $moduleA = Module::factory()->create(['filiere_id' => $filiereA->id]);
        $moduleB = Module::factory()->create(['filiere_id' => $filiereB->id]);

        Document::factory()->create([
            'module_id' => $moduleA->id,
            'title' => 'Doc GI',
            'status' => DocumentStatus::Approved,
        ]);
        Document::factory()->create([
            'module_id' => $moduleB->id,
            'title' => 'Doc GC',
            'status' => DocumentStatus::Approved,
        ]);

        $results = app(DocumentService::class)->browse([
            'filiere_id' => $filiereA->id,
        ]);

        $this->assertCount(1, $results->items());
        $this->assertSame('Doc GI', $results->items()[0]->title);
    }
}
