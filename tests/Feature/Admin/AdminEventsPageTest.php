<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\EventsIndex;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminEventsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_guest_is_redirected_from_admin_events(): void
    {
        $this->get(route('admin.events.index'))
            ->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_admin_events(): void
    {
        $student = User::factory()->create(['email' => 'student-events@hestim.ma']);

        $this->actingAs($student)
            ->get(route('admin.events.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_events_page(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin-events@hestim.ma']);

        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('Gestion des événements', false)
            ->assertSeeLivewire('admin.events-index');
    }

    public function test_admin_can_create_event_from_livewire(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'create-event@hestim.ma']);
        $startsAt = now()->addWeek()->format('Y-m-d\TH:i');

        Livewire::actingAs($admin)
            ->test(EventsIndex::class)
            ->call('openCreateForm')
            ->set('formTitle', 'Hackathon HESTIM 2026')
            ->set('formDescription', '48 h pour innover autour des villes intelligentes.')
            ->set('formStartsAt', $startsAt)
            ->set('formLocation', 'Bâtiment Innovation')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('events', [
            'title' => 'Hackathon HESTIM 2026',
            'location' => 'Bâtiment Innovation',
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_can_update_event(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'update-event@hestim.ma']);
        $event = Event::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Forum stages',
            'starts_at' => now()->addDays(5),
        ]);

        Livewire::actingAs($admin)
            ->test(EventsIndex::class)
            ->call('openEditForm', $event->id)
            ->set('formTitle', 'Forum entreprises et stages')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Forum entreprises et stages',
        ]);
    }

    public function test_admin_can_delete_event(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'delete-event@hestim.ma']);
        $event = Event::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Conférence IA',
            'starts_at' => now()->addDays(2),
        ]);

        Livewire::actingAs($admin)
            ->test(EventsIndex::class)
            ->call('openDeleteModal', $event->id)
            ->call('confirmDelete')
            ->assertHasNoErrors();

        $this->assertSoftDeleted($event);
    }

    public function test_admin_search_filters_events(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'search-admin-events@hestim.ma']);

        Event::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Soutenance développement web',
            'starts_at' => now()->addDays(3),
        ]);
        Event::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Portes ouvertes campus',
            'starts_at' => now()->addDays(4),
        ]);

        Livewire::actingAs($admin)
            ->test(EventsIndex::class)
            ->set('search', 'Soutenance')
            ->assertSee('Soutenance développement web')
            ->assertDontSee('Portes ouvertes campus');
    }

    public function test_web_store_redirects_with_success_message(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'post-event@hestim.ma']);

        $this->actingAs($admin)
            ->post(route('admin.events.store'), [
                'title' => 'Séminaire méthodologie',
                'description' => 'Conseils pour réussir son stage.',
                'starts_at' => now()->addWeek()->toDateTimeString(),
                'location' => 'Salle 105',
            ])
            ->assertRedirect(route('admin.events.index'))
            ->assertSessionHas('success');
    }

    public function test_json_endpoint_lists_events_for_admin(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'json-admin-events@hestim.ma']);

        Event::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Journée portes ouvertes',
            'starts_at' => now()->addMonth(),
        ]);

        $this->actingAs($admin)
            ->getJson(route('admin.events.index'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Journée portes ouvertes']);
    }

    public function test_admin_can_upload_event_image(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'image-event@hestim.ma']);
        $image = UploadedFile::fake()->create('affiche.jpg', 100, 'image/jpeg');
        $startsAt = now()->addWeek()->format('Y-m-d\TH:i');

        Livewire::actingAs($admin)
            ->test(EventsIndex::class)
            ->call('openCreateForm')
            ->set('formTitle', 'Forum entreprises')
            ->set('formStartsAt', $startsAt)
            ->set('formImage', $image)
            ->call('save')
            ->assertHasNoErrors();

        $event = Event::query()->where('title', 'Forum entreprises')->first();
        $this->assertNotNull($event?->image_path);
        Storage::disk('public')->assertExists($event->image_path);
    }
}
