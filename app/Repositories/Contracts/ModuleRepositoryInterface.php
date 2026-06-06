<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Module;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends RepositoryInterface<Module>
 */
interface ModuleRepositoryInterface extends RepositoryInterface
{
    /** @return Collection<int, Module> */
    public function forFiliere(string $filiereId): Collection;
}
