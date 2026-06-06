<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;

class CompanyService
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
    ) {
    }

    public function findOrCreate(string $name, ?string $city, ?string $sector = null): Company
    {
        return $this->companies->firstOrCreateByNameCity($name, $city, $sector);
    }
}
