<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\DocumentRating;

/**
 * @extends RepositoryInterface<DocumentRating>
 */
interface DocumentRatingRepositoryInterface extends RepositoryInterface
{
    public function findForUserAndDocument(string $userId, string $documentId): ?DocumentRating;
}
