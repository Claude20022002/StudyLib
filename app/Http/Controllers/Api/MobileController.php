<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModuleResource;
use App\Models\Document;
use App\Models\InternshipReview;
use App\Models\Module;
use App\Models\ProjectIdea;
use App\Services\DocumentService;
use App\Services\DownloadService;
use App\Services\InternshipReviewService;
use App\Services\ProjectIdeaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Points d'entrée propres à l'application mobile (le reste réutilise les contrôleurs du web).
 */
class MobileController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloads,
        private readonly DocumentService $documents,
        private readonly InternshipReviewService $reviews,
    ) {}

    /** Module d'une séance Planner : filière (code) et code du module, comme dans Planner. */
    public function module(string $filiere, string $code): ModuleResource
    {
        $module = Module::query()
            ->where('code', $code)
            ->whereHas('filiere', fn ($q) => $q->where('code', $filiere))
            ->firstOrFail();

        return ModuleResource::make($module);
    }

    /**
     * Recherche de la bibliothèque mobile : documents publiés dont le titre ou le module contient le
     * texte (mêmes règles de visibilité que le site), en version légère, 20 au plus.
     */
    public function searchDocuments(Request $request): JsonResponse
    {
        $q = trim((string) $request->validate(['q' => ['required', 'string', 'min:2', 'max:80']])['q']);

        $resultats = $this->documents->browse(['q' => $q], 20)->getCollection()->map(fn (Document $document): array => [
            'id' => $document->id,
            'title' => $document->title,
            'type' => $document->type,
            'mime_type' => $document->mime_type,
            'file_size' => $document->file_size,
            'year_concern' => $document->year_concern,
            'module' => $document->module ? ['code' => $document->module->code, 'name' => $document->module->name] : null,
        ]);

        return response()->json(['data' => $resultats->values()]);
    }

    /**
     * Idées de projets pour l'application : recherche facultative, 12 par page, champs utiles
     * seulement (ni l'auteur ni son e-mail).
     */
    public function projectIdeas(Request $request, ProjectIdeaService $ideas): JsonResponse
    {
        $filtres = $request->validate(['q' => ['nullable', 'string', 'max:80'], 'page' => ['nullable', 'integer', 'min:1']]);
        $page = $ideas->search(['q' => trim((string) ($filtres['q'] ?? ''))]);

        return response()->json([
            'data' => $page->getCollection()->map(fn (ProjectIdea $idea): array => [
                'id' => $idea->id,
                'title' => $idea->title,
                'description' => $idea->description,
                'level' => $idea->level,
                'difficulty' => $idea->difficulty,
                'estimated_weeks' => $idea->estimated_weeks,
                'filiere' => $idea->filiere ? ['code' => $idea->filiere->code, 'name' => $idea->filiere->name] : null,
            ])->values(),
            'page' => $page->currentPage(),
            'has_more' => $page->hasMorePages(),
        ]);
    }

    /** Derniers retours de stage, en version légère (pas de pagination par entreprise). */
    public function recentInternshipReviews(): JsonResponse
    {
        return response()->json(['data' => $this->reviews->recentForMobile()]);
    }

    /**
     * Photo d'un retour de stage. Servie par l'API (et non par un lien public) : seuls les
     * comptes connectés la voient ; le téléphone la garde en cache une journée.
     */
    public function internshipReviewPhoto(InternshipReview $review): Response
    {
        $photo = $this->reviews->photo($review);
        abort_if($photo === null, 404);

        return response($photo['content'], 200, [
            'Content-Type' => $photo['mime'],
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Téléchargement : l'application reçoit une URL signée de courte durée (5 minutes) au lieu
     * d'une redirection, et le téléchargement est compté comme sur le web.
     */
    public function download(Request $request, Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $this->downloads->record($document, $request->user()?->getKey(), $request->ip(), (string) $request->userAgent());

        return response()->json([
            'url' => $this->documents->temporaryDownloadUrl($document),
            'expires_in' => 300,
        ]);
    }
}
