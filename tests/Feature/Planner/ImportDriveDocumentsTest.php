<?php

declare(strict_types=1);

namespace Tests\Feature\Planner;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\IssuesPlannerTokens;
use Tests\TestCase;

/**
 * php artisan studylib:import-drive : supports de cours importés d'un dossier Drive partagé
 * avec le compte de service (Google simulé par Http::fake, MinIO par Storage::fake).
 */
class ImportDriveDocumentsTest extends TestCase
{
    use IssuesPlannerTokens, RefreshDatabase;

    private const ROOT = 'dossier-racine-0001';

    private const PDF = 'application/pdf';

    private const DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

    private const GDOC = 'application/vnd.google-apps.document';

    private const FOLDER = 'application/vnd.google-apps.folder';

    private string $credentials;

    private string $publicPem;

    /** @var array<string, array{id: string, name: string, mimeType: string, size?: string, parent: string|null}> */
    private array $drive = [];

    private Module $ml;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('minio');

        [$pem] = $this->newRsaKey('cle-drive');
        $this->publicPem = openssl_pkey_get_details(openssl_pkey_get_private($pem))['key'];
        $this->credentials = tempnam(sys_get_temp_dir(), 'drive');
        file_put_contents($this->credentials, json_encode([
            'type' => 'service_account',
            'client_email' => 'import@hestim-test.iam.gserviceaccount.com',
            'private_key_id' => 'cle-drive',
            'private_key' => $pem,
        ]));
        config(['services.google_drive.credentials' => $this->credentials]);

        $iiia = Filiere::factory()->create(['code' => 'IIIA']);
        $this->ml = Module::factory()->create(['filiere_id' => $iiia->id, 'code' => 'IIIA-ML', 'name' => 'Machine Learning']);

        $this->folder(self::ROOT, 'Supports HESTIM', null);
        $this->folder('dossier-iiia-0001', 'IIIA', self::ROOT);
        $this->folder('dossier-ml-00001', 'IIIA-ML Machine learning', 'dossier-iiia-0001');
        $this->folder('dossier-td-00001', 'TD', 'dossier-ml-00001');
        $this->file('fichier-pdf-0001', 'Série 1.pdf', self::PDF, 'dossier-td-00001');
        $this->file('fichier-docx-001', 'Examen 2023-2024.docx', self::DOCX, 'dossier-ml-00001');
        $this->file('fichier-gdoc-001', 'Plan du cours', self::GDOC, 'dossier-ml-00001');
        $this->file('fichier-jpg-0001', 'photo.jpg', 'image/jpeg', 'dossier-ml-00001');
        $this->file('fichier-gros-001', 'Vidéo.pdf', self::PDF, 'dossier-ml-00001', (string) (25 * 1024 * 1024));
        $this->folder('dossier-divers-1', 'Divers', self::ROOT);
        $this->file('fichier-divers-1', 'notes.pdf', self::PDF, 'dossier-divers-1');

        Http::fake(fn (ClientRequest $request) => $this->fakeGoogle($request));
    }

    protected function tearDown(): void
    {
        @unlink($this->credentials);
        parent::tearDown();
    }

    public function test_imports_supported_files_into_the_module_found_in_the_folders(): void
    {
        $this->artisan('studylib:import-drive', ['folder' => self::ROOT])->assertSuccessful();

        $documents = Document::query()->orderBy('title')->get()->keyBy('title');
        $this->assertSame(['Examen 2023-2024', 'Plan du cours', 'Série 1'], $documents->keys()->all());
        $this->assertTrue($documents->every(fn (Document $d) => $d->module_id === $this->ml->id
            && $d->status === DocumentStatus::Pending && $d->user_id === null));

        $this->assertSame(DocumentType::Td, $documents['Série 1']->type);
        $this->assertSame(DocumentType::Examen, $documents['Examen 2023-2024']->type);
        $this->assertSame(2024, $documents['Examen 2023-2024']->year_concern);
        $this->assertSame(DocumentType::Cours, $documents['Plan du cours']->type);

        // Le Google Doc est exporté en PDF, les autres fichiers téléchargés tels quels
        $this->assertSame(self::PDF, $documents['Plan du cours']->mime_type);
        Storage::disk('minio')->assertExists($documents['Plan du cours']->file_path);
        $this->assertSame('EXPORT-PDF', Storage::disk('minio')->get($documents['Plan du cours']->file_path));
        $this->assertSame('CONTENU-fichier-pdf-0001', Storage::disk('minio')->get($documents['Série 1']->file_path));
        $this->assertStringEndsWith('.docx', $documents['Examen 2023-2024']->file_path);
        $this->assertSame('drive:fichier-pdf-0001', $documents['Série 1']->source_ref);
    }

    public function test_authenticates_as_the_service_account_with_read_only_scope(): void
    {
        $this->artisan('studylib:import-drive', ['folder' => self::ROOT])->assertSuccessful();

        Http::assertSent(function (ClientRequest $request): bool {
            if ($request->url() !== 'https://oauth2.googleapis.com/token') {
                return false;
            }
            $claims = JWT::decode($request['assertion'], new Key($this->publicPem, 'RS256'));

            return $request['grant_type'] === 'urn:ietf:params:oauth:grant-type:jwt-bearer'
                && $claims->iss === 'import@hestim-test.iam.gserviceaccount.com'
                && $claims->scope === 'https://www.googleapis.com/auth/drive.readonly'
                && $claims->aud === 'https://oauth2.googleapis.com/token';
        });
        Http::assertSent(fn (ClientRequest $request) => str_contains($request->url(), 'googleapis.com/drive/v3')
            && $request->hasHeader('Authorization', 'Bearer jeton-drive'));
    }

    public function test_a_second_run_imports_nothing_even_after_a_moderator_deleted_a_document(): void
    {
        $this->artisan('studylib:import-drive', ['folder' => self::ROOT])->assertSuccessful();
        Document::query()->where('title', 'Série 1')->sole()->delete();

        $this->artisan('studylib:import-drive', ['folder' => self::ROOT])->assertSuccessful();

        $this->assertSame(3, Document::withTrashed()->count());
        $this->assertSame(2, Document::query()->count());
    }

    public function test_approve_publishes_directly_and_dry_run_records_nothing(): void
    {
        $this->artisan('studylib:import-drive', ['folder' => self::ROOT, '--dry-run' => true])
            ->expectsOutputToContain('IIIA-ML · TD')
            ->assertSuccessful();
        $this->assertSame(0, Document::query()->count());
        $this->assertSame([], Storage::disk('minio')->allFiles());

        $this->artisan('studylib:import-drive', ['folder' => self::ROOT, '--approve' => true])->assertSuccessful();
        $this->assertSame(3, Document::query()->where('status', DocumentStatus::Approved->value)->count());
    }

    public function test_a_module_code_shared_by_two_filieres_needs_the_filiere_folder(): void
    {
        $gi = Filiere::factory()->create(['code' => 'GI']);
        $iiia = Filiere::query()->where('code', 'IIIA')->sole();
        Module::factory()->create(['filiere_id' => $iiia->id, 'code' => 'ANG-1', 'name' => 'Anglais 1']);
        $anglaisGi = Module::factory()->create(['filiere_id' => $gi->id, 'code' => 'ANG-1', 'name' => 'Anglais 1']);
        $this->drive = [];
        $this->folder(self::ROOT, 'Langues', null);
        $this->folder('dossier-ang-0001', 'ANG-1', self::ROOT);
        $this->file('fichier-ang-0001', 'Vocabulaire.pdf', self::PDF, 'dossier-ang-0001');
        $this->folder('dossier-gi-00001', 'GI', self::ROOT);
        $this->folder('dossier-anggi-01', 'Anglais 1', 'dossier-gi-00001');
        $this->file('fichier-anggi-01', 'Grammaire.pdf', self::PDF, 'dossier-anggi-01');

        $this->artisan('studylib:import-drive', ['folder' => self::ROOT])
            ->expectsOutputToContain('existe dans plusieurs filières')
            ->assertSuccessful();

        $document = Document::query()->sole();
        $this->assertSame(['Grammaire', $anglaisGi->id], [$document->title, $document->module_id]);
    }

    public function test_fails_clearly_without_a_readable_key_or_with_an_invalid_folder_id(): void
    {
        $this->artisan('studylib:import-drive', ['folder' => "x' or name contains '"])
            ->expectsOutputToContain('ID de dossier invalide')
            ->assertFailed();

        config(['services.google_drive.credentials' => sys_get_temp_dir().'/absente.json']);
        $this->artisan('studylib:import-drive', ['folder' => self::ROOT])
            ->expectsOutputToContain('Clé du compte de service introuvable')
            ->assertFailed();

        file_put_contents($this->credentials, json_encode(['type' => 'authorized_user']));
        config(['services.google_drive.credentials' => $this->credentials]);
        $this->artisan('studylib:import-drive', ['folder' => self::ROOT])
            ->expectsOutputToContain("n'est pas une clé de compte de service")
            ->assertFailed();
        $this->assertSame(0, Document::query()->count());
    }

    public function test_reports_a_folder_that_is_not_shared_with_the_service_account(): void
    {
        $this->artisan('studylib:import-drive', ['folder' => 'dossier-non-partage'])
            ->expectsOutputToContain('le dossier est-il partagé avec le compte de service')
            ->assertFailed();
    }

    private function folder(string $id, string $name, ?string $parent): void
    {
        $this->drive[$id] = ['id' => $id, 'name' => $name, 'mimeType' => self::FOLDER, 'parent' => $parent];
    }

    private function file(string $id, string $name, string $mimeType, string $parent, string $size = '9'): void
    {
        $this->drive[$id] = ['id' => $id, 'name' => $name, 'mimeType' => $mimeType, 'size' => $size, 'parent' => $parent];
    }

    private function fakeGoogle(ClientRequest $request)
    {
        $url = parse_url($request->url());
        parse_str($url['query'] ?? '', $query);
        $path = $url['path'] ?? '';

        if ($url['host'] === 'oauth2.googleapis.com') {
            return Http::response(['access_token' => 'jeton-drive', 'expires_in' => 3600, 'token_type' => 'Bearer']);
        }
        if ($path === '/drive/v3/files') {
            preg_match("/^'([^']+)' in parents/", $query['q'] ?? '', $parent);
            $files = collect($this->drive)->where('parent', $parent[1] ?? null)
                ->map(fn (array $f) => collect($f)->except('parent')->all())->values();

            return Http::response(['files' => $files]);
        }
        if (preg_match('#^/drive/v3/files/([^/]+)(/export)?$#', $path, $match) === 1) {
            $file = $this->drive[$match[1]] ?? null;
            if ($file === null) {
                return Http::response(['error' => ['code' => 404, 'message' => 'File not found']], 404);
            }
            if (($match[2] ?? '') === '/export') {
                return Http::response('EXPORT-PDF');
            }

            return ($query['alt'] ?? null) === 'media'
                ? Http::response('CONTENU-'.$file['id'])
                : Http::response(collect($file)->only(['id', 'name', 'mimeType'])->all());
        }

        return Http::response(['error' => ['message' => 'inattendu']], 500);
    }
}
