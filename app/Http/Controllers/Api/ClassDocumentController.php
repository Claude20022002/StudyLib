<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\DocumentType;
use App\Enums\DocumentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\Document\StoreClassDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\Module;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Documents de classe (plan espace-de-cours.md, § 3) : appelés par le serveur de Planner avec le
 * jeton de l'enseignant, jamais par un navigateur. StudyLib stocke et contrôle le fichier ; Planner
 * le range dans une classe et en donne l'accès aux étudiants.
 */
class ClassDocumentController extends Controller
{
    public function __construct(private readonly DocumentService $documents) {}

    public function store(StoreClassDocumentRequest $request): JsonResponse
    {
        $module = Module::query()
            ->where('code', $request->validated('module'))
            ->whereHas('filiere', fn ($query) => $query->where('code', $request->validated('filiere')))
            ->first();
        abort_if($module === null, 422, 'Module inconnu de la Bibliothèque');

        $document = $this->documents->upload($request->user(), $request->file('file'), [
            'module_id' => $module->getKey(),
            'type' => $request->validated('type') ?? DocumentType::Cours->value,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
        ], DocumentVisibility::ClassOnly);

        return DocumentResource::make($document->load('module'))->response()->setStatusCode(201);
    }

    /**
     * Un document de classe n'est rendu qu'à son auteur ou à l'administration ; un document de la
     * Bibliothèque, à qui peut le voir (publié ou à lui). Tout le reste : 404, sans rien révéler.
     */
    public function show(Request $request, string $document): DocumentResource
    {
        return DocumentResource::make($this->find($request->user(), $document)->load('module'));
    }

    /** Publier dans la Bibliothèque : le même document passe à toute l'école, sans copie. */
    public function publish(Request $request, string $document): DocumentResource
    {
        $found = $this->findOwnClassDocument($request->user(), $document);
        $found->forceFill(['visibility' => DocumentVisibility::School->value])->save();

        return DocumentResource::make($found->load('module'));
    }

    public function destroy(Request $request, string $document): JsonResponse
    {
        $this->documents->delete($this->findOwnClassDocument($request->user(), $document));

        return response()->json(status: 204);
    }

    private function find(User $user, string $id): Document
    {
        $document = Document::withClassDocuments()->whereKey($id)->first();
        $allowed = $document !== null && ($document->isClassOnly()
            ? $this->ownsOrAdmin($user, $document)
            : $user->can('view', $document));
        abort_unless($allowed, 404);

        return $document;
    }

    private function findOwnClassDocument(User $user, string $id): Document
    {
        $document = $this->find($user, $id);
        abort_unless($document->isClassOnly() && $this->ownsOrAdmin($user, $document), 404);

        return $document;
    }

    private function ownsOrAdmin(User $user, Document $document): bool
    {
        return $document->user_id === $user->getKey() || $user->isAdmin();
    }
}
