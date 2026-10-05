<?php

declare(strict_types=1);

namespace Tests\Feature\Planner;

use App\Models\Filiere;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * php artisan planner:sync : filières et modules suivent la maquette de HESTIM Planner.
 */
class SyncPlannerReferentialTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'jeton-integration-de-test-0123456789';

    /** @var array<string, mixed> réponse courante du faux Planner */
    private array $referentiel = [];

    private int $statut = 200;

    protected function setUp(): void
    {
        parent::setUp();
        config(['planner.api_url' => 'http://planner.test/api', 'planner.integration_token' => self::TOKEN]);
        Http::fake(['planner.test/api/integration/referentiel' => fn () => Http::response($this->referentiel, $this->statut)]);
    }

    private function fakeReferentiel(array $modules): void
    {
        $this->referentiel = [
            'filieres' => [
                ['code' => 'IIIA', 'nom' => 'Ingénierie informatique et IA', 'ecole' => 'engineering', 'cycle' => 'ingenieur'],
                ['code' => str_repeat('X', 40), 'nom' => 'Code trop long, ignoré'],
            ],
            'modules' => $modules,
        ];
    }

    public function test_creates_then_updates_without_duplicates(): void
    {
        $this->fakeReferentiel([
            ['code' => 'IIIA-ML', 'nom' => 'Machine Learning', 'semestre' => 7, 'filiere' => 'IIIA', 'ects' => 4],
            ['code' => 'IIIA-X', 'nom' => 'Semestre invalide', 'semestre' => 12, 'filiere' => 'IIIA'],
            ['code' => 'INCONNU-1', 'nom' => 'Filière inconnue', 'semestre' => 1, 'filiere' => 'NOPE'],
        ]);

        $this->artisan('planner:sync')->assertSuccessful();
        $this->artisan('planner:sync')->assertSuccessful();

        $this->assertSame(1, Filiere::query()->count());
        $module = Module::query()->sole();
        $this->assertSame(['IIIA-ML', 'Machine Learning', 7], [$module->code, $module->name, $module->semester]);
        Http::assertSent(fn (ClientRequest $request) => $request->hasHeader('X-Integration-Token', self::TOKEN));

        // Le module est renommé dans Planner : mis à jour, pas recréé
        $this->fakeReferentiel([['code' => 'IIIA-ML', 'nom' => 'Apprentissage automatique', 'semestre' => 7, 'filiere' => 'IIIA']]);
        $this->artisan('planner:sync')->assertSuccessful();
        $this->assertSame('Apprentissage automatique', Module::query()->sole()->name);
    }

    public function test_a_module_removed_from_planner_is_kept_for_its_documents(): void
    {
        $this->fakeReferentiel([['code' => 'IIIA-ML', 'nom' => 'Machine Learning', 'semestre' => 7, 'filiere' => 'IIIA']]);
        $this->artisan('planner:sync')->assertSuccessful();
        $this->fakeReferentiel([]);

        $this->artisan('planner:sync')
            ->expectsOutputToContain('absent(s) de la maquette')
            ->assertSuccessful();
        $this->assertSame(1, Module::query()->count());
    }

    public function test_refuses_to_run_without_a_proper_token_or_when_planner_fails(): void
    {
        config(['planner.integration_token' => 'court']);
        $this->artisan('planner:sync')->assertFailed();

        config(['planner.integration_token' => self::TOKEN]);
        $this->fakeReferentiel([]);
        $this->statut = 401;
        $this->artisan('planner:sync')->assertFailed();
        $this->assertSame(0, Filiere::query()->count());
    }
}
