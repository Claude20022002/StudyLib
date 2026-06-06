<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct(
        private readonly ModerationService $moderation,
    ) {
    }

    public function index(): JsonResponse
    {
        $this->authorize('moderate', Document::class);

        return response()->json($this->moderation->queue());
    }

    public function approve(Document $document): JsonResponse
    {
        $this->authorize('moderate', Document::class);

        return response()->json($this->moderation->approve($document));
    }

    public function reject(Request $request, Document $document): JsonResponse
    {
        $this->authorize('moderate', Document::class);

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        return response()->json(
            $this->moderation->reject($document, $validated['reason'] ?? null),
        );
    }
}
