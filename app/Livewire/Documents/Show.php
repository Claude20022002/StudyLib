<?php

declare(strict_types=1);

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\RatingService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Document $document;

    /** @var Collection<int, Document> */
    public Collection $similarDocuments;

    /** @var Collection<int, Document> */
    public Collection $examDocuments;

    public int $authorDocumentCount = 0;

    public ?int $userRatingScore = null;

    public bool $rateModalOpen = false;

    public int $rateScore = 0;

    public function mount(Document $document, DocumentService $documents): void
    {
        $this->authorize('view', $document);

        $pageData = $documents->showPageData($document, Auth::user());

        $this->document = $pageData['document'];
        $this->similarDocuments = $pageData['similarDocuments'];
        $this->examDocuments = $pageData['examDocuments'];
        $this->authorDocumentCount = $pageData['authorDocumentCount'];
        $this->userRatingScore = $pageData['userRating']?->score;
        $this->rateScore = $this->userRatingScore ?? 0;
    }

    public function openRateModal(): void
    {
        $this->rateScore = $this->userRatingScore ?? 0;
        $this->rateModalOpen = true;
    }

    public function closeRateModal(): void
    {
        $this->rateModalOpen = false;
    }

    public function setRateScore(int $score): void
    {
        $this->rateScore = max(1, min(5, $score));
    }

    public function submitRating(RatingService $ratings): void
    {
        $this->validate([
            'rateScore' => ['required', 'integer', 'between:1,5'],
        ]);

        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $ratings->rate($user, $this->document, $this->rateScore);

        $this->document->refresh();
        $this->userRatingScore = $this->rateScore;
        $this->rateModalOpen = false;

        session()->flash('success', 'Merci ! Votre note a été enregistrée.');
    }

    public function render(): View
    {
        return view('livewire.documents.show');
    }
}
