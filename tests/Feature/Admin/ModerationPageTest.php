<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Livewire\Admin\ModerationIndex;
use App\Models\Document;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\FiliereSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ModerationPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FiliereSeeder::class);
    }

    public function test_guest_is_redirected_from_admin_moderation(): void
    {
        $this->get(route('admin.moderation.index'))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_admin_moderation(): void
    {
        $student = User::factory()->create(['email' => 'student@hestim.ma']);

        $this->actingAs($student)
            ->get(route('admin.moderation.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_moderation_page(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@hestim.ma']);

        $this->actingAs($admin)
            ->get(route('admin.moderation.index'))
            ->assertOk()
            ->assertSee('Modération', false)
            ->assertSeeLivewire('admin.moderation-index');
    }

    public function test_admin_sees_pending_documents_in_queue(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@hestim.ma']);
        $module = Module::factory()->create();
        Document::factory()->pending()->create([
            'module_id' => $module->id,
            'title' => 'TP Réseaux en attente',
        ]);

        Livewire::actingAs($admin)
            ->test(ModerationIndex::class)
            ->assertSee('TP Réseaux en attente')
            ->assertSee('Uploads en attente de validation');
    }

    public function test_admin_can_approve_pending_document(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@hestim.ma']);
        $module = Module::factory()->create();
        $document = Document::factory()->pending()->create([
            'module_id' => $module->id,
            'title' => 'Cours à valider',
            'type' => DocumentType::Cours,
        ]);

        Livewire::actingAs($admin)
            ->test(ModerationIndex::class)
            ->call('approve', $document->id)
            ->assertHasNoErrors();

        $this->assertSame(DocumentStatus::Approved, $document->fresh()->status);
    }

    public function test_admin_can_reject_pending_document_with_reason(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@hestim.ma']);
        $module = Module::factory()->create();
        $document = Document::factory()->pending()->create([
            'module_id' => $module->id,
            'title' => 'Document hors-sujet',
        ]);

        Livewire::actingAs($admin)
            ->test(ModerationIndex::class)
            ->call('openRejectModal', $document->id)
            ->set('rejectReason', 'Hors-sujet')
            ->call('submitReject')
            ->assertHasNoErrors();

        $this->assertSame(DocumentStatus::Rejected, $document->fresh()->status);
    }

    public function test_moderation_json_api_still_works_for_admin(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@hestim.ma']);
        $module = Module::factory()->create();
        Document::factory()->pending()->create(['module_id' => $module->id]);

        $this->actingAs($admin)
            ->getJson(route('admin.moderation.index'))
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_status_filter_shows_only_approved_documents(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@hestim.ma']);
        $module = Module::factory()->create();
        Document::factory()->create([
            'module_id' => $module->id,
            'title' => 'Doc validé visible',
            'status' => DocumentStatus::Approved,
        ]);
        Document::factory()->pending()->create([
            'module_id' => $module->id,
            'title' => 'Doc en attente caché',
        ]);

        Livewire::actingAs($admin)
            ->test(ModerationIndex::class)
            ->call('setStatusFilter', 'approved')
            ->assertSee('Doc validé visible')
            ->assertDontSee('Doc en attente caché');
    }
}
