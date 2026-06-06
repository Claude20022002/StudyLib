<?php

declare(strict_types=1);

namespace App\Livewire\Documents;

use App\Enums\DocumentType;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\FiliereService;
use App\Services\ModuleService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'filiere')]
    public string $filiereId = '';

    #[Url(as: 'semestre')]
    public string $semester = '';

    #[Url(as: 'module')]
    public string $moduleId = '';

    #[Url(as: 'annee')]
    public string $yearConcern = '';

    /** @var list<string> */
    #[Url(as: 'types')]
    public array $types = [];

    #[Url(as: 'note')]
    public int $minRating = 0;

    #[Url(as: 'tri')]
    public string $sort = 'recent';

    #[Url(as: 'vue')]
    public string $viewMode = 'list';

    #[Url(as: 'mine')]
    public bool $mine = false;

    public bool $filtersOpen = false;

    public bool $uploadOpen = false;

    public ?TemporaryUploadedFile $uploadFile = null;

    public string $uploadTitle = '';

    public string $uploadModuleId = '';

    public string $uploadType = '';

    public string $uploadYear = '';

    public bool $rightsAcknowledged = false;

    public function mount(): void
    {
        if (request()->boolean('upload')) {
            $this->uploadOpen = true;
        }
    }

    public function updatedFiliereId(): void
    {
        $this->moduleId = '';
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSemester(): void
    {
        $this->resetPage();
    }

    public function updatedModuleId(): void
    {
        $this->resetPage();
    }

    public function updatedYearConcern(): void
    {
        $this->resetPage();
    }

    public function updatedTypes(): void
    {
        $this->resetPage();
    }

    public function updatedMinRating(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedMine(): void
    {
        $this->resetPage();
    }

    public function toggleType(string $type): void
    {
        if (in_array($type, $this->types, true)) {
            $this->types = array_values(array_filter(
                $this->types,
                fn (string $value): bool => $value !== $type,
            ));
        } else {
            $this->types[] = $type;
        }

        $this->resetPage();
    }

    public function setMinRating(int $rating): void
    {
        $this->minRating = $this->minRating === $rating ? 0 : $rating;
        $this->resetPage();
    }

    public function applyQuickSearch(string $term): void
    {
        $this->search = $term;
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
        $this->semester = '';
        $this->moduleId = '';
        $this->yearConcern = '';
        $this->types = [];
        $this->minRating = 0;
        $this->resetPage();
    }

    public function removeFilter(string $key, ?string $value = null): void
    {
        match ($key) {
            'q' => $this->search = '',
            'filiere' => $this->filiereId = '',
            'semestre' => $this->semester = '',
            'module' => $this->moduleId = '',
            'annee' => $this->yearConcern = '',
            'note' => $this->minRating = 0,
            'type' => $this->types = array_values(array_filter(
                $this->types,
                fn (string $type): bool => $type !== $value,
            )),
            default => null,
        };

        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode === 'grid' ? 'grid' : 'list';
    }

    public function openUpload(): void
    {
        $this->uploadOpen = true;
    }

    public function closeUpload(): void
    {
        $this->uploadOpen = false;
        $this->resetUploadForm();
    }

    public function removeUploadFile(): void
    {
        $this->uploadFile = null;
    }

    public function submitUpload(DocumentService $documents): void
    {
        $this->validate([
            'uploadFile' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx'],
            'uploadTitle' => ['required', 'string', 'max:200'],
            'uploadModuleId' => ['required', 'uuid', 'exists:modules,id'],
            'uploadType' => ['required', 'string', Rule::enum(DocumentType::class)],
            'uploadYear' => ['nullable', 'integer', 'between:2000,2100'],
            'rightsAcknowledged' => ['accepted'],
        ], [
            'rightsAcknowledged.accepted' => 'Vous devez attester disposer des droits de partage.',
        ]);

        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $documents->upload($user, $this->uploadFile, [
            'module_id' => $this->uploadModuleId,
            'type' => $this->uploadType,
            'title' => $this->uploadTitle,
            'year_concern' => $this->uploadYear !== '' ? (int) $this->uploadYear : null,
        ]);

        $this->closeUpload();
        session()->flash('success', 'Document envoyé. Il sera visible après modération.');
        $this->resetPage();
    }

    public function render(
        DocumentService $documents,
        FiliereService $filieres,
        ModuleService $modules,
    ): View {
        $filters = $this->filterPayload();
        $countFilters = $filters;
        unset($countFilters['types'], $countFilters['sort']);

        return view('livewire.documents.index', [
            'documents' => $documents->browse($filters),
            'typeCounts' => $documents->typeCountsForBrowse($countFilters),
            'filieres' => $filieres->all(),
            'modules' => $this->modulesForFilter($modules),
            'academicYears' => $this->academicYears(),
            'activeChips' => $this->activeFilterChips($filieres),
        ]);
    }

    /** @return array<string, mixed> */
    private function filterPayload(): array
    {
        $user = Auth::user();

        $filters = [
            'q' => trim($this->search) !== '' ? trim($this->search) : null,
            'filiere_id' => $this->filiereId !== '' ? $this->filiereId : null,
            'semester' => $this->semester !== '' ? (int) $this->semester : null,
            'module_id' => $this->moduleId !== '' ? $this->moduleId : null,
            'year_concern' => $this->yearConcern !== '' ? (int) $this->yearConcern : null,
            'types' => $this->types !== [] ? $this->types : null,
            'min_rating' => $this->minRating > 0 ? (float) $this->minRating : null,
            'sort' => $this->sort,
            'mine' => $this->mine,
            'user_id' => $user instanceof User ? $user->getKey() : null,
        ];

        return array_filter(
            $filters,
            fn (mixed $value): bool => $value !== null && $value !== false,
        );
    }

    /** @return Collection<int, Module> */
    private function modulesForFilter(ModuleService $modules): Collection
    {
        if ($this->filiereId === '') {
            return Module::query()->orderBy('name')->get();
        }

        $filiereModel = Filiere::query()->find($this->filiereId);

        if ($filiereModel === null) {
            return collect();
        }

        return $modules->forFiliere($filiereModel);
    }

    /** @return list<array{value: int, label: string}> */
    private function academicYears(): array
    {
        $years = [];
        $current = (int) now()->format('Y');

        for ($year = $current; $year >= $current - 3; $year--) {
            $years[] = [
                'value' => $year,
                'label' => $year.'-'.($year + 1),
            ];
        }

        return $years;
    }

    /**
     * @return list<array{key: string, label: string, value?: string}>
     */
    private function activeFilterChips(FiliereService $filieres): array
    {
        $chips = [];

        if (trim($this->search) !== '') {
            $chips[] = ['key' => 'q', 'label' => '« '.trim($this->search).' »'];
        }

        if ($this->filiereId !== '') {
            $name = $filieres->all()->firstWhere('id', $this->filiereId)?->name ?? 'Filière';
            $chips[] = ['key' => 'filiere', 'label' => $name];
        }

        if ($this->semester !== '') {
            $chips[] = ['key' => 'semestre', 'label' => 'S'.$this->semester];
        }

        if ($this->moduleId !== '') {
            $module = Module::query()->find($this->moduleId);
            $chips[] = ['key' => 'module', 'label' => $module?->name ?? 'Module'];
        }

        if ($this->yearConcern !== '') {
            $chips[] = ['key' => 'annee', 'label' => $this->yearConcern.'-'.((int) $this->yearConcern + 1)];
        }

        foreach ($this->types as $type) {
            $documentType = DocumentType::tryFrom($type);
            $chips[] = [
                'key' => 'type',
                'value' => $type,
                'label' => $documentType?->label() ?? $type,
            ];
        }

        if ($this->minRating > 0) {
            $chips[] = ['key' => 'note', 'label' => $this->minRating.'★ et +'];
        }

        return $chips;
    }

    private function resetUploadForm(): void
    {
        $this->uploadFile = null;
        $this->uploadTitle = '';
        $this->uploadModuleId = '';
        $this->uploadType = '';
        $this->uploadYear = '';
        $this->rightsAcknowledged = false;
        $this->resetValidation();
    }
}
