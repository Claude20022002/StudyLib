<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\UserRole;
use App\Livewire\ProjectIdeas\Index as ProjectIdeasIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Espace de l'enseignant : seulement ce qui lui sert pour accompagner les étudiants (pas
 * d'événements étudiants, pas de suggestions pour le CV), et des ressources qu'il partage hors
 * de ses classes (exercices, articles).
 */
class TeacherSpaceTest extends TestCase
{
    use RefreshDatabase;

    private function teacher(): User
    {
        return User::factory()->create(['role' => UserRole::Teacher->value, 'email_verified_at' => now()]);
    }

    private function student(): User
    {
        return User::factory()->create(['role' => UserRole::Student->value, 'email_verified_at' => now()]);
    }

    public function test_teacher_dashboard_has_no_student_events_nor_cv_suggestions(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recommandés pour vous', false)
            ->assertDontSee('Événements proches', false)
            ->assertDontSee('Suggestion IA', false)
            ->assertDontSee(route('events.index'), false);
    }

    public function test_student_still_sees_events_in_the_menu(): void
    {
        $this->actingAs($this->student())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Événements proches', false)
            ->assertSee(route('events.index'), false);
    }

    public function test_teacher_is_sent_back_from_the_events_page(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('events.index'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($this->student())
            ->get(route('events.index'))
            ->assertOk();
    }

    public function test_teacher_can_propose_a_project_but_gets_no_generated_ideas(): void
    {
        $teacher = $this->teacher();

        $this->actingAs($teacher)
            ->get(route('project-ideas.index'))
            ->assertOk()
            ->assertSee('Proposez un projet aux étudiants', false)
            ->assertSee('Proposer un projet', false)
            ->assertDontSee('Idées IA', false)
            ->assertDontSee('id="ai-module"', false);

        Livewire::actingAs($teacher)
            ->test(ProjectIdeasIndex::class)
            ->call('generateAiIdeas')
            ->assertForbidden();

        $this->actingAs($this->student())
            ->get(route('project-ideas.index'))
            ->assertOk()
            ->assertSee('Idées IA', false)
            ->assertSee('id="ai-module"', false);
    }

    public function test_exercises_and_articles_are_document_types(): void
    {
        $this->assertSame('Exercice', DocumentType::from('exercice')->label());
        $this->assertSame('Article', DocumentType::from('article')->label());
        $this->assertTrue(UserRole::Teacher->publishesWithoutModeration());
        $this->assertSame('approved', DocumentStatus::Approved->value);
    }
}
