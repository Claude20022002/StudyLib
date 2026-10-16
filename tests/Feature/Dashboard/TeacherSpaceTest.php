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

    /** Lot E-9 : l'enseignant a son tableau de bord, son profil et ses notifications dans Planner. */
    public function test_teacher_is_sent_to_planner_for_dashboard_profile_and_notifications(): void
    {
        $teacher = $this->teacher();

        $this->actingAs($teacher)->get(route('dashboard'))->assertRedirect('/tableau');
        $this->actingAs($teacher)->get(route('profile.show'))->assertRedirect('/parametres');
        $this->actingAs($teacher)->get(route('notifications.index'))->assertRedirect('/notifications');
        // L'accueil de StudyLib l'amène sur les documents
        $this->actingAs($teacher)->get(route('home'))->assertRedirect(route('documents.index'));

        // Les réponses JSON (cloche, application) ne changent pas
        $this->actingAs($teacher)->getJson(route('notifications.index'))->assertOk();
        $this->actingAs($teacher)->getJson(route('profile.show'))->assertOk();
    }

    public function test_teacher_library_has_no_dashboard_nor_profile_entry_and_links_to_planner(): void
    {
        $this->actingAs($this->teacher())
            ->get(route('documents.index'))
            ->assertOk()
            ->assertDontSee('href="'.route('dashboard').'"', false)
            ->assertDontSee('href="'.route('profile.show').'"', false)
            ->assertDontSee(route('events.index'), false)
            ->assertSee('href="/parametres"', false)
            ->assertSee('href="/notifications"', false);
    }

    public function test_student_keeps_dashboard_profile_and_notifications_in_studylib(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get(route('dashboard'))->assertOk();
        $this->actingAs($student)->get(route('home'))->assertRedirect(route('dashboard'));
        $this->actingAs($student)
            ->get(route('documents.index'))
            ->assertOk()
            ->assertSee('href="'.route('profile.show').'"', false)
            ->assertSee('href="'.route('notifications.index').'"', false);
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
