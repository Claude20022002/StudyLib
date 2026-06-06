<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\DocumentType;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    /** @var array<string, mixed> */
    public array $overview = [];

    public string $filter = 'all';

    public bool $ready = false;

    /** @var Collection<int, \App\Models\Document> */
    public Collection $documents;

    /** @var Collection<int, \App\Models\Event> */
    public Collection $events;

    /** @var Collection<int, \App\Models\YoutubeRecommendation> */
    public Collection $videos;

    public function mount(): void
    {
        $this->documents = collect();
        $this->events = collect();
        $this->videos = collect();
    }

    public function loadDashboard(DashboardService $dashboard): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $user->load('filiere.modules');

        $this->overview = $dashboard->overview($user);
        $this->documents = $dashboard->recommendedDocuments($user, $this->resolveType());
        $this->events = $dashboard->upcomingEvents();
        $this->videos = $dashboard->featuredVideos($user);
        $this->ready = true;
    }

    public function setFilter(string $filter, DashboardService $dashboard): void
    {
        $this->filter = $filter;

        $user = Auth::user();

        if ($user instanceof User) {
            $this->documents = $dashboard->recommendedDocuments($user, $this->resolveType());
        }
    }

    public function render(): View
    {
        return view('livewire.dashboard.index');
    }

    private function resolveType(): ?DocumentType
    {
        if ($this->filter === 'all') {
            return null;
        }

        return DocumentType::tryFrom($this->filter);
    }
}
