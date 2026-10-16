<?php

declare(strict_types=1);

namespace App\Services\Offres;

use App\Enums\DocumentStatus;
use App\Models\InternshipOffer;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Offres de stage : publication (un enseignant ou l'administration publie directement, un étudiant
 * passe par la modération, comme pour les documents), liste de l'école (approuvées, non expirées,
 * filtrables) et mes offres.
 */
class OffresService
{
    public const PAR_PAGE = 15;

    /** Sans date limite, une offre reste 60 jours */
    public const DUREE_JOURS = 60;

    public function publier(User $auteur, array $champs): InternshipOffer
    {
        $limite = filled($champs['deadline'] ?? null) ? CarbonImmutable::parse($champs['deadline']) : null;

        return InternshipOffer::query()->create([
            ...$champs,
            'user_id' => $auteur->getKey(),
            'status' => $auteur->role->publishesWithoutModeration() ? DocumentStatus::Approved->value : DocumentStatus::Pending->value,
            'expires_at' => $limite ? $limite->addDay()->startOfDay() : now()->addDays(self::DUREE_JOURS),
        ]);
    }

    /** @param  array{q?: string, type?: string, city?: string}  $filtres */
    public function lister(array $filtres): LengthAwarePaginator
    {
        $requete = InternshipOffer::query()->visible();
        $texte = trim((string) ($filtres['q'] ?? ''));
        if ($texte !== '') {
            $motif = '%'.addcslashes(mb_substr($texte, 0, 80), '%_\\').'%';
            $requete->where(fn (Builder $q) => $q->where('title', 'like', $motif)->orWhere('company_name', 'like', $motif)->orWhere('description', 'like', $motif));
        }
        if (filled($filtres['type'] ?? null)) {
            $requete->where('type', $filtres['type']);
        }
        if (filled($filtres['city'] ?? null)) {
            $requete->where('city', $filtres['city']);
        }

        // Les dates limites les plus proches d'abord, puis les plus récentes
        return $requete->orderByRaw('deadline is null')->orderBy('deadline')->latest()->paginate(self::PAR_PAGE)->through(fn ($o) => $this->versMobile($o));
    }

    public function miennes(User $user): LengthAwarePaginator
    {
        return InternshipOffer::query()->where('user_id', $user->getKey())->latest()->paginate(self::PAR_PAGE)->through(fn ($o) => $this->versMobile($o, true));
    }

    /** Villes des offres visibles, pour le filtre */
    public function villes(): array
    {
        return InternshipOffer::query()->visible()->whereNotNull('city')->distinct()->orderBy('city')->pluck('city')->all();
    }

    public function versMobile(InternshipOffer $o, bool $auteur = false): array
    {
        return [
            'id' => $o->id,
            'title' => $o->title,
            'company' => $o->company_name,
            'city' => $o->city,
            'type' => $o->type,
            'deadline' => $o->deadline?->toDateString(),
            'salary' => $o->salary,
            'description' => $o->description,
            'apply_url' => $o->apply_url,
            'host' => parse_url($o->apply_url, PHP_URL_HOST),
            'created_at' => $o->created_at?->toIso8601String(),
            ...($auteur ? ['statut' => $o->expires_at->isPast() ? 'expiree' : $o->status->value] : []),
        ];
    }
}
