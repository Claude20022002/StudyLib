<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class EventService
{
    private const IMAGE_DISK = 'public';

    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {}

    public function upcoming(): LengthAwarePaginator
    {
        return $this->events->upcoming();
    }

    /**
     * @param  array{title: string, description?: string|null, starts_at: string, ends_at?: string|null, location?: string|null}  $data
     */
    public function create(User $organizer, array $data, ?UploadedFile $image = null): Event
    {
        if ($image !== null) {
            $data['image_path'] = $image->store('events', self::IMAGE_DISK);
        }

        unset($data['image']);

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
    public function update(Event $event, array $data, ?UploadedFile $image = null): Event
    {
        if ($image !== null) {
            $this->deleteImage($event);
            $data['image_path'] = $image->store('events', self::IMAGE_DISK);
        }

        unset($data['image']);

        return $this->events->update($event, $data);
    }

    public function delete(Event $event): void
    {
        $this->deleteImage($event);

        $this->events->delete($event);
    }

    private function deleteImage(Event $event): void
    {
        if (filled($event->image_path)) {
            Storage::disk(self::IMAGE_DISK)->delete($event->image_path);
        }
    }
}
