<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Document;
use App\Services\ModerationService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ModerationIndex extends Component
{
    use WithPagination;

    #[Url(as: 'statut')]
    public string $statusFilter = 'pending';

    #[Url(as: 'q')]
    public string $search = '';

    public bool $rejectModalOpen = false;

    public ?string $rejectDocumentId = null;

    public string $rejectReason = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function approve(string $documentId, ModerationService $moderation): void
    {
        $this->authorize('moderate', Document::class);

        $document = Document::query()->findOrFail($documentId);
        $moderation->approve($document);

        session()->flash('success', '« '.$document->title.' » a été approuvé.');
    }

    public function openRejectModal(string $documentId): void
    {
        $this->rejectDocumentId = $documentId;
        $this->rejectReason = '';
        $this->rejectModalOpen = true;
    }

    public function closeRejectModal(): void
    {
        $this->rejectModalOpen = false;
        $this->rejectDocumentId = null;
        $this->rejectReason = '';
    }

    public function submitReject(ModerationService $moderation): void
    {
        $this->authorize('moderate', Document::class);

        $this->validate([
            'rejectDocumentId' => ['required', 'uuid', 'exists:documents,id'],
            'rejectReason' => ['nullable', 'string', 'max:500'],
        ]);

        $document = Document::query()->findOrFail($this->rejectDocumentId);
        $moderation->reject($document, $this->rejectReason !== '' ? $this->rejectReason : null);

        $this->closeRejectModal();

        session()->flash('success', '« '.$document->title.' » a été refusé.');
    }

    public function render(ModerationService $moderation): View
    {
        $counts = $moderation->statusCounts();

        return view('livewire.admin.moderation-index', [
            'counts' => $counts,
            'documents' => $moderation->listForAdmin($this->statusFilter, $this->search),
            'rejectDocument' => $this->rejectDocumentId
                ? Document::query()->with(['author', 'module'])->find($this->rejectDocumentId)
                : null,
        ]);
    }
}
