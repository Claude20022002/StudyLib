<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Event;
use App\Models\User;
use App\Services\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_create_stores_event_image(): void
    {
        $admin = User::factory()->create(['email' => 'admin@hestim.ma']);
        $image = UploadedFile::fake()->create('forum.jpg', 100, 'image/jpeg');

        $event = app(EventService::class)->create($admin, [
            'title' => 'Forum entreprises',
            'starts_at' => now()->addWeek()->toDateTimeString(),
            'location' => 'Hall central',
        ], $image);

        $this->assertNotNull($event->image_path);
        Storage::disk('public')->assertExists($event->image_path);
    }

    public function test_delete_removes_event_image(): void
    {
        Storage::disk('public')->put('events/poster.jpg', 'binary');

        $event = Event::factory()->create([
            'image_path' => 'events/poster.jpg',
        ]);

        app(EventService::class)->delete($event);

        Storage::disk('public')->assertMissing('events/poster.jpg');
        $this->assertSoftDeleted($event);
    }
}
