<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{
    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {
    }

    public function upcoming(): LengthAwarePaginator
    {
        return $this->events->upcoming();
    }

    /**
     * @param  array{title: string, description?: string|null, starts_at: string, ends_at?: string|null, location?: string|null, image_path?: string|null}  $data
     */
    public function create(User $organizer, array $data): Event
    {
        return $this->events->create([
            'user_id' => $organizer->getKey(),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'location' => $data['location'] ?? null,
            'image_path' => $data['image_path'] ?? null,
        ]);
    }

    /** @param array<string, mixed> $data */
    public function update(Event $event, array $data): Event
    {
        return $this->events->update($event, $data);
    }
}
