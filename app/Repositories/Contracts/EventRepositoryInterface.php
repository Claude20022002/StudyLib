<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends RepositoryInterface<Event>
 */
interface EventRepositoryInterface extends RepositoryInterface
{
    /** @return LengthAwarePaginator<int, Event> */
    public function upcoming(int $perPage = 15): LengthAwarePaginator;

    /** @return Collection<int, Event> */
    public function upcomingList(int $limit = 3): Collection;

    public function countUpcoming(): int;

    public function daysUntilNext(): ?int;
}
