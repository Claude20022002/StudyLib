<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\UserRole;
use App\Livewire\Dashboard\Index;
use App\Models\Document;
use App\Models\Event;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = $this->createStudent();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recommandés pour vous', false)
            ->assertSee('Événements proches', false)
            ->assertSee('Suggestion IA', false);
    }

    public function test_dashboard_livewire_loads_kpis_and_documents(): void
    {
        $filiere = Filiere::factory()->create(['name' => 'Génie Informatique', 'code' => 'GI']);
        $user = User::factory()->create([
            'email' => 'etudiant@hestim.ma',
            'role' => UserRole::Student,
            'filiere_id' => $filiere->id,
            'year_level' => 3,
        ]);
        $module = Module::factory()->create(['filiere_id' => $filiere->id, 'semester' => 5]);

        Document::factory()->create([
            'module_id' => $module->id,
            'type' => DocumentType::Cours,
            'status' => DocumentStatus::Approved,
            'title' => 'Modélisation relationnelle',
        ]);

        Event::factory()->create([
            'title' => 'Forum entreprises',
            'starts_at' => now()->addDays(3),
            'location' => 'Hall central',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('loadDashboard')
            ->assertSet('ready', true)
            ->assertSee('Bonjour')
            ->assertSee('Génie Informatique')
            ->assertSee('Modélisation relationnelle')
            ->assertSee('Forum entreprises');
    }

    public function test_dashboard_filter_limits_documents_by_type(): void
    {
        $filiere = Filiere::factory()->create();
        $user = User::factory()->create([
            'email' => 'filter@hestim.ma',
            'filiere_id' => $filiere->id,
        ]);
        $module = Module::factory()->create(['filiere_id' => $filiere->id]);

        Document::factory()->create([
            'module_id' => $module->id,
            'type' => DocumentType::Cours,
            'status' => DocumentStatus::Approved,
            'title' => 'Cours visible',
        ]);
        Document::factory()->create([
            'module_id' => $module->id,
            'type' => DocumentType::Examen,
            'status' => DocumentStatus::Approved,
            'title' => 'Examen visible',
        ]);

        Livewire::actingAs($user)
            ->test(Index::class)
            ->call('loadDashboard')
            ->call('setFilter', 'examen')
            ->assertSee('Examen visible')
            ->assertDontSee('Cours visible');
    }

    private function createStudent(): User
    {
        return User::factory()->create([
            'email' => 'dash@hestim.ma',
            'role' => UserRole::Student,
            'filiere_id' => Filiere::factory()->create()->id,
            'year_level' => 3,
        ]);
    }
}
