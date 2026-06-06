<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Filiere;
use App\Repositories\Contracts\FiliereRepositoryInterface;

/**
 * @extends BaseRepository<Filiere>
 */
class FiliereRepository extends BaseRepository implements FiliereRepositoryInterface
{
    public function __construct(Filiere $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code): ?Filiere
    {
        return $this->model->newQuery()->where('code', $code)->first();
    }
}
