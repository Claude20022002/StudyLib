<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyRepositoryInterface;

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
}
