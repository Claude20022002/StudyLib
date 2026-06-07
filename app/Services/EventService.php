<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class EventService
{
    private const IMAGE_DISK = 'public';

    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'hackathon' => 'Hackathon',
        'conference' => 'Conférence',
        'soutenance' => 'Soutenance',
        'portes' => 'Portes ouvertes',
        'default' => 'Événement',
    ];

    public function __construct(
        private readonly EventRepositoryInterface $events,
    ) {}

    public function upcoming(): LengthAwarePaginator
    {
        return $this->events->upcoming();
    }

    public function find(string $id): ?Event
    {
        $event = $this->events->find($id);

        return $event instanceof Event ? $event->loadMissing(['author']) : null;
    }

    /** @return Collection<int, Event> */
    public function forMonth(int $year, int $month, ?string $search = null): Collection
    {
        return $this->events->forMonth($year, $month, $search);
    }

    public function monthTitle(int $year, int $month): string
    {
        return Carbon::create($year, $month, 1)
            ->locale('fr')
            ->translatedFormat('F Y');
    }

    public function typeKey(Event $event): string
    {
        $title = mb_strtolower($event->title);

        if (str_contains($title, 'hackathon')) {
            return 'hackathon';
        }

        if (str_contains($title, 'conférence') || str_contains($title, 'conference') || str_contains($title, 'séminaire') || str_contains($title, 'seminaire')) {
            return 'conference';
        }

        if (str_contains($title, 'soutenance') || str_contains($title, 'examen')) {
            return 'soutenance';
        }

        if (str_contains($title, 'portes ouvertes') || str_contains($title, 'forum')) {
            return 'portes';
        }

        return 'default';
    }

    public function typeLabel(Event $event): string
    {
        return self::TYPE_LABELS[$this->typeKey($event)] ?? self::TYPE_LABELS['default'];
    }

    public function formatTime(Event $event): string
    {
        return $event->starts_at->format('H:i');
    }

    public function formatDuration(Event $event): ?string
    {
        if ($event->ends_at === null) {
            return null;
        }

        $minutes = (int) $event->starts_at->diffInMinutes($event->ends_at);

        if ($minutes < 60) {
            return $minutes.' min';
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        if ($rest === 0) {
            return $hours.' h';
        }

        return $hours.' h '.$rest.' min';
    }

    public function maskedOrganizerName(Event $event): string
    {
        $name = $event->author?->name;

        if ($name === null || trim($name) === '') {
            return 'Organisation HESTIM';
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? '';
        $lastInitial = isset($parts[1]) ? mb_substr($parts[1], 0, 1).'.' : '';

        return trim($first.' '.$lastInitial) !== '' ? trim($first.' '.$lastInitial) : 'Organisateur';
    }

    /**
     * @param  Collection<int, Event>  $events
     * @return list<array{day: int, out: bool, is_today: bool, events: list<Event>}>
     */
    public function buildCalendarGrid(int $year, int $month, Collection $events): array
    {
        $first = Carbon::create($year, $month, 1)->startOfDay();
        $daysInMonth = $first->daysInMonth;
        $startOffset = $first->dayOfWeekIso - 1;

        /** @var Collection<int, Collection<int, Event>> $eventsByDay */
        $eventsByDay = $events->groupBy(fn (Event $event): int => (int) $event->starts_at->day);

        $cells = [];
        $prevMonth = $first->copy()->subMonth();
        $prevDays = $prevMonth->daysInMonth;

        for ($i = $startOffset - 1; $i >= 0; $i--) {
            $cells[] = [
                'day' => $prevDays - $i,
                'out' => true,
                'is_today' => false,
                'events' => [],
            ];
        }

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $first->copy()->day($day);

            $cells[] = [
                'day' => $day,
                'out' => false,
                'is_today' => now()->isSameDay($date),
                'events' => $eventsByDay->get($day, collect())->values()->all(),
            ];
        }

        $nextDay = 1;

        while (count($cells) % 7 !== 0) {
            $cells[] = [
                'day' => $nextDay,
                'out' => true,
                'is_today' => false,
                'events' => [],
            ];
            $nextDay++;
        }

        return $cells;
    }

    /**
     * @param  Collection<int, Event>  $events
     * @return list<array{date: Carbon, label: string, weekday: string, events: list<Event>}>
     */
    public function groupEventsByDay(Collection $events): array
    {
        $groups = [];

        foreach ($events->groupBy(fn (Event $event): string => $event->starts_at->toDateString()) as $dateString => $dayEvents) {
            $date = Carbon::parse((string) $dateString)->locale('fr');

            $groups[] = [
                'date' => $date,
                'label' => $date->translatedFormat('d F Y'),
                'weekday' => $date->translatedFormat('l'),
                'events' => $dayEvents->values()->all(),
            ];
        }

        return $groups;
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
