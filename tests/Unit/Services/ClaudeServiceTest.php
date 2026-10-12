<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AiKind;
use App\Exceptions\AiUnavailableException;
use App\Models\AiRecommendation;
use App\Models\User;
use App\Services\ClaudeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Appel à l'API Claude : délai borné, panne traduite en AiUnavailableException, trace de la
 * demande effacée après le délai de conservation.
 */
class ClaudeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.claude.key' => 'cle-de-test',
            'services.claude.url' => 'https://claude.test/v1/messages',
            'services.claude.model' => 'modele-de-test',
            'services.claude.timeout' => 7,
        ]);
    }

    public function test_a_suggestion_is_returned_and_recorded(): void
    {
        Http::fake(['claude.test/*' => Http::response([
            'model' => 'modele-de-test',
            'content' => [['type' => 'text', 'text' => 'Trois idées']],
            'usage' => ['output_tokens' => 12],
        ])]);
        $user = User::factory()->create();

        $response = app(ClaudeService::class)->suggest($user, AiKind::Project, 'Des idées de projet');

        $this->assertSame('Trois idées', $response['content'][0]['text']);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://claude.test/v1/messages'
            && $request->hasHeader('x-api-key', 'cle-de-test')
            && $request['model'] === 'modele-de-test'
            && $request['messages'][0]['content'] === 'Des idées de projet');
        $this->assertDatabaseHas('ai_recommendations', [
            'user_id' => $user->id,
            'kind' => 'project',
            'model' => 'modele-de-test',
            'tokens_used' => 12,
        ]);
    }

    public function test_an_api_error_becomes_unavailable_and_records_nothing(): void
    {
        Http::fake(['claude.test/*' => Http::response(['error' => ['type' => 'overloaded_error']], 529)]);

        try {
            app(ClaudeService::class)->suggest(User::factory()->create(), AiKind::Other, 'Bonjour');
            $this->fail('Une panne de l\'API doit lever AiUnavailableException');
        } catch (AiUnavailableException) {
            $this->assertSame(0, AiRecommendation::query()->count());
        }
    }

    public function test_a_timeout_becomes_unavailable(): void
    {
        Http::fake(fn () => throw new ConnectionException('Délai dépassé'));

        $this->expectException(AiUnavailableException::class);
        app(ClaudeService::class)->suggest(User::factory()->create(), AiKind::Other, 'Bonjour');
    }

    public function test_without_a_key_the_api_is_not_called(): void
    {
        config(['services.claude.key' => null]);
        Http::fake();

        try {
            app(ClaudeService::class)->suggest(User::factory()->create(), AiKind::Other, 'Bonjour');
            $this->fail('Sans clé, le service doit se déclarer indisponible');
        } catch (AiUnavailableException) {
            Http::assertNothingSent();
        }
    }

    public function test_old_requests_are_pruned_after_the_retention_period(): void
    {
        $user = User::factory()->create();
        $trace = fn (int $ageInDays): AiRecommendation => AiRecommendation::query()->forceCreate([
            'user_id' => $user->id,
            'kind' => AiKind::Project->value,
            'prompt' => 'Étudiant en 4e année, intéressé par la robotique',
            'response' => ['content' => []],
            'created_at' => now()->subDays($ageInDays),
        ]);
        $old = $trace(120);
        $recent = $trace(10);

        config(['studylib.ai.retention_days' => 90]);
        Artisan::call('model:prune', ['--model' => [AiRecommendation::class]]);

        $this->assertDatabaseMissing('ai_recommendations', ['id' => $old->id]);
        $this->assertDatabaseHas('ai_recommendations', ['id' => $recent->id]);

        // 0 : conservation sans limite
        $ancient = $trace(900);
        config(['studylib.ai.retention_days' => 0]);
        Artisan::call('model:prune', ['--model' => [AiRecommendation::class]]);
        $this->assertDatabaseHas('ai_recommendations', ['id' => $ancient->id]);
    }
}
