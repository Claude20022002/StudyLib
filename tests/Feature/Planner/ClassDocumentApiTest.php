<?php

declare(strict_types=1);

namespace Tests\Feature\Planner;

use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\IssuesPlannerTokens;
use Tests\TestCase;

/**
 * Documents de classe (plan espace-de-cours.md, § 3) : déposés par le serveur de Planner avec le
 * jeton de l'enseignant, invisibles partout dans StudyLib, ouverts aux étudiants par un jeton de
 * service de Planner limité à un document.
 */
class ClassDocumentApiTest extends TestCase
{
    use IssuesPlannerTokens, RefreshDatabase;

    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakePlanner();
        Storage::fake('minio');
        Storage::disk('minio')->buildTemporaryUrlsUsing(fn (string $path, $expiration) => 'https://minio.test/'.$path);
        $filiere = Filiere::factory()->create(['code' => 'IIIA']);
        $this->module = Module::factory()->create(['filiere_id' => $filiere->id, 'code' => 'IIIA-NIS2']);
    }

    /** Chaque appel avec son jeton : le garde garde sinon l'utilisateur du premier appel du test */
    private function en(string $token): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    }

    private function enseignant(string $id = '7'): string
    {
        return $this->plannerToken(['sub' => $id, 'role' => 'enseignant', 'email' => "prof{$id}@hestim.ma"]);
    }

    /**
     * Un vrai fichier temporaire : son type est lu dans son contenu (finfo), comme en production.
     * Un faux fichier de Laravel (UploadedFile::fake) devinerait le type d'après le nom.
     */
    private function fichier(string $nom, string $contenu): UploadedFile
    {
        $chemin = tempnam(sys_get_temp_dir(), 'doc');
        file_put_contents($chemin, $contenu);

        return new UploadedFile($chemin, $nom, null, null, true);
    }

    private function pdf(): UploadedFile
    {
        return $this->fichier('cours.pdf', "%PDF-1.4\n1 0 obj << >> endobj\ntrailer << >>\n%%EOF\n");
    }

    /**
     * Comme Planner : le module de la classe dans l'adresse, le formulaire du navigateur (titre,
     * fichier) dans le corps.
     */
    private function deposer(?string $token = null, array $champs = [], array $module = []): TestResponse
    {
        $adresse = '/api/classe/documents?'.http_build_query($module + ['filiere' => 'IIIA', 'module' => 'IIIA-NIS2']);

        return $this->en($token ?? $this->enseignant())->post($adresse, $champs + [
            'title' => 'Chapitre 1',
            'file' => $this->pdf(),
        ], ['Accept' => 'application/json']);
    }

    private function serviceToken(string $doc, array $overrides = []): string
    {
        return JWT::encode(array_merge([
            'iss' => 'hestim-planner',
            'aud' => ['studylib-interne'],
            'scope' => 'document:lien',
            'doc' => $doc,
            'sub' => '42',
            'iat' => time(),
            'exp' => time() + 60,
        ], $overrides), $this->plannerPrivateKey, 'RS256', $this->plannerKid);
    }

    public function test_a_teacher_deposits_a_class_document_that_stays_out_of_the_library(): void
    {
        $id = $this->deposer()->assertCreated()->assertJsonPath('data.visibility', 'class')->json('data.id');

        $document = Document::withClassDocuments()->findOrFail($id);
        $this->assertTrue($document->isClassOnly());
        Storage::disk('minio')->assertExists($document->file_path);

        // Nulle part dans StudyLib : liste, fiche, recherche, page web
        $etudiant = $this->plannerToken();
        $this->en($etudiant)->getJson('/api/documents?module_id='.$this->module->id)->assertOk()->assertJsonMissing(['id' => $id]);
        $this->en($etudiant)->getJson("/api/documents/{$id}")->assertNotFound();
        $this->en($etudiant)->getJson('/api/documents/search?q=Chapitre')->assertJsonMissing(['id' => $id]);
        $this->en($etudiant)->postJson("/api/documents/{$id}/download")->assertNotFound();
        $this->assertSame(0, Document::query()->count());
    }

    public function test_only_teachers_deposit_and_only_real_files_of_an_accepted_type(): void
    {
        $this->deposer($this->plannerToken())->assertForbidden();
        $html = $this->fichier('cours.pdf', '<!DOCTYPE html><html><body><script>alert(1)</script></body></html>');
        $this->deposer(null, ['file' => $html])->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->deposer(null, [], ['module' => 'INCONNU'])->assertStatus(422);
        $this->deposer(null, [], ['filiere' => '../IIIA'])->assertUnprocessable();
    }

    public function test_a_class_document_is_shown_published_or_removed_by_its_author_only(): void
    {
        $id = $this->deposer()->json('data.id');
        $autre = $this->enseignant('8');

        $this->en($this->enseignant())->getJson("/api/classe/documents/{$id}")->assertOk();
        $this->en($autre)->getJson("/api/classe/documents/{$id}")->assertNotFound();
        $this->en($autre)->patchJson("/api/classe/documents/{$id}/publier")->assertNotFound();
        $this->en($autre)->deleteJson("/api/classe/documents/{$id}")->assertNotFound();

        $this->en($this->enseignant())->patchJson("/api/classe/documents/{$id}/publier")
            ->assertOk()->assertJsonPath('data.visibility', 'school');
        $this->en($this->plannerToken())->getJson("/api/documents/{$id}")->assertOk();
        // Publié, il n'est plus un document de classe : on ne le retire plus par cette route
        $this->en($this->enseignant())->deleteJson("/api/classe/documents/{$id}")->assertNotFound();
    }

    public function test_the_author_removes_the_class_document_and_its_file(): void
    {
        $id = $this->deposer()->json('data.id');
        $path = Document::withClassDocuments()->findOrFail($id)->file_path;

        $this->en($this->enseignant())->deleteJson("/api/classe/documents/{$id}")->assertNoContent();
        Storage::disk('minio')->assertMissing($path);
    }

    public function test_a_teacher_can_pin_a_library_document_but_not_a_class_document_of_someone_else(): void
    {
        $bibliotheque = Document::factory()->create(['module_id' => $this->module->id]);
        $classeAutre = Document::factory()->classOnly()->create(['module_id' => $this->module->id]);

        $this->en($this->enseignant())->getJson("/api/classe/documents/{$bibliotheque->id}")->assertOk();
        $this->en($this->enseignant())->getJson("/api/classe/documents/{$classeAutre->id}")->assertNotFound();
    }

    public function test_the_service_token_opens_one_document_and_nothing_else(): void
    {
        $id = $this->deposer()->json('data.id');

        $this->en($this->serviceToken($id))->getJson("/api/interne/documents/{$id}/lien")
            ->assertOk()->assertJsonPath('expires_in', 300)
            ->assertJson(fn ($json) => $json->where('url', fn ($url) => str_starts_with($url, 'https://minio.test/'))->etc());
        $this->assertDatabaseHas('document_downloads', ['document_id' => $id]);

        $autre = Document::factory()->create()->id;
        $this->en($this->serviceToken($autre))->getJson("/api/interne/documents/{$id}/lien")->assertForbidden();
        $this->en($this->serviceToken($id, ['scope' => 'document:supprimer']))->getJson("/api/interne/documents/{$id}/lien")->assertUnauthorized();
        $this->en($this->serviceToken($id, ['exp' => time() + 3600]))->getJson("/api/interne/documents/{$id}/lien")->assertUnauthorized();
        $this->en($this->serviceToken($id, ['aud' => ['studylib-interne', 'studylib']]))->getJson("/api/interne/documents/{$id}/lien")->assertUnauthorized();
        // Un jeton d'utilisateur n'ouvre pas la route interne, un jeton de service n'ouvre pas de session
        $this->en($this->enseignant())->getJson("/api/interne/documents/{$id}/lien")->assertUnauthorized();
        $this->en($this->serviceToken($id))->getJson('/api/events')->assertUnauthorized();
        // Le jeton de service n'a ouvert aucun compte
        $this->assertDatabaseMissing('users', ['planner_id' => '42']);
    }
}
