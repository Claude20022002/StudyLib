<?php

declare(strict_types=1);

namespace Tests\Feature\Internship;

use App\Models\Filiere;
use App\Models\InternshipReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\IssuesPlannerTokens;
use Tests\TestCase;

/**
 * Retours de stage pour l'application mobile : liste légère, photo d'illustration (envoi limité,
 * servie par l'API seulement) et ajout par l'administration (commande).
 */
class InternshipReviewPhotoTest extends TestCase
{
    use IssuesPlannerTokens, RefreshDatabase;

    /** Vraie image JPEG de 8 × 6 px (sans GD, Laravel ne sait pas en fabriquer). */
    private const JPEG = '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAA0JCgsKCA0LCgsODg0PEyAVExISEyccHhcgLikxMC4pLSwzOko+MzZGNywtQFdBRkxOUlNSMj5aYVpQYEpRUk//2wBDAQ4ODhMREyYVFSZPNS01T09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0//wAARCAAGAAgDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwDkqKKK9U4T/9k=';

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakePlanner();
        Storage::fake('minio');
    }

    /** Requête suivante sans jeton : la garde garde sinon l'utilisateur de la requête précédente */
    private function sansConnexion(): static
    {
        $this->app['auth']->forgetGuards();

        return $this->flushHeaders();
    }

    private function photo(string $nom = 'stage.jpg'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nom, base64_decode(self::JPEG));
    }

    private function avis(array $champs = []): array
    {
        return array_merge([
            'company_name' => 'Atelier Test',
            'company_city' => 'Casablanca',
            'position' => 'Stage de développement',
            'description' => 'Très formateur.',
            'rating' => 4,
            'year_done' => 2026,
            'consent' => true,
        ], $champs);
    }

    public function test_recent_list_is_light_and_only_shows_published_reviews(): void
    {
        InternshipReview::factory()->create(['consent_at' => now(), 'position' => 'Publié']);
        InternshipReview::factory()->create(['consent_at' => null, 'position' => 'Sans accord']);

        $reponse = $this->withToken($this->plannerToken())->getJson('/api/internship-reviews/recent')->assertOk();

        $this->assertSame(['Publié'], array_column($reponse->json('data'), 'position'));
        $this->assertSame(
            ['id', 'company', 'city', 'sector', 'filiere', 'position', 'description', 'rating', 'year_level', 'year_done', 'is_paid', 'has_photo'],
            array_keys($reponse->json('data.0')),
        );
        $this->sansConnexion()->getJson('/api/internship-reviews/recent')->assertUnauthorized();
    }

    public function test_a_review_can_be_shared_with_a_photo_served_by_the_api_only(): void
    {
        $cree = $this->withToken($this->plannerToken())
            ->post('/api/internship-reviews', $this->avis(['photo' => $this->photo()]), ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonMissingPath('photo_path');

        $avis = InternshipReview::query()->findOrFail($cree->json('id'));
        Storage::disk('minio')->assertExists($avis->photo_path);
        $this->assertTrue($this->withToken($this->plannerToken())->getJson('/api/internship-reviews/recent')->json('data.0.has_photo'));

        $photo = $this->withToken($this->plannerToken())->get("/api/internship-reviews/{$avis->id}/photo")->assertOk();
        $this->assertSame('image/jpeg', $photo->headers->get('Content-Type'));
        $this->assertStringContainsString('max-age=86400', (string) $photo->headers->get('Cache-Control'));
        $this->sansConnexion()->get("/api/internship-reviews/{$avis->id}/photo", ['Accept' => 'application/json'])->assertUnauthorized();
    }

    public function test_photo_must_be_a_small_image(): void
    {
        $this->withToken($this->plannerToken())
            ->post('/api/internship-reviews', $this->avis(['photo' => UploadedFile::fake()->create('lourde.jpg', 3000, 'image/jpeg')]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('photo');
        $this->withToken($this->plannerToken())
            ->post('/api/internship-reviews', $this->avis(['photo' => UploadedFile::fake()->createWithContent('page.pdf', '%PDF-1.4 test')]), ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('photo');
        $this->assertSame(0, InternshipReview::query()->count());
    }

    public function test_a_review_without_photo_has_no_photo_route(): void
    {
        $avis = InternshipReview::factory()->create(['consent_at' => now()]);

        $this->withToken($this->plannerToken())->get("/api/internship-reviews/{$avis->id}/photo")->assertNotFound();
    }

    public function test_the_administration_can_add_a_consented_review_with_a_photo_once(): void
    {
        Filiere::factory()->create(['code' => 'IIIA']);
        $dossier = sys_get_temp_dir().DIRECTORY_SEPARATOR.'studylib-test-'.uniqid();
        mkdir($dossier);
        file_put_contents("{$dossier}/texte.txt", "Mise en service d'un protocole de communication.");
        file_put_contents("{$dossier}/robot.jpg", base64_decode(self::JPEG));
        $options = [
            '--company' => 'HESTIM FabLab',
            '--city' => 'Casablanca',
            '--position' => 'Stage en robotique',
            '--description-file' => "{$dossier}/texte.txt",
            '--rating' => 4,
            '--year-done' => 2026,
            '--year-level' => 3,
            '--filiere' => 'IIIA',
            '--photo' => "{$dossier}/robot.jpg",
        ];

        $this->artisan('studylib:add-internship-review', $options)->assertFailed();
        $this->artisan('studylib:add-internship-review', [...$options, '--consent' => true])->assertSuccessful();
        $this->artisan('studylib:add-internship-review', [...$options, '--consent' => true])->expectsOutputToContain('existe déjà')->assertSuccessful();

        $avis = InternshipReview::query()->with(['company', 'filiere'])->sole();
        $this->assertNull($avis->user_id);
        $this->assertSame('HESTIM FabLab', $avis->company->name);
        $this->assertSame('IIIA', $avis->filiere->code);
        $this->assertFalse($avis->is_paid);
        $this->assertNotNull($avis->consent_at);
        Storage::disk('minio')->assertExists($avis->photo_path);
    }
}
