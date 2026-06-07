<?php

declare(strict_types=1);

namespace Tests\Feature\Event;

use App\Livewire\Events\Index;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EventsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_events_page(): void
    {
        $this->get(route('events.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_events_page(): void
    {
        $user = User::factory()->create(['email' => 'events@hestim.ma']);

        $this->actingAs($user)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Agenda du campus', false)
            ->assertSeeLivewire('events.index');
    }

    public function test_events_page_lists_events_in_current_month(): void
    {
        $user = User::factory()->create(['email' => 'list-events@hestim.ma']);
        $startsAt = now()->setTime(14, 0);

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Hackathon HESTIM 2026',
            'starts_at' => $startsAt,
            'location' => 'Bâtiment Innovation',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee('Hackathon HESTIM 2026')
            ->assertSee('Bâtiment Innovation');
    }

    public function test_events_search_filters_by_title(): void
    {
        $user = User::factory()->create(['email' => 'search-events@hestim.ma']);
        $month = now();

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Conférence IA dans l\'industrie',
            'starts_at' => $month->copy()->day(10)->setTime(16, 0),
        ]);
        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Forum entreprises et stages',
            'starts_at' => $month->copy()->day(12)->setTime(14, 0),
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('search', 'Conférence')
            ->assertSee('Conférence IA dans l\'industrie', false)
            ->assertDontSee('Forum entreprises et stages');
    }

    public function test_user_can_open_event_detail_drawer(): void
    {
        $user = User::factory()->create(['email' => 'detail-events@hestim.ma']);
        $event = Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Soutenance projet Web',
            'description' => 'Présentations des projets de la promo L3.',
            'starts_at' => now()->addDays(3)->setTime(10, 30),
            'location' => 'Salle 204',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('openDetail', $event->id)
            ->assertSet('detailOpen', true)
            ->assertSee('Détail de l\'événement', false)
            ->assertSee('Présentations des projets de la promo L3.');
    }

    public function test_user_can_navigate_to_previous_month(): void
    {
        $user = User::factory()->create(['email' => 'nav-events@hestim.ma']);
        $current = now();

        Livewire::actingAs($user)
            ->test(Index::class)
            ->set('year', (int) $current->year)
            ->set('monthNum', (int) $current->month)
            ->call('previousMonth')
            ->assertSet('monthNum', (int) $current->copy()->subMonth()->month);
    }

    public function test_json_endpoint_still_returns_upcoming_events(): void
    {
        $user = User::factory()->create(['email' => 'json-events@hestim.ma']);

        Event::factory()->create([
            'user_id' => $user->id,
            'title' => 'Portes ouvertes campus',
            'starts_at' => now()->addWeek(),
        ]);

        $this->actingAs($user)
            ->getJson(route('events.index'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Portes ouvertes campus']);
    }
}
