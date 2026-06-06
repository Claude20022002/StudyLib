<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Filiere;

/**
 * @extends RepositoryInterface<Filiere>
 */
interface FiliereRepositoryInterface extends RepositoryInterface
{
    public function findByCode(string $code): ?Filiere;
}
