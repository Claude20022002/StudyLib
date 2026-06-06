<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\DocumentRating;
use App\Repositories\Contracts\DocumentRatingRepositoryInterface;

/**
 * @extends BaseRepository<DocumentRating>
 */
class DocumentRatingRepository extends BaseRepository implements DocumentRatingRepositoryInterface
{
    public function __construct(DocumentRating $model)
    {
        parent::__construct($model);
    }

    public function findForUserAndDocument(string $userId, string $documentId): ?DocumentRating
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('document_id', $documentId)
            ->first();
    }
}
