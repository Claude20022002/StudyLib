<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Http\Requests\Document\ListDocumentsRequest;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
    ) {
    }

    public function index(ListDocumentsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json(
            $this->documents->listByModule(
                $validated['module_id'],
                isset($validated['type']) ? DocumentType::from($validated['type']) : null,
            ),
        );
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        return response()->json($document->load(['author', 'module']));
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $document = $this->documents->upload(
            $request->user(),
            $request->file('file'),
            $request->validated(),
        );

        return response()->json($document, 201);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $this->documents->delete($document);

        return response()->json(status: 204);
    }
}
