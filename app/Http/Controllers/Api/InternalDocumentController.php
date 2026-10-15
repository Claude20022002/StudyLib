<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\DocumentStatus;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\DownloadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Lien de téléchargement demandé par le serveur de Planner pour un étudiant de la classe (jeton de
 * service, vérifié par le middleware `planner.service`). Planner a contrôlé les droits ; StudyLib
 * vérifie seulement que le jeton vise bien ce document.
 */
class InternalDocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
        private readonly DownloadService $downloads,
    ) {}

    public function link(Request $request, string $document): JsonResponse
    {
        $claims = (array) $request->attributes->get('planner_service', []);
        abort_unless(($claims['doc'] ?? null) === $document, 403);

        $found = Document::withClassDocuments()->whereKey($document)->first();
        abort_if($found === null || $found->status !== DocumentStatus::Approved, 404);

        // L'étudiant est connu de StudyLib s'il y est déjà venu ; sinon le téléchargement reste anonyme
        $userId = is_string($claims['sub'] ?? null) ? User::query()->where('planner_id', $claims['sub'])->value('id') : null;
        $this->downloads->record($found, $userId, null, 'planner');

        return response()->json(['url' => $this->documents->temporaryDownloadUrl($found), 'expires_in' => 300]);
    }
}
