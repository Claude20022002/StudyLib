<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('create', Event::class);

        if ($request->expectsJson()) {
            return response()->json($this->events->adminList());
        }

        return view('pages.admin.events.index');
    }

    public function store(StoreEventRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Event::class);

        $event = $this->events->create(
            $request->user(),
            $request->validated(),
            $request->file('image'),
        );

        if ($request->expectsJson()) {
            return response()->json($event, 201);
        }

        return redirect()
            ->route('admin.events.index')
            ->with('success', '« '.$event->title.' » a été créé.');
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $event);

        $updated = $this->events->update($event, $request->validated(), $request->file('image'));

        if ($request->expectsJson()) {
            return response()->json($updated);
        }

        return redirect()
            ->route('admin.events.index')
            ->with('success', '« '.$updated->title.' » a été mis à jour.');
    }

    public function destroy(Request $request, Event $event): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $event);

        $title = $event->title;
        $this->events->delete($event);

        if ($request->expectsJson()) {
            return response()->json(status: 204);
        }

        return redirect()
            ->route('admin.events.index')
            ->with('success', '« '.$title.' » a été supprimé.');
    }
}
