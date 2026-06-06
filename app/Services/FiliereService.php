<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\FiliereRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FiliereService
{
    public function __construct(
        private readonly FiliereRepositoryInterface $filieres,
    ) {
    }

    /** @return Collection<int, \App\Models\Filiere> */
    public function all(): Collection
    {
        return $this->filieres->all();
    }
}
