<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Company>
 */
interface CompanyRepositoryInterface extends RepositoryInterface
{
    public function firstOrCreateByNameCity(string $name, ?string $city, ?string $sector = null): Company;

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Company>
     */
    public function browse(array $filters, int $perPage = 12): LengthAwarePaginator;

    /** @return list<string> */
    public function distinctCities(): array;

    /** @return list<string> */
    public function distinctSectors(): array;

    public function findWithReviews(string $companyId): ?Company;
}
