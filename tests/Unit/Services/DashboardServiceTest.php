<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\UserRole;
use App\Models\Document;
use App\Models\Event;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_overview_returns_kpis_for_user_filiere(): void
    {
        $filiere = Filiere::factory()->create();
        $user = User::factory()->create([
            'filiere_id' => $filiere->id,
            'year_level' => 3,
            'role' => UserRole::Student,
        ]);
        $module = Module::factory()->create(['filiere_id' => $filiere->id]);

        Document::factory()->count(2)->create([
            'module_id' => $module->id,
            'type' => DocumentType::Examen,
            'status' => DocumentStatus::Approved,
            'created_at' => now(),
        ]);

        Event::factory()->count(2)->create(['starts_at' => now()->addDays(2)]);

        $overview = app(DashboardService::class)->overview($user);

        $this->assertSame('Examens disponibles', $overview['kpis'][1]['label']);
        $this->assertSame(2, $overview['kpis'][1]['value']);
        $this->assertSame(2, $overview['kpis'][3]['value']);
        $this->assertArrayHasKey('profile_completion', $overview);
    }
}
