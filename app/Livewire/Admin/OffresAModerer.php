<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\InternshipOffer;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Offres de stage partagées par des étudiants, en attente de modération (en tête de la page de
 * modération). Les offres des enseignants et de l'administration sont publiées sans passer ici.
 */
class OffresAModerer extends Component
{
    public function approuver(string $id): void
    {
        $this->authorize('moderate', Document::class);
        $offre = InternshipOffer::query()->findOrFail($id);
        $offre->update(['status' => DocumentStatus::Approved->value]);
        session()->flash('success', '« '.$offre->title.' » est publiée.');
    }

    public function refuser(string $id): void
    {
        $this->authorize('moderate', Document::class);
        $offre = InternshipOffer::query()->findOrFail($id);
        $offre->update(['status' => DocumentStatus::Rejected->value]);
        session()->flash('success', '« '.$offre->title.' » est refusée.');
    }

    public function render(): View
    {
        $this->authorize('moderate', Document::class);

        return view('livewire.admin.offres-a-moderer', [
            'offres' => InternshipOffer::query()->with('author:id,name')->where('status', DocumentStatus::Pending)->where('expires_at', '>', now())->oldest()->limit(50)->get(),
        ]);
    }
}
