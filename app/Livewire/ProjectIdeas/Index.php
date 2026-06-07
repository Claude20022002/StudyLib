<?php

declare(strict_types=1);

namespace App\Livewire\ProjectIdeas;

use App\Enums\IdeaSource;
use App\Enums\StudyLevel;
use App\Models\Filiere;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\FiliereService;
use App\Services\ProjectIdeaService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'filiere')]
    public string $filiereId = '';

    #[Url(as: 'niveau')]
    public string $level = '';

    #[Url(as: 'source')]
    public string $source = '';

    #[Url(as: 'tri')]
    public string $sort = 'recent';

    public bool $filtersOpen = false;

    public bool $detailOpen = false;

    public bool $proposeOpen = false;

    public ?string $selectedIdeaId = null;

    public string $proposeTitle = '';

    public string $proposeDescription = '';

    public string $proposeLevel = '';

    public string $proposeFiliereId = '';

    public string $proposeRepoUrl = '';

    public string $aiFiliereId = '';

    public string $aiLevel = '';

    public string $aiInterests = '';

    /** @var list<string> */
    public array $aiGeneratedIds = [];

    public bool $aiLoading = false;

    public function mount(): void
    {
        if (request()->boolean('proposer')) {
            $this->proposeOpen = true;
        }

        $user = Auth::user();

        if ($user instanceof User) {
            if ($this->proposeFiliereId === '' && $user->filiere_id) {
                $this->proposeFiliereId = $user->filiere_id;
            }

            if ($this->aiFiliereId === '' && $user->filiere_id) {
                $this->aiFiliereId = $user->filiere_id;
            }
        }

        if ($this->aiLevel === '') {
            $this->aiLevel = StudyLevel::L3->value;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiliereId(): void
    {
        $this->resetPage();
    }

    public function updatedLevel(): void
    {
        $this->resetPage();
    }

    public function updatedSource(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function setSourceFilter(string $source): void
    {
        $this->source = $this->source === $source ? '' : $source;
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filiereId = '';
        $this->level = '';
        $this->source = '';
        $this->resetPage();
    }

    public function openDetail(string $ideaId): void
    {
        $this->selectedIdeaId = $ideaId;
        $this->detailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->detailOpen = false;
        $this->selectedIdeaId = null;
    }

    public function openPropose(): void
    {
        $this->proposeOpen = true;
    }

    public function closePropose(): void
    {
        $this->proposeOpen = false;
        $this->resetProposeForm();
    }

    public function submitPropose(ProjectIdeaService $ideas): void
    {
        $this->validate([
            'proposeTitle' => ['required', 'string', 'max:200'],
            'proposeDescription' => ['required', 'string'],
            'proposeLevel' => ['required', 'string', Rule::enum(StudyLevel::class)],
            'proposeFiliereId' => ['nullable', 'uuid', 'exists:filieres,id'],
            'proposeRepoUrl' => ['nullable', 'url', 'max:500'],
        ]);

        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $this->authorize('create', ProjectIdea::class);

        $ideas->create($user, [
            'title' => $this->proposeTitle,
            'description' => $this->proposeDescription,
            'level' => $this->proposeLevel,
            'filiere_id' => $this->proposeFiliereId !== '' ? $this->proposeFiliereId : null,
            'repo_url' => $this->proposeRepoUrl !== '' ? $this->proposeRepoUrl : null,
        ]);

        $this->closePropose();
        session()->flash('success', 'Votre idée de projet a été publiée.');
        $this->resetPage();
    }

    public function generateAiIdeas(ProjectIdeaService $ideas, FiliereService $filieres): void
    {
        $this->validate([
            'aiFiliereId' => ['required', 'uuid', 'exists:filieres,id'],
            'aiLevel' => ['required', 'string', Rule::enum(StudyLevel::class)],
            'aiInterests' => ['nullable', 'string', 'max:500'],
        ]);

        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $filiere = $filieres->all()->firstWhere('id', $this->aiFiliereId);
        $level = StudyLevel::from($this->aiLevel);

        $this->aiLoading = true;

        $generated = $ideas->generateAiIdeas(
            $user,
            $filiere?->name ?? 'HESTIM',
            $level,
            trim($this->aiInterests),
        );

        $this->aiGeneratedIds = collect($generated)->pluck('id')->all();
        $this->aiLoading = false;
        $this->resetPage();

        session()->flash('success', count($generated).' idées IA générées et ajoutées à la bibliothèque.');
    }

    public function render(ProjectIdeaService $ideas, FiliereService $filieres): View
    {
        $detail = $this->selectedIdeaId !== null
            ? $ideas->find($this->selectedIdeaId)
            : null;

        $aiIdeas = $this->aiGeneratedIds !== []
            ? ProjectIdea::query()->whereIn('id', $this->aiGeneratedIds)->with(['filiere'])->get()
            : collect();

        return view('livewire.project-ideas.index', [
            'ideas' => $ideas->browse($this->filterPayload()),
            'filieres' => $filieres->all(),
            'detail' => $detail,
            'ideaService' => $ideas,
            'studyLevels' => StudyLevel::cases(),
            'ideaSources' => IdeaSource::cases(),
            'aiIdeas' => $aiIdeas,
        ]);
    }

    /** @return array<string, mixed> */
    private function filterPayload(): array
    {
        $filters = [
            'q' => trim($this->search) !== '' ? trim($this->search) : null,
            'filiere_id' => $this->filiereId !== '' ? $this->filiereId : null,
            'level' => $this->level !== '' ? $this->level : null,
            'source' => $this->source !== '' ? $this->source : null,
            'sort' => $this->sort,
        ];

        return array_filter(
            $filters,
            fn (mixed $value): bool => $value !== null,
        );
    }

    private function resetProposeForm(): void
    {
        $user = Auth::user();

        $this->proposeTitle = '';
        $this->proposeDescription = '';
        $this->proposeLevel = '';
        $this->proposeFiliereId = $user instanceof User && $user->filiere_id ? $user->filiere_id : '';
        $this->proposeRepoUrl = '';
        $this->resetValidation();
    }
}
