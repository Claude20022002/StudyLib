<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectDocumentRequest;
use App\Models\Document;
use App\Services\ModerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct(
        private readonly ModerationService $moderation,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('moderate', Document::class);

        if ($request->expectsJson()) {
            return response()->json($this->moderation->queue());
        }

        return view('pages.admin.index', [
            'header' => 'Administration',
            'breadcrumb' => 'Modération des documents',
        ]);
    }

    public function approve(Request $request, Document $document): JsonResponse|RedirectResponse
    {
        $this->authorize('moderate', Document::class);

        $this->moderation->approve($document);

        if ($request->expectsJson()) {
            return response()->json($document->fresh());
        }

        return back()->with('success', 'Document approuvé.');
    }

    public function reject(RejectDocumentRequest $request, Document $document): JsonResponse|RedirectResponse
    {
        $this->authorize('moderate', Document::class);

        $this->moderation->reject($document, $request->validated('reason'));

        if ($request->expectsJson()) {
            return response()->json($document->fresh());
        }

        return back()->with('success', 'Document refusé.');
    }
}
