<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

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

    public function forMonth(int $year, int $month, ?string $search = null): Collection
    {
        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        $query = $this->model->newQuery()
            ->with(['author'])
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at');

        if ($search !== null && $search !== '') {
            $term = '%'.$search.'%';

            $query->where(function ($builder) use ($term): void {
                $builder->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('location', 'like', $term);
            });
        }

        return $query->get();
    }

    public function adminList(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['author'])
            ->orderByDesc('starts_at');

        if ($search !== null && $search !== '') {
            $term = '%'.$search.'%';

            $query->where(function ($builder) use ($term): void {
                $builder->where('title', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('location', 'like', $term);
            });
        }

        return $query->paginate($perPage);
    }

    public function countAll(): int
    {
        return $this->model->newQuery()->count();
    }
}
