<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\InternshipReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<InternshipReview>
 */
interface InternshipReviewRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, InternshipReview>
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator;

    public function countForFiliere(string $filiereId): int;

    public function totalDistinctCompanies(): int;
}
