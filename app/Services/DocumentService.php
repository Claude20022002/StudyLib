<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\User;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    private const DISK = 'minio';

    public function __construct(
        private readonly DocumentRepositoryInterface $documents,
    ) {}

    public function listByModule(string $moduleId, ?DocumentType $type = null): LengthAwarePaginator
    {
        return $this->documents->listByModule($moduleId, $type);
    }

    /**
     * Stocke le fichier sur MinIO puis enregistre les métadonnées (statut : en attente).
     *
     * @param  array{module_id: string, type: string, title: string, description?: string|null, year_concern?: int|null}  $data
     */
    public function upload(User $author, UploadedFile $file, array $data): Document
    {
        $path = $file->store('documents', self::DISK);

        return $this->documents->create([
            'user_id' => $author->getKey(),
            'module_id' => $data['module_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'year_concern' => $data['year_concern'] ?? null,
            'status' => DocumentStatus::Pending->value,
        ]);
    }

    public function temporaryDownloadUrl(Document $document, int $minutes = 5): string
    {
        return Storage::disk(self::DISK)->temporaryUrl(
            $document->file_path,
            now()->addMinutes($minutes),
        );
    }

    public function delete(Document $document): void
    {
        $this->documents->delete($document);
    }
}
