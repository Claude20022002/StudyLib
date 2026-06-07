<?php

declare(strict_types=1);

namespace App\Livewire\InternshipReviews;

use App\Models\InternshipReview;
use App\Models\User;
use App\Services\FiliereService;
use App\Services\InternshipReviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
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
    public string $yearLevel = '';

    #[Url(as: 'ville')]
    public string $city = '';

    #[Url(as: 'secteur')]
    public string $sector = '';

    #[Url(as: 'annee')]
    public string $yearDone = '';

    #[Url(as: 'note')]
    public int $minRating = 0;

    #[Url(as: 'tri')]
    public string $sort = 'rating';

    public bool $filtersOpen = false;

    public bool $detailOpen = false;

    public bool $shareOpen = false;

    public ?string $selectedCompanyId = null;

    public string $shareCompanyName = '';

    public string $shareCompanyCity = '';

    public string $shareCompanySector = '';

    public string $shareFiliereId = '';

    public string $sharePosition = '';

    public string $shareDescription = '';

    public int $shareRating = 0;

    public string $shareYearLevel = '';

    public string $shareYearDone = '';

    public bool $shareIsPaid = false;

    public function mount(): void
    {
        if (request()->boolean('partager')) {
            $this->shareOpen = true;
        }

        $user = Auth::user();

        if ($user instanceof User && $this->shareFiliereId === '' && $user->filiere_id) {
            $this->shareFiliereId = $user->filiere_id;
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

    public function updatedYearLevel(): void
    {
        $this->resetPage();
    }

    public function updatedCity(): void
    {
        $this->resetPage();
    }

    public function updatedSector(): void
    {
        $this->resetPage();
    }

    public function updatedYearDone(): void
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

    public function setMinRating(int $rating): void
    {
        $this->minRating = $this->minRating === $rating ? 0 : $rating;
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
        $this->yearLevel = '';
        $this->city = '';
        $this->sector = '';
        $this->yearDone = '';
        $this->minRating = 0;
        $this->resetPage();
    }

    public function openDetail(string $companyId): void
    {
        $this->selectedCompanyId = $companyId;
        $this->detailOpen = true;
    }

    public function closeDetail(): void
    {
        $this->detailOpen = false;
        $this->selectedCompanyId = null;
    }

    public function openShare(?string $companyName = null): void
    {
        if ($companyName !== null && $companyName !== '') {
            $this->shareCompanyName = $companyName;
        }

        $this->shareOpen = true;
    }

    public function closeShare(): void
    {
        $this->shareOpen = false;
        $this->resetShareForm();
    }

    public function setShareRating(int $rating): void
    {
        $this->shareRating = $rating;
    }

    public function submitShare(InternshipReviewService $reviews): void
    {
        $this->validate([
            'shareCompanyName' => ['required', 'string', 'max:150'],
            'shareCompanyCity' => ['nullable', 'string', 'max:100'],
            'shareCompanySector' => ['nullable', 'string', 'max:100'],
            'shareFiliereId' => ['nullable', 'uuid', 'exists:filieres,id'],
            'sharePosition' => ['nullable', 'string', 'max:150'],
            'shareDescription' => ['required', 'string'],
            'shareRating' => ['required', 'integer', 'between:1,5'],
            'shareYearLevel' => ['nullable', 'integer', 'between:1,5'],
            'shareYearDone' => ['nullable', 'integer', 'between:2000,2100'],
            'shareIsPaid' => ['boolean'],
        ], [
            'shareRating.required' => 'Veuillez attribuer une note à votre expérience.',
            'shareRating.between' => 'La note doit être entre 1 et 5.',
        ]);

        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $this->authorize('create', InternshipReview::class);

        $reviews->create($user, [
            'company_name' => $this->shareCompanyName,
            'company_city' => $this->shareCompanyCity !== '' ? $this->shareCompanyCity : null,
            'company_sector' => $this->shareCompanySector !== '' ? $this->shareCompanySector : null,
            'filiere_id' => $this->shareFiliereId !== '' ? $this->shareFiliereId : null,
            'position' => $this->sharePosition !== '' ? $this->sharePosition : null,
            'description' => $this->shareDescription,
            'rating' => $this->shareRating,
            'year_level' => $this->shareYearLevel !== '' ? (int) $this->shareYearLevel : null,
            'year_done' => $this->shareYearDone !== '' ? (int) $this->shareYearDone : null,
            'is_paid' => $this->shareIsPaid,
        ]);

        $this->closeShare();
        session()->flash('success', 'Votre retour de stage a été publié. Merci pour le partage !');
        $this->resetPage();
    }

    public function render(
        InternshipReviewService $reviews,
        FiliereService $filieres,
    ): View {
        $filters = $this->filterPayload();
        $detail = null;

        if ($this->selectedCompanyId !== null) {
            $detail = $reviews->companyDetail($this->selectedCompanyId);
        }

        return view('livewire.internship-reviews.index', [
            'companies' => $reviews->browse($filters),
            'filterOptions' => $reviews->filterOptions(),
            'filieres' => $filieres->all(),
            'detail' => $detail,
            'reviewService' => $reviews,
        ]);
    }

    /** @return array<string, mixed> */
    private function filterPayload(): array
    {
        $filters = [
            'q' => trim($this->search) !== '' ? trim($this->search) : null,
            'filiere_id' => $this->filiereId !== '' ? $this->filiereId : null,
            'year_level' => $this->yearLevel !== '' ? (int) $this->yearLevel : null,
            'city' => $this->city !== '' ? $this->city : null,
            'sector' => $this->sector !== '' ? $this->sector : null,
            'year_done' => $this->yearDone !== '' ? (int) $this->yearDone : null,
            'min_rating' => $this->minRating > 0 ? $this->minRating : null,
            'sort' => $this->sort,
        ];

        return array_filter(
            $filters,
            fn (mixed $value): bool => $value !== null,
        );
    }

    private function resetShareForm(): void
    {
        $user = Auth::user();

        $this->shareCompanyName = '';
        $this->shareCompanyCity = '';
        $this->shareCompanySector = '';
        $this->shareFiliereId = $user instanceof User && $user->filiere_id ? $user->filiere_id : '';
        $this->sharePosition = '';
        $this->shareDescription = '';
        $this->shareRating = 0;
        $this->shareYearLevel = '';
        $this->shareYearDone = '';
        $this->shareIsPaid = false;
        $this->resetValidation();
    }
}
