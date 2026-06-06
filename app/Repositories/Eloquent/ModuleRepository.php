<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Module;
use App\Repositories\Contracts\ModuleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Module>
 */
class ModuleRepository extends BaseRepository implements ModuleRepositoryInterface
{
    public function __construct(Module $model)
    {
        parent::__construct($model);
    }

    public function forFiliere(string $filiereId): Collection
    {
        return $this->model->newQuery()
            ->where('filiere_id', $filiereId)
            ->orderBy('semester')
            ->get();
    }
}
