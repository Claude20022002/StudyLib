<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends RepositoryInterface<Event>
 */
interface EventRepositoryInterface extends RepositoryInterface
{
    /** @return LengthAwarePaginator<int, Event> */
    public function upcoming(int $perPage = 15): LengthAwarePaginator;
}
