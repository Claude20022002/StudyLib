<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $validated = $this->validatePayload($request);

        return response()->json(
            $this->events->create($request->user(), $validated),
            201,
        );
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $validated = $this->validatePayload($request);

        return response()->json($this->events->update($event, $validated));
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return response()->json(status: 204);
    }

    /** @return array<string, mixed> */
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'location' => ['nullable', 'string', 'max:200'],
            'image_path' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
