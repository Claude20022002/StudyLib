<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\DocumentStatus;
use App\Enums\UserRole;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\User;
use App\Services\DocumentService;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\IssuesPlannerTokens;
use Tests\TestCase;

/**
 * Connexion unique (phase C2) : StudyLib fait confiance aux jetons RS256 de HESTIM Planner.
 */
class PlannerSsoTest extends TestCase
{
    use IssuesPlannerTokens, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakePlanner();
    }

    /**
     * Entre deux requêtes d'un même test, l'application est conservée : on oublie l'utilisateur
     * résolu par les gardes, comme le fait chaque nouvelle requête PHP en production.
     */
    private function nouvelleRequete(): void
    {
        $this->app['auth']->forgetGuards();
    }

    public function test_no_local_login_or_registration_guests_go_to_planner(): void
    {
        $this->get('/login')->assertRedirect('/connexion?next=%2Fdashboard');
        $this->get('/register')->assertRedirect('/connexion?next=%2Fdashboard');
        $this->post('/login', ['email' => 'x@hestim.ma', 'password' => 'secret'])->assertStatus(405);

        // Page protégée : on revient dessus après la connexion Planner
        $this->get('/documents')->assertRedirect(route('login'));
        $this->get('/login')->assertRedirect('/connexion?next=%2Fdocuments');
    }

    public function test_an_external_intended_url_is_never_forwarded(): void
    {
        $this->withSession(['url.intended' => 'https://evil.example/vol'])
            ->get('/login')
            ->assertRedirect('/connexion?next=%2Fdashboard');
    }

    public function test_planner_cookie_opens_the_account_with_planner_identity(): void
    {
        $filiere = Filiere::factory()->create(['code' => 'IIIA']);

        $this->withUnencryptedCookie('access_token', $this->plannerToken(['filiere' => 'IIIA']))
            ->get('/dashboard')
            ->assertOk();

        $user = User::query()->where('planner_id', '42')->sole();
        $this->assertSame('amina.etudiante@hestim.ma', $user->email);
        $this->assertSame('Amina Etudiante', $user->name);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertSame($filiere->id, $user->filiere_id);
        $this->assertSame(4, $user->year_level);
        $this->assertNotNull($user->email_verified_at);

        // Une visite suivante met le compte à jour (nom changé dans Planner), sans doublon
        $this->nouvelleRequete();
        $this->withUnencryptedCookie('access_token', $this->plannerToken(['nom' => 'Benali', 'filiere' => 'IIIA']))->get('/dashboard')->assertOk();
        $this->assertSame(1, User::query()->count());
        $this->assertSame('Amina Benali', $user->fresh()->name);
    }

    public function test_roles_are_mapped_and_an_existing_account_is_linked_by_email(): void
    {
        $existing = User::factory()->create(['email' => 'prof.dupont@hestim.ma', 'role' => UserRole::Student->value]);

        $this->withToken($this->plannerToken(['sub' => '7', 'role' => 'enseignant', 'email' => 'prof.dupont@hestim.ma']))
            ->getJson('/api/events')
            ->assertOk();

        $existing->refresh();
        $this->assertSame('7', $existing->planner_id);
        $this->assertSame(UserRole::Teacher, $existing->role);

        // Un rôle sans accès (compte de service) n'ouvre rien
        $this->nouvelleRequete();
        $this->withToken($this->plannerToken(['sub' => '8', 'role' => 'service', 'email' => 'svc@hestim.ma']))
            ->getJson('/api/events')
            ->assertUnauthorized();
        $this->assertDatabaseMissing('users', ['email' => 'svc@hestim.ma']);
    }

    public function test_invalid_tokens_are_refused(): void
    {
        [$otherKey] = $this->newRsaKey('autre');
        $publicPem = openssl_pkey_get_details(openssl_pkey_get_private($this->plannerPrivateKey))['key'];

        $refused = [
            'expiré' => $this->plannerToken(['exp' => time() - 120, 'iat' => time() - 1200]),
            'autre clé, même kid' => $this->plannerToken([], $otherKey),
            'kid inconnu' => $this->plannerToken([], $otherKey, 'inconnu'),
            'mauvaise audience' => $this->plannerToken(['aud' => ['planner']]),
            'mauvais émetteur' => $this->plannerToken(['iss' => 'quelqu-un']),
            'sans email' => $this->plannerToken(['email' => '']),
            'HS256 avec la clé publique' => JWT::encode(['iss' => 'hestim-planner', 'aud' => ['studylib'], 'sub' => '1', 'sid' => 's', 'email' => 'a@hestim.ma', 'role' => 'admin', 'exp' => time() + 900], $publicPem, 'HS256', $this->plannerKid),
            'mal formé' => 'pas.un.jeton',
        ];
        foreach ($refused as $case => $token) {
            $this->withToken($token)->getJson('/api/events')->assertUnauthorized();
            $this->flushHeaders();
            $this->nouvelleRequete();
        }
        $this->assertSame(0, User::query()->count());
    }

    public function test_an_unknown_kid_refreshes_the_jwks_at_most_once(): void
    {
        [$otherKey] = $this->newRsaKey('inconnu');
        for ($i = 0; $i < 3; $i++) {
            $this->withToken($this->plannerToken([], $otherKey, 'inconnu'))->getJson('/api/events')->assertUnauthorized();
        }
        // Une lecture initiale, une relecture pour le kid inconnu, puis plus rien (verrou de 30 s)
        Http::assertSentCount(2);
    }

    public function test_the_api_accepts_the_bearer_only_never_the_cookie(): void
    {
        // withCredentials : sans lui, le client de test n'enverrait pas le cookie sur une requête JSON
        $this->withCredentials()->withUnencryptedCookie('access_token', $this->plannerToken())->getJson('/api/events')->assertUnauthorized();
        // Le même cookie ouvre bien les pages web : c'est l'API qui le refuse
        $this->nouvelleRequete();
        $this->withCredentials()->withUnencryptedCookie('access_token', $this->plannerToken())->getJson('/modules/supports?codes[]=X')->assertOk();
        $this->nouvelleRequete();
        $this->withToken($this->plannerToken())->getJson('/api/events')->assertOk();
    }

    public function test_logout_revokes_the_planner_session_and_clears_cookies(): void
    {
        $token = $this->plannerToken();
        $response = $this->withUnencryptedCookie('access_token', $token)->post('/logout');

        $response->assertRedirect('/connexion');
        $response->assertCookieExpired('access_token');
        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'http://planner.test/api/auth/logout'
            && $request->hasHeader('Authorization', 'Bearer '.$token));
    }

    public function test_teachers_publish_without_moderation_students_go_through_it(): void
    {
        Storage::fake('minio');
        $teacher = User::factory()->create(['role' => UserRole::Teacher->value]);
        $student = User::factory()->create();
        $module = Module::factory()->create();
        $data = ['module_id' => $module->id, 'type' => 'cours', 'title' => 'Support'];
        $service = app(DocumentService::class);

        $this->assertSame(DocumentStatus::Approved, $service->upload($teacher, UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'), $data)->status);
        $this->assertSame(DocumentStatus::Pending, $service->upload($student, UploadedFile::fake()->create('b.pdf', 10, 'application/pdf'), $data)->status);
    }
}
