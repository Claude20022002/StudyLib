<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModuleResource;
use App\Models\Document;
use App\Models\Module;
use App\Services\DocumentService;
use App\Services\DownloadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Points d'entrée propres à l'application mobile (le reste réutilise les contrôleurs du web).
 */
class MobileController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloads,
        private readonly DocumentService $documents,
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
