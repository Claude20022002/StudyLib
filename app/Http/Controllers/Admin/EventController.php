<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function store(StoreEventRequest $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        return response()->json(
            $this->events->create(
                $request->user(),
                $request->validated(),
                $request->file('image'),
            ),
            201,
        );
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        return response()->json(
            $this->events->update($event, $request->validated(), $request->file('image')),
        );
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);

        $this->events->delete($event);

        return response()->json(status: 204);
    }
}
