<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
    ) {}

    public function index(Request $request): View|AnonymousResourceCollection
    {
        if ($request->expectsJson()) {
            $validated = $request->validate([
                'module_id' => ['required', 'uuid', 'exists:modules,id'],
                'type' => ['nullable', 'string', Rule::enum(DocumentType::class)],
            ]);

            return DocumentResource::collection(
                $this->documents->listByModule(
                    $validated['module_id'],
                    isset($validated['type']) ? DocumentType::from($validated['type']) : null,
                ),
            );
        }

        return view('pages.documents.index', [
            'pageTitle' => $request->boolean('mine') ? 'Mes dépôts' : 'Bibliothèque',
        ]);
    }

    public function show(Document $document): DocumentResource
    {
        $this->authorize('view', $document);

        return DocumentResource::make($document->load(['author', 'module']));
    }

    public function store(StoreDocumentRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Document::class);

        $document = $this->documents->upload(
            $request->user(),
            $request->file('file'),
            $request->validated(),
        );

        if ($request->expectsJson()) {
            return DocumentResource::make($document)->response()->setStatusCode(201);
        }

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document envoyé. Il sera visible après modération.');
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $this->documents->delete($document);

        return response()->json(status: 204);
    }
}
