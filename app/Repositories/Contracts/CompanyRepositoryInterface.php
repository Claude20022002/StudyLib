<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Company;

/**
 * @extends RepositoryInterface<Company>
 */
interface CompanyRepositoryInterface extends RepositoryInterface
{
    public function firstOrCreateByNameCity(string $name, ?string $city, ?string $sector = null): Company;
}
