<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\InternshipReview;
use App\Repositories\Contracts\InternshipReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends BaseRepository<InternshipReview>
 */
class InternshipReviewRepository extends BaseRepository implements InternshipReviewRepositoryInterface
{
    public function __construct(InternshipReview $model)
    {
        parent::__construct($model);
    }

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['company', 'filiere'])
            ->when($filters['company_id'] ?? null, fn ($q, $v) => $q->where('company_id', $v))
            ->when($filters['filiere_id'] ?? null, fn ($q, $v) => $q->where('filiere_id', $v))
            ->when($filters['year_done'] ?? null, fn ($q, $v) => $q->where('year_done', $v))
            ->when($filters['min_rating'] ?? null, fn ($q, $v) => $q->where('rating', '>=', $v))
            ->latest()
            ->paginate($perPage);
    }
}
