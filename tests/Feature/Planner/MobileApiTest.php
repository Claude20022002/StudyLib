<?php

declare(strict_types=1);

namespace Tests\Feature\Planner;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\ProjectIdea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\IssuesPlannerTokens;
use Tests\TestCase;

/**
 * API JSON de l'application mobile, authentifiée par le jeton Planner en Bearer.
 */
class MobileApiTest extends TestCase
{
    use IssuesPlannerTokens, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakePlanner();
    }

    public function test_requires_a_planner_bearer_token(): void
    {
        $this->getJson('/api/events')->assertUnauthorized();
        $this->getJson('/api/modules/by-code/IIIA/IIIA-ML')->assertUnauthorized();
    }

    public function test_finds_a_module_by_the_planner_codes(): void
    {
        $filiere = Filiere::factory()->create(['code' => 'IIIA']);
        $module = Module::factory()->create(['filiere_id' => $filiere->id, 'code' => 'IIIA-ML']);

        $this->withToken($this->plannerToken())
            ->getJson('/api/modules/by-code/IIIA/IIIA-ML')
            ->assertOk()
            ->assertJsonPath('data.id', $module->id);
        $this->withToken($this->plannerToken())->getJson('/api/modules/by-code/IIIA/AUTRE')->assertNotFound();
        $this->withToken($this->plannerToken())->getJson('/api/modules/by-code/II%20IA/IIIA-ML')->assertNotFound();
    }

    public function test_download_returns_a_short_lived_signed_url_and_counts_it(): void
    {
        Storage::fake('minio');
        Storage::disk('minio')->buildTemporaryUrlsUsing(fn (string $path, $expiration) => 'https://minio.test/'.$path.'?expires='.$expiration->getTimestamp());
        $document = Document::factory()->create(['status' => DocumentStatus::Approved->value, 'file_path' => 'documents/a.pdf']);

        $this->withToken($this->plannerToken())
            ->postJson("/api/documents/{$document->id}/download")
            ->assertOk()
            ->assertJsonPath('expires_in', 300)
            ->assertJson(fn ($json) => $json->where('url', fn ($url) => str_starts_with($url, 'https://minio.test/documents/a.pdf'))->etc());
        $this->assertDatabaseHas('document_downloads', ['document_id' => $document->id]);
    }

    public function test_a_pending_document_of_someone_else_cannot_be_downloaded(): void
    {
        Storage::fake('minio');
        $document = Document::factory()->create(['status' => DocumentStatus::Pending->value]);

        $this->withToken($this->plannerToken())
            ->postJson("/api/documents/{$document->id}/download")
            ->assertForbidden();
    }

    public function test_course_supports_count_published_documents_and_link_to_the_library(): void
    {
        $filiere = Filiere::factory()->create(['code' => 'IIIA']);
        $module = Module::factory()->create(['filiere_id' => $filiere->id, 'code' => 'IIIA-ML']);
        Document::factory()->count(2)->create(['module_id' => $module->id, 'status' => DocumentStatus::Approved->value]);
        Document::factory()->create(['module_id' => $module->id, 'status' => DocumentStatus::Pending->value]);

        // Tableau de bord Planner : cookie d'accès sur la même origine
        $response = $this->withCredentials()->withUnencryptedCookie('access_token', $this->plannerToken())
            ->getJson('/modules/supports?codes[]=IIIA-ML&codes[]=INCONNU')
            ->assertOk()
            ->assertJsonPath('supports.IIIA-ML.documents', 2)
            ->assertJsonPath('supports.IIIA-ML.module_id', $module->id)
            ->assertJsonMissingPath('supports.INCONNU');
        $this->assertStringContainsString('module='.$module->id, $response->json('supports.IIIA-ML.url'));

        // Application mobile : Bearer
        $this->app['auth']->forgetGuards();
        $this->withToken($this->plannerToken())->getJson('/api/modules/supports?codes[]=IIIA-ML')->assertJsonPath('supports.IIIA-ML.documents', 2);
    }

    public function test_course_supports_validate_codes_and_require_a_session(): void
    {
        $this->getJson('/modules/supports?codes[]=IIIA-ML')->assertUnauthorized();
        $this->withToken($this->plannerToken())->getJson('/api/modules/supports?codes[]=bad%20code')->assertUnprocessable();
        $this->app['auth']->forgetGuards();
        $this->withToken($this->plannerToken())->getJson('/api/modules/supports?'.http_build_query(['codes' => array_map(fn ($i) => "M{$i}", range(1, 51))]))->assertUnprocessable();
    }

    public function test_library_search_finds_published_documents_by_title_or_module(): void
    {
        $module = Module::factory()->create(['name' => 'Big Data', 'code' => 'IIIA-BD']);
        Document::factory()->create(['module_id' => $module->id, 'title' => 'Introduction', 'status' => DocumentStatus::Approved->value]);
        Document::factory()->create(['title' => 'Résumé Hadoop', 'status' => DocumentStatus::Approved->value]);
        Document::factory()->create(['title' => 'Hadoop brouillon', 'status' => DocumentStatus::Pending->value]);

        $parTitre = $this->withToken($this->plannerToken())->getJson('/api/documents/search?q=hadoop')->assertOk();
        $this->assertSame(['Résumé Hadoop'], array_column($parTitre->json('data'), 'title'));
        $parModule = $this->withToken($this->plannerToken())->getJson('/api/documents/search?q=big%20data')->assertOk();
        $this->assertSame('IIIA-BD', $parModule->json('data.0.module.code'));
        $this->withToken($this->plannerToken())->getJson('/api/documents/search?q=a')->assertUnprocessable();
    }

    public function test_project_ideas_for_the_app_are_light_paginated_and_searchable(): void
    {
        ProjectIdea::factory()->count(13)->create();
        ProjectIdea::factory()->create(['title' => 'Robot suiveur de ligne']);

        $premiere = $this->withToken($this->plannerToken())->getJson('/api/project-ideas/mobile')->assertOk();
        $this->assertCount(12, $premiere->json('data'));
        $this->assertTrue($premiere->json('has_more'));
        $this->assertSame(['id', 'title', 'description', 'level', 'difficulty', 'estimated_weeks', 'filiere'], array_keys($premiere->json('data.0')));
        $this->assertStringNotContainsString('@', $premiere->getContent());

        $trouvee = $this->withToken($this->plannerToken())->getJson('/api/project-ideas/mobile?q=robot')->assertOk();
        $this->assertSame(['Robot suiveur de ligne'], array_column($trouvee->json('data'), 'title'));
    }
}
