<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\AiUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\InternshipOffer;
use App\Services\Offres\ErreurLecture;
use App\Services\Offres\ExtracteurOffre;
use App\Services\Offres\LecteurPage;
use App\Services\Offres\OffreDepuisTexte;
use App\Services\Offres\OffresService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Offres de stage partagées par un lien (application, jeton Planner en Bearer) :
 *  - POST /offres/analyser { url }       lit la page et rend les champs pré-remplis ;
 *  - POST /offres/analyser-texte { texte } met en forme un texte collé avec l'IA ;
 *  - POST /offres                        publie (après relecture par l'utilisateur) ;
 *  - GET  /offres, /offres/miennes, /offres/disponibilite ; DELETE /offres/{id} (auteur, admin).
 */
class InternshipOfferController extends Controller
{
    public function __construct(private readonly OffresService $offres) {}

    public function analyser(Request $request, LecteurPage $lecteur, ExtracteurOffre $extracteur): JsonResponse
    {
        $url = $request->validate(['url' => ['required', 'string', 'max:2048', 'url:https']])['url'];
        if (LecteurPage::refuse($url)) {
            return response()->json(['source' => 'refuse', 'message' => 'Ce site ne permet pas la lecture automatique : collez le texte de l’offre.', 'champs' => []]);
        }
        try {
            $resultat = $extracteur->extraire($lecteur->lire($url));
        } catch (ErreurLecture $e) {
            return response()->json(['message' => $e->getMessage(), 'refuse' => $e->refuse], 422);
        }

        return response()->json($resultat);
    }

    public function analyserTexte(Request $request, OffreDepuisTexte $ia): JsonResponse
    {
        $texte = $request->validate(['texte' => ['required', 'string', 'min:80', 'max:'.OffreDepuisTexte::TEXTE_MAX]])['texte'];
        try {
            return response()->json(['source' => 'texte', 'champs' => $ia->mettreEnForme($request->user(), $texte)]);
        } catch (AiUnavailableException) {
            return response()->json(['message' => 'La mise en forme automatique est indisponible : remplissez le formulaire.'], 503);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $champs = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'company_name' => ['required', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:120'],
            'type' => ['required', Rule::in(InternshipOffer::TYPES)],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'salary' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'apply_url' => ['required', 'string', 'max:2048', 'url:https'],
            'source' => ['required', Rule::in(['page', 'texte', 'manuel'])],
        ]);
        $offre = $this->offres->publier($request->user(), array_map(fn ($v) => is_string($v) ? trim(strip_tags($v)) : $v, $champs));

        return response()->json($this->offres->versMobile($offre, true), 201);
    }

    public function index(Request $request): JsonResponse
    {
        $filtres = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', Rule::in(InternshipOffer::TYPES)],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        return response()->json([...$this->offres->lister($filtres)->toArray(), 'villes' => $this->offres->villes()]);
    }

    public function mine(Request $request): JsonResponse
    {
        return response()->json($this->offres->miennes($request->user()));
    }

    public function disponibilite(): JsonResponse
    {
        return response()->json(['ia' => filled(config('services.claude.key'))]);
    }

    public function destroy(Request $request, string $offre): JsonResponse
    {
        $trouvee = InternshipOffer::query()->whereKey($offre)->first();
        abort_unless($trouvee && ($trouvee->user_id === $request->user()->getKey() || $request->user()->isAdmin()), 404);
        $trouvee->delete();

        return response()->json(status: 204);
    }
}
