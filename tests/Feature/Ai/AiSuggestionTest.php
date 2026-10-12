<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * POST /ai/suggestions : une panne de l'IA donne un message clair (503), jamais une erreur 500 ;
 * les idées de projet générées restent réservées aux étudiants, comme sur la page Projets.
 */
class AiSuggestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.claude.key' => 'cle-de-test', 'services.claude.url' => 'https://claude.test/v1/messages']);
    }

    private function user(UserRole $role): User
    {
        return User::factory()->create(['role' => $role->value, 'email_verified_at' => now()]);
    }

    public function test_an_unavailable_assistant_answers_503_with_a_message(): void
    {
        Http::fake(['claude.test/*' => Http::response([], 500)]);

        $this->actingAs($this->user(UserRole::Student))
            ->postJson(route('ai.suggestions'), ['kind' => 'other', 'prompt' => 'Bonjour'])
            ->assertStatus(503)
            ->assertJsonPath('message', "L'assistant est momentanément indisponible. Réessayez dans un instant.");
    }

    public function test_a_student_gets_the_suggestion(): void
    {
        Http::fake(['claude.test/*' => Http::response(['content' => [['type' => 'text', 'text' => 'Réponse']]])]);

        $this->actingAs($this->user(UserRole::Student))
            ->postJson(route('ai.suggestions'), ['kind' => 'project', 'prompt' => 'Des idées'])
            ->assertOk()
            ->assertJsonPath('content.0.text', 'Réponse');
    }

    public function test_a_teacher_cannot_generate_project_ideas_through_the_endpoint(): void
    {
        Http::fake();

        $this->actingAs($this->user(UserRole::Teacher))
            ->postJson(route('ai.suggestions'), ['kind' => 'project', 'prompt' => 'Des idées'])
            ->assertForbidden();
        Http::assertNothingSent();
    }
}
