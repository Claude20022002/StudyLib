<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentService;
use App\Services\DownloadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentDownloadController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloads,
        private readonly DocumentService $documents,
    ) {
    }

    public function store(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('view', $document);

        $this->downloads->record(
            $document,
            $request->user()?->getKey(),
            $request->ip(),
            (string) $request->userAgent(),
        );

        return redirect()->away($this->documents->temporaryDownloadUrl($document));
    }
}
