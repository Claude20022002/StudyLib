<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\InternshipReview;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Retours de stage pour l'écran « Stages » de l'application (Planner) : tous les retours partagés,
 * filtrables, et ceux de l'étudiant lui-même. Un retour par ligne (le site, lui, regroupe par
 * entreprise). Seuls les retours dont l'auteur a donné son accord de publication sont montrés aux
 * autres ; l'auteur voit toujours les siens.
 */
class InternshipReviewMobileService
{
    public const PAR_PAGE = 15;

    /**
     * @param  array{q?: string, city?: string, sector?: string, year_done?: int|string, is_paid?: string|bool, filiere?: string}  $filtres
     */
    public function lister(array $filtres): LengthAwarePaginator
    {
        $requete = $this->base()->whereNotNull('consent_at');
        $this->filtrer($requete, $filtres);

        return $requete->latest()->paginate(self::PAR_PAGE)->through(fn (InternshipReview $r) => $this->versMobile($r));
    }

    public function miens(User $user): LengthAwarePaginator
    {
        return $this->base()->where('user_id', $user->getKey())->latest()->paginate(self::PAR_PAGE)->through(fn (InternshipReview $r) => $this->versMobile($r, true));
    }

    /** @return Builder<InternshipReview> */
    private function base(): Builder
    {
        return InternshipReview::query()->with(['company:id,name,city,sector', 'filiere:id,code']);
    }

    /** @param  Builder<InternshipReview>  $requete */
    private function filtrer(Builder $requete, array $filtres): void
    {
        $texte = trim((string) ($filtres['q'] ?? ''));
        if ($texte !== '') {
            // Recherche sur l'entreprise, le poste et le texte ; % et _ tapés par l'étudiant restent littéraux
            $motif = '%'.addcslashes(mb_substr($texte, 0, 80), '%_\\').'%';
            $requete->where(fn (Builder $q) => $q
                ->where('position', 'like', $motif)
                ->orWhere('description', 'like', $motif)
                ->orWhereHas('company', fn (Builder $c) => $c->where('name', 'like', $motif)));
        }
        foreach (['city' => 'city', 'sector' => 'sector'] as $cle => $colonne) {
            if (filled($filtres[$cle] ?? null)) {
                $requete->whereHas('company', fn (Builder $c) => $c->where($colonne, (string) $filtres[$cle]));
            }
        }
        if (filled($filtres['year_done'] ?? null)) {
            $requete->where('year_done', (int) $filtres['year_done']);
        }
        if (isset($filtres['is_paid']) && $filtres['is_paid'] !== '') {
            $requete->where('is_paid', filter_var($filtres['is_paid'], FILTER_VALIDATE_BOOLEAN));
        }
        if (filled($filtres['filiere'] ?? null)) {
            $requete->whereHas('filiere', fn (Builder $f) => $f->where('code', (string) $filtres['filiere']));
        }
    }

    /** Même forme que les retours récents de l'onglet Bibliothèque, plus l'état de publication pour l'auteur. */
    private function versMobile(InternshipReview $review, bool $auteur = false): array
    {
        return [
            'id' => $review->id,
            'company' => $review->company?->name,
            'city' => $review->company?->city,
            'sector' => $review->company?->sector,
            'filiere' => $review->filiere?->code,
            'position' => $review->position,
            'description' => Str::limit($review->description, 600),
            'rating' => $review->rating,
            'year_level' => $review->year_level,
            'year_done' => $review->year_done,
            'is_paid' => $review->is_paid,
            'has_photo' => filled($review->photo_path),
            'created_at' => $review->created_at?->toIso8601String(),
            ...($auteur ? ['publie' => $review->consent_at !== null] : []),
        ];
    }
}
