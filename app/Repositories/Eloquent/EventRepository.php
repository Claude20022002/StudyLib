<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}
