<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends BaseRepository<Company>
 */
class CompanyRepository extends BaseRepository implements CompanyRepositoryInterface
{
    public function __construct(Company $model)
    {
        parent::__construct($model);
    }

    public function firstOrCreateByNameCity(string $name, ?string $city, ?string $sector = null): Company
    {
        return $this->model->newQuery()->firstOrCreate(
            ['name' => $name, 'city' => $city],
            ['sector' => $sector],
        );
    }

    public function browse(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = $this->browseQuery($filters);

        $sort = $filters['sort'] ?? 'rating';

        if ($sort === 'reviews') {
            $query->orderByDesc('reviews_count');
        } elseif ($sort === 'recent') {
            $query->orderByDesc('latest_review_at');
        } else {
            $query->orderByDesc('avg_rating');
        }

        return $query->paginate($perPage);
    }

    public function distinctCities(): array
    {
        return $this->model->newQuery()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->whereHas('internshipReviews')
            ->orderBy('city')
            ->distinct()
            ->pluck('city')
            ->all();
    }

    public function distinctSectors(): array
    {
        return $this->model->newQuery()
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->whereHas('internshipReviews')
            ->orderBy('sector')
            ->distinct()
            ->pluck('sector')
            ->all();
    }

    public function findWithReviews(string $companyId): ?Company
    {
        return $this->model->newQuery()
            ->with([
                'internshipReviews' => fn ($query) => $query
                    ->with(['user', 'filiere'])
                    ->latest(),
            ])
            ->find($companyId);
    }

    /** @return Builder<Company> */
    private function browseQuery(array $filters): Builder
    {
        $query = $this->model->newQuery()
            ->select('companies.*')
            ->selectRaw('ROUND(AVG(internship_reviews.rating), 1) as avg_rating')
            ->selectRaw('COUNT(internship_reviews.id) as reviews_count')
            ->selectRaw('MAX(internship_reviews.created_at) as latest_review_at')
            ->join('internship_reviews', function ($join): void {
                $join->on('internship_reviews.company_id', '=', 'companies.id')
                    ->whereNull('internship_reviews.deleted_at');
            })
            ->groupBy('companies.id');

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('companies.name', 'like', $term)
                    ->orWhere('companies.city', 'like', $term)
                    ->orWhere('companies.sector', 'like', $term);
            });
        }

        if (! empty($filters['city'])) {
            $query->where('companies.city', $filters['city']);
        }

        if (! empty($filters['sector'])) {
            $query->where('companies.sector', $filters['sector']);
        }

        if (! empty($filters['filiere_id'])) {
            $query->where('internship_reviews.filiere_id', $filters['filiere_id']);
        }

        if (! empty($filters['year_level'])) {
            $query->where('internship_reviews.year_level', (int) $filters['year_level']);
        }

        if (! empty($filters['year_done'])) {
            $query->where('internship_reviews.year_done', (int) $filters['year_done']);
        }

        if (! empty($filters['min_rating'])) {
            $query->havingRaw('AVG(internship_reviews.rating) >= ?', [(float) $filters['min_rating']]);
        }

        return $query;
    }
}
