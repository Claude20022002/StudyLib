<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentService $documents,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module_id' => ['required', 'uuid'],
            'type' => ['nullable', 'string'],
        ]);

        $type = isset($validated['type']) ? DocumentType::tryFrom($validated['type']) : null;

        return response()->json(
            $this->documents->listByModule($validated['module_id'], $type),
        );
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        return response()->json($document->load(['author', 'module']));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'module_id' => ['required', 'uuid'],
            'type' => ['required', 'string'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'year_concern' => ['nullable', 'integer'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $document = $this->documents->upload(
            $request->user(),
            $request->file('file'),
            $validated,
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
