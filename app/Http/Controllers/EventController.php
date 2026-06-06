<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\EventService;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->events->upcoming());
    }
}
