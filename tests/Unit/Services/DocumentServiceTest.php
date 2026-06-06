<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Module;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('minio');
    }

    public function test_delete_removes_file_from_minio(): void
    {
        Storage::disk('minio')->put('documents/test.pdf', 'content');

        $document = Document::factory()->create([
            'module_id' => Module::factory()->create()->id,
            'user_id' => User::factory()->create()->id,
            'file_path' => 'documents/test.pdf',
            'status' => DocumentStatus::Approved,
            'type' => DocumentType::Cours,
        ]);

        app(DocumentService::class)->delete($document);

        Storage::disk('minio')->assertMissing('documents/test.pdf');
        $this->assertSoftDeleted($document);
    }

    public function test_upload_stores_file_on_minio(): void
    {
        $user = User::factory()->create();
        $module = Module::factory()->create();
        $file = UploadedFile::fake()->create('cours.pdf', 100, 'application/pdf');

        $document = app(DocumentService::class)->upload($user, $file, [
            'module_id' => $module->id,
            'type' => DocumentType::Cours->value,
            'title' => 'Introduction',
        ]);

        Storage::disk('minio')->assertExists($document->file_path);
        $this->assertSame(DocumentStatus::Pending, $document->status);
    }
}
