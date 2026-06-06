<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InternshipReview;
use App\Models\User;
use App\Repositories\Contracts\InternshipReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InternshipReviewService
{
    public function __construct(
        private readonly InternshipReviewRepositoryInterface $reviews,
        private readonly CompanyService $companies,
    ) {
    }

    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return $this->reviews->search($filters);
    }

    /**
     * @param  array{company_name: string, company_city?: string|null, company_sector?: string|null, filiere_id?: string|null, position?: string|null, description: string, rating: int, year_level?: int|null, year_done?: int|null, is_paid?: bool}  $data
     */
    public function create(User $author, array $data): InternshipReview
    {
        $company = $this->companies->findOrCreate(
            $data['company_name'],
            $data['company_city'] ?? null,
            $data['company_sector'] ?? null,
        );

        return $this->reviews->create([
            'user_id' => $author->getKey(),
            'company_id' => $company->getKey(),
            'filiere_id' => $data['filiere_id'] ?? $author->filiere_id,
            'position' => $data['position'] ?? null,
            'description' => $data['description'],
            'rating' => $data['rating'],
            'year_level' => $data['year_level'] ?? null,
            'year_done' => $data['year_done'] ?? null,
            'is_paid' => $data['is_paid'] ?? false,
        ]);
    }
}
