<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Services\EventService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'annee')]
    public int $year = 0;

    #[Url(as: 'mois')]
    public int $monthNum = 0;

    #[Url(as: 'vue')]
    public string $viewMode = 'calendar';

    #[Url(as: 'q')]
    public string $search = '';

    public bool $detailOpen = false;

    public ?string $selectedEventId = null;

    public function mount(): void
    {
        $now = now();

        if ($this->year === 0) {
            $this->year = (int) $now->year;
        }

        if ($this->monthNum === 0) {
            $this->monthNum = (int) $now->month;
        }

        if (! in_array($this->viewMode, ['calendar', 'list'], true)) {
            $this->viewMode = 'calendar';
        }
    }

    public function previousMonth(): void
    {
        $date = now()->setDate($this->year, $this->monthNum, 1)->subMonth();
        $this->year = (int) $date->year;
        $this->monthNum = (int) $date->month;
    }

    public function nextMonth(): void
    {
        $date = now()->setDate($this->year, $this->monthNum, 1)->addMonth();
        $this->year = (int) $date->year;
        $this->monthNum = (int) $date->month;
    }

    public function goToToday(): void
    {
        $now = now();
        $this->year = (int) $now->year;
        $this->monthNum = (int) $now->month;
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['calendar', 'list'], true)) {
            $this->viewMode = $mode;
        }
    }

    public function clearSearch(): void
    {
        $this->search = '';
    }

    public function openDetail(string $eventId): void
    {
        $this->selectedEventId = $eventId;
        $this->detailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->detailOpen = false;
        $this->selectedEventId = null;
    }

    public function render(EventService $events): View
    {
        $monthEvents = $events->forMonth(
            $this->year,
            $this->monthNum,
            trim($this->search) !== '' ? trim($this->search) : null,
        );

        $detail = $this->selectedEventId !== null
            ? $events->find($this->selectedEventId)
            : null;

        return view('livewire.events.index', [
            'monthEvents' => $monthEvents,
            'calendarCells' => $events->buildCalendarGrid($this->year, $this->monthNum, $monthEvents),
            'dayGroups' => $events->groupEventsByDay($monthEvents),
            'monthTitle' => $events->monthTitle($this->year, $this->monthNum),
            'detail' => $detail,
            'eventService' => $events,
        ]);
    }
}
