<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\DocumentRating;
use App\Models\User;
use App\Repositories\Contracts\DocumentRatingRepositoryInterface;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    private const DISK = 'minio';

    public function __construct(
        private readonly DocumentRepositoryInterface $documents,
        private readonly DocumentRatingRepositoryInterface $ratings,
    ) {}

    public function listByModule(string $moduleId, ?DocumentType $type = null): LengthAwarePaginator
    {
        return $this->documents->listByModule($moduleId, $type);
    }

    /**
     * @param  array{
     *     q?: string,
     *     filiere_id?: string,
     *     semester?: int,
     *     module_id?: string,
     *     year_concern?: int,
     *     types?: list<string>,
     *     min_rating?: float,
     *     sort?: string,
     *     mine?: bool,
     *     user_id?: string,
     * }  $filters
     * @return LengthAwarePaginator<int, Document>
     */
    public function browse(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->documents->browse($filters, $perPage);
    }

    /**
     * @param  array{
     *     q?: string,
     *     filiere_id?: string,
     *     semester?: int,
     *     module_id?: string,
     *     year_concern?: int,
     *     min_rating?: float,
     *     mine?: bool,
     *     user_id?: string,
     * }  $filters
     * @return array<string, int>
     */
    public function typeCountsForBrowse(array $filters): array
    {
        return $this->documents->countByTypeForBrowse($filters);
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

    /**
     * @return array{
     *     document: Document,
     *     similarDocuments: Collection<int, Document>,
     *     examDocuments: Collection<int, Document>,
     *     authorDocumentCount: int,
     *     userRating: DocumentRating|null,
     * }
     */
    public function showPageData(Document $document, ?User $viewer): array
    {
        $document->load(['author', 'module.filiere']);

        $userRating = null;

        if ($viewer !== null) {
            $userRating = $this->ratings->findForUserAndDocument(
                $viewer->getKey(),
                $document->getKey(),
            );
        }

        return [
            'document' => $document,
            'similarDocuments' => $this->documents->similarInModule($document),
            'examDocuments' => $this->documents->examsInModule($document),
            'authorDocumentCount' => $this->documents->countApprovedByAuthor($document->user_id),
            'userRating' => $userRating,
        ];
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
        if (filled($document->file_path)) {
            Storage::disk(self::DISK)->delete($document->file_path);
        }

        $this->documents->delete($document);
    }
}
