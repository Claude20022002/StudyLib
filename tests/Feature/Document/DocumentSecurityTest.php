<?php

declare(strict_types=1);

namespace Tests\Feature\Document;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_json_does_not_expose_file_path(): void
    {
        $user = User::factory()->create(['email' => 'viewer@hestim.ma']);
        $module = Module::factory()->create();
        $document = Document::factory()->create([
            'module_id' => $module->id,
            'user_id' => $user->id,
            'status' => DocumentStatus::Approved,
            'file_path' => 'documents/secret-path/file.pdf',
        ]);

        $this->actingAs($user)
            ->getJson(route('documents.show', $document))
            ->assertOk()
            ->assertJsonMissing(['file_path' => 'documents/secret-path/file.pdf'])
            ->assertJsonPath('data.title', $document->title);
    }

    public function test_student_cannot_view_pending_document_they_do_not_own(): void
    {
        $owner = User::factory()->create(['email' => 'owner@hestim.ma']);
        $other = User::factory()->create(['email' => 'other@hestim.ma']);
        $module = Module::factory()->create();
        $document = Document::factory()->create([
            'module_id' => $module->id,
            'user_id' => $owner->id,
            'status' => DocumentStatus::Pending,
        ]);

        $this->actingAs($other)
            ->getJson(route('documents.show', $document))
            ->assertForbidden();
    }

    public function test_student_cannot_rate_pending_document(): void
    {
        $owner = User::factory()->create(['email' => 'owner2@hestim.ma']);
        $other = User::factory()->create(['email' => 'other2@hestim.ma']);
        $module = Module::factory()->create();
        $document = Document::factory()->create([
            'module_id' => $module->id,
            'user_id' => $owner->id,
            'status' => DocumentStatus::Pending,
            'type' => DocumentType::Cours,
        ]);

        $this->actingAs($other)
            ->postJson(route('documents.ratings.store', $document), ['score' => 5])
            ->assertForbidden();
    }

    public function test_student_can_rate_approved_document(): void
    {
        $user = User::factory()->create(['email' => 'rater@hestim.ma']);
        $module = Module::factory()->create();
        $document = Document::factory()->create([
            'module_id' => $module->id,
            'status' => DocumentStatus::Approved,
            'type' => DocumentType::Cours,
        ]);

        $this->actingAs($user)
            ->postJson(route('documents.ratings.store', $document), ['score' => 4])
            ->assertCreated()
            ->assertJsonPath('score', 4);
    }

    public function test_download_requires_view_permission(): void
    {
        $owner = User::factory()->create(['email' => 'owner3@hestim.ma']);
        $other = User::factory()->create(['email' => 'other3@hestim.ma']);
        $module = Module::factory()->create();
        $document = Document::factory()->create([
            'module_id' => $module->id,
            'user_id' => $owner->id,
            'status' => DocumentStatus::Pending,
        ]);

        $this->actingAs($other)
            ->post(route('documents.download', $document))
            ->assertForbidden();
    }
}
