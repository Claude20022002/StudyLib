<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Filiere;
use App\Models\Module;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ModuleService
{
    public function __construct(
        private readonly ModuleRepositoryInterface $modules,
    ) {}

    /** @return Collection<int, Module> */
    public function forFiliere(Filiere $filiere): Collection
    {
        return $this->modules->forFiliere($filiere->getKey());
    }
}
