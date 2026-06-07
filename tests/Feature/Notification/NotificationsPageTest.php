<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\Livewire\Notifications\Index;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_notifications_page(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_notifications_page(): void
    {
        $user = User::factory()->create(['email' => 'notif@hestim.ma']);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Vos notifications', false)
            ->assertSeeLivewire('notifications.index');
    }

    public function test_notifications_page_lists_user_notifications(): void
    {
        $user = User::factory()->create(['email' => 'list-notif@hestim.ma']);

        Notification::query()->create([
            'type' => 'document.reviewed',
            'user_id' => $user->id,
            'data' => [
                'title' => 'Annale BDD 2024',
                'status' => 'approved',
            ],
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->assertSee('Document approuvé')
            ->assertSee('Annale BDD 2024');
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create(['email' => 'read-notif@hestim.ma']);

        $notification = Notification::query()->create([
            'type' => 'document.reviewed',
            'user_id' => $user->id,
            'data' => [
                'title' => 'TD Réseaux',
                'status' => 'rejected',
                'reason' => 'Qualité insuffisante',
            ],
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('markAsRead', $notification->id)
            ->assertHasNoErrors();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_json_endpoint_still_returns_unread_notifications(): void
    {
        $user = User::factory()->create(['email' => 'json-notif@hestim.ma']);

        Notification::query()->create([
            'type' => 'document.reviewed',
            'user_id' => $user->id,
            'data' => [
                'title' => 'Cours Java',
                'status' => 'approved',
            ],
        ]);

        $this->actingAs($user)
            ->getJson(route('notifications.index'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Cours Java']);
    }
}
