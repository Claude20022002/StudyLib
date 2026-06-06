<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<Event>
 */
class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    public function __construct(Event $model)
    {
        parent::__construct($model);
    }

    public function upcoming(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()->upcoming()->paginate($perPage);
    }

    public function upcomingList(int $limit = 3): Collection
    {
        return $this->model->newQuery()->upcoming()->limit($limit)->get();
    }

    public function countUpcoming(): int
    {
        return $this->model->newQuery()->upcoming()->count();
    }

    public function daysUntilNext(): ?int
    {
        $next = $this->model->newQuery()->upcoming()->value('starts_at');

        if ($next === null) {
            return null;
        }

        $startsAt = $next instanceof \Carbon\CarbonInterface ? $next : \Illuminate\Support\Carbon::parse($next);

        return (int) now()->startOfDay()->diffInDays($startsAt->copy()->startOfDay(), false);
    }
}
