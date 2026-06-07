<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json($this->events->upcoming());
        }

        return view('pages.events.index');
    }
}
