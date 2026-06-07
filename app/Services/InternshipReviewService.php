<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\InternshipReview;
use App\Models\User;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use App\Repositories\Contracts\InternshipReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InternshipReviewService
{
    public function __construct(
        private readonly InternshipReviewRepositoryInterface $reviews,
        private readonly CompanyRepositoryInterface $companies,
        private readonly CompanyService $companyService,
    ) {}

    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return $this->reviews->search($filters);
    }

    /** @param array<string, mixed> $filters */
    public function browse(array $filters): LengthAwarePaginator
    {
        return $this->companies->browse($filters);
    }

    public function totalCompanies(): int
    {
        return $this->reviews->totalDistinctCompanies();
    }

    /**
     * @return array{
     *     cities: list<string>,
     *     sectors: list<string>,
     *     years: list<int>,
     * }
     */
    public function filterOptions(): array
    {
        $currentYear = (int) now()->format('Y');
        $years = [];

        for ($year = $currentYear; $year >= $currentYear - 5; $year--) {
            $years[] = $year;
        }

        return [
            'cities' => $this->companies->distinctCities(),
            'sectors' => $this->companies->distinctSectors(),
            'years' => $years,
        ];
    }

    /**
     * @return array{
     *     company: Company,
     *     avg_rating: float,
     *     reviews_count: int,
     *     distribution: array<int, int>,
     * }|null
     */
    public function companyDetail(string $companyId): ?array
    {
        $company = $this->companies->findWithReviews($companyId);

        if ($company === null) {
            return null;
        }

        $reviews = $company->internshipReviews;

        return [
            'company' => $company,
            'avg_rating' => round((float) $reviews->avg('rating'), 1),
            'reviews_count' => $reviews->count(),
            'distribution' => $this->ratingDistribution($reviews),
        ];
    }

    public function maskedAuthorName(InternshipReview $review): string
    {
        $name = $review->user?->name;

        if ($name === null || trim($name) === '') {
            return 'Anonyme';
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? '';
        $lastInitial = isset($parts[1]) ? mb_substr($parts[1], 0, 1).'.' : '';

        return trim($first.' '.$lastInitial);
    }

    public function companyAccentColor(string $name): string
    {
        $palette = [
            '#1D4ED8',
            '#1F8A5B',
            '#92400E',
            '#6D28D9',
            '#0EA5E9',
            '#334155',
            '#BE123C',
            '#B45309',
        ];

        $index = abs(crc32(mb_strtolower(trim($name)))) % count($palette);

        return $palette[$index];
    }

    public function companyInitials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(
            fn (string $part): string => mb_substr($part, 0, 1),
        )->implode(''));
    }

    /**
     * @param  array{company_name: string, company_city?: string|null, company_sector?: string|null, filiere_id?: string|null, position?: string|null, description: string, rating: int, year_level?: int|null, year_done?: int|null, is_paid?: bool}  $data
     */
    public function create(User $author, array $data): InternshipReview
    {
        $company = $this->companyService->findOrCreate(
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

    /**
     * @param  Collection<int, InternshipReview>  $reviews
     * @return array<int, int>
     */
    private function ratingDistribution(Collection $reviews): array
    {
        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

        foreach ($reviews as $review) {
            $rating = (int) $review->rating;

            if ($rating >= 1 && $rating <= 5) {
                $distribution[$rating]++;
            }
        }

        return $distribution;
    }
}
