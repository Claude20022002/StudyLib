<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Event;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class EventsIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public bool $formOpen = false;

    public bool $deleteModalOpen = false;

    public ?string $editingEventId = null;

    public ?string $deleteEventId = null;

    public string $formTitle = '';

    public string $formDescription = '';

    public string $formStartsAt = '';

    public string $formEndsAt = '';

    public string $formLocation = '';

    public ?TemporaryUploadedFile $formImage = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->authorize('create', Event::class);
        $this->resetForm();
        $this->editingEventId = null;
        $this->formOpen = true;
    }

    public function openEditForm(string $eventId): void
    {
        $event = Event::query()->findOrFail($eventId);
        $this->authorize('update', $event);

        $this->editingEventId = $event->id;
        $this->formTitle = $event->title;
        $this->formDescription = $event->description ?? '';
        $this->formStartsAt = $event->starts_at->format('Y-m-d\TH:i');
        $this->formEndsAt = $event->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->formLocation = $event->location ?? '';
        $this->formImage = null;
        $this->formOpen = true;
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
        $this->resetForm();
    }

    public function save(EventService $events): void
    {
        $this->validate([
            'formTitle' => ['required', 'string', 'max:200'],
            'formDescription' => ['nullable', 'string'],
            'formStartsAt' => ['required', 'date'],
            'formEndsAt' => ['nullable', 'date', 'after_or_equal:formStartsAt'],
            'formLocation' => ['nullable', 'string', 'max:200'],
            'formImage' => ['nullable', 'image', 'max:5120'],
        ]);

        $payload = [
            'title' => $this->formTitle,
            'description' => $this->formDescription !== '' ? $this->formDescription : null,
            'starts_at' => $this->formStartsAt,
            'ends_at' => $this->formEndsAt !== '' ? $this->formEndsAt : null,
            'location' => $this->formLocation !== '' ? $this->formLocation : null,
        ];

        if ($this->editingEventId !== null) {
            $event = Event::query()->findOrFail($this->editingEventId);
            $this->authorize('update', $event);

            $events->update($event, $payload, $this->formImage);
            session()->flash('success', '« '.$event->title.' » a été mis à jour.');
        } else {
            $user = Auth::user();

            if (! $user instanceof User) {
                return;
            }

            $this->authorize('create', Event::class);

            $event = $events->create($user, $payload, $this->formImage);
            session()->flash('success', '« '.$event->title.' » a été créé.');
        }

        $this->closeForm();
        $this->resetPage();
    }

    public function openDeleteModal(string $eventId): void
    {
        $event = Event::query()->findOrFail($eventId);
        $this->authorize('delete', $event);

        $this->deleteEventId = $eventId;
        $this->deleteModalOpen = true;
    }

    public function closeDeleteModal(): void
    {
        $this->deleteModalOpen = false;
        $this->deleteEventId = null;
    }

    public function confirmDelete(EventService $events): void
    {
        if ($this->deleteEventId === null) {
            return;
        }

        $event = Event::query()->findOrFail($this->deleteEventId);
        $this->authorize('delete', $event);

        $title = $event->title;
        $events->delete($event);

        $this->closeDeleteModal();
        session()->flash('success', '« '.$title.' » a été supprimé.');
        $this->resetPage();
    }

    public function render(EventService $events): View
    {
        $deleteEvent = $this->deleteEventId !== null
            ? Event::query()->find($this->deleteEventId)
            : null;

        return view('livewire.admin.events-index', [
            'stats' => $events->adminStats(),
            'eventRows' => $events->adminList(trim($this->search) !== '' ? trim($this->search) : null),
            'eventService' => $events,
            'deleteEvent' => $deleteEvent,
        ]);
    }

    private function resetForm(): void
    {
        $this->editingEventId = null;
        $this->formTitle = '';
        $this->formDescription = '';
        $this->formStartsAt = '';
        $this->formEndsAt = '';
        $this->formLocation = '';
        $this->formImage = null;
        $this->resetValidation();
    }
}
