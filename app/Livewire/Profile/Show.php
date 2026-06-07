<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Models\Document;
use App\Models\InternshipReview;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\FiliereService;
use App\Services\ProfileService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    #[Url(as: 'onglet')]
    public string $tab = 'docs';

    public string $name = '';

    public string $filiereId = '';

    public ?int $yearLevel = null;

    /** @var array<string, mixed> */
    public array $stats = [];

    /** @var Collection<int, Document> */
    public Collection $documents;

    /** @var Collection<int, InternshipReview> */
    public Collection $internshipReviews;

    /** @var Collection<int, ProjectIdea> */
    public Collection $projectIdeas;

    /** @var SupportCollection<int, array{time: string, text: string, tone: string}> */
    public SupportCollection $recentActivity;

    public function mount(ProfileService $profile): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $this->hydrateFromUser($user, $profile);
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['docs', 'stages', 'projets', 'favoris', 'params'], true)) {
            return;
        }

        $this->tab = $tab;
    }

    public function saveProfile(ProfileService $profile): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'filiereId' => ['nullable', 'uuid', 'exists:filieres,id'],
            'yearLevel' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $profile->update($user, [
            'name' => $this->name,
            'filiere_id' => $this->filiereId !== '' ? $this->filiereId : null,
            'year_level' => $this->yearLevel,
        ]);

        $this->hydrateFromUser($user->fresh(['filiere']), $profile);

        session()->flash('success', 'Profil mis à jour.');
    }

    public function render(FiliereService $filieres): View
    {
        return view('livewire.profile.show', [
            'user' => Auth::user(),
            'filieres' => $filieres->all(),
        ]);
    }

    private function hydrateFromUser(User $user, ProfileService $profile): void
    {
        $pageData = $profile->showPageData($user);

        $this->name = $user->name;
        $this->filiereId = $user->filiere_id ?? '';
        $this->yearLevel = $user->year_level;
        $this->stats = $pageData['stats'];
        $this->documents = $pageData['documents'];
        $this->internshipReviews = $pageData['internship_reviews'];
        $this->projectIdeas = $pageData['project_ideas'];
        $this->recentActivity = $pageData['recent_activity'];
    }
}
