<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\PlannerTokenVerifier;
use App\Enums\UserRole;
use App\Models\Filiere;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\IssuesPlannerTokens;
use Tests\TestCase;
use UnexpectedValueException;

/**
 * Contrat du jeton d'accès avec HESTIM Planner (tests/Fixtures/contrat-jeton-sso.json, copie de
 * shared/contrat-jeton-sso.json du dépôt Planner). Ce que StudyLib exige et lit d'un jeton est
 * exactement ce que le contrat annonce : si un des deux services change une revendication, le
 * test de son côté échoue.
 */
class PlannerTokenContractTest extends TestCase
{
    use IssuesPlannerTokens, RefreshDatabase;

    private const COPIE_PLANNER = __DIR__.'/../../../../plan_B_projet_PACTE/shared/contrat-jeton-sso.json';

    /** @var array<string, mixed> */
    private array $contrat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakePlanner();
        $this->contrat = json_decode((string) file_get_contents(base_path('tests/Fixtures/contrat-jeton-sso.json')), true, flags: JSON_THROW_ON_ERROR);
    }

    public function test_the_configuration_follows_the_contract(): void
    {
        $this->assertSame($this->contrat['emetteur'], config('planner.issuer'));
        $this->assertSame($this->contrat['audience'], config('planner.audience'));
    }

    public function test_each_mandatory_claim_is_required_and_no_other(): void
    {
        $verifier = app(PlannerTokenVerifier::class);

        foreach ($this->contrat['obligatoires'] as $claim) {
            try {
                $verifier->verify($this->plannerToken([$claim => '']));
                $this->fail("Un jeton sans « {$claim} » doit être refusé");
            } catch (UnexpectedValueException) {
                $this->addToAssertionCount(1);
            }
        }

        // Aucune revendication facultative n'est exigée
        $minimal = array_fill_keys(array_keys($this->contrat['facultatives']), null);
        $claims = $verifier->verify($this->plannerToken($minimal));
        $this->assertSame('42', $claims['sub']);
    }

    public function test_optional_claims_are_read_under_the_names_of_the_contract(): void
    {
        $this->assertSame(['prenom', 'nom', 'filiere', 'annee'], array_keys($this->contrat['facultatives']));
        $filiere = Filiere::factory()->create(['code' => 'IIIA']);

        $user = app(AuthService::class)->provisionFromPlanner([
            'sub' => '42', 'sid' => 's', 'email' => 'amina@hestim.ma', 'role' => 'etudiant',
            'prenom' => 'Amina', 'nom' => 'Benali', 'filiere' => 'IIIA', 'annee' => 4,
        ]);

        $this->assertSame('Amina Benali', $user->name);
        $this->assertSame($filiere->id, $user->filiere_id);
        $this->assertSame(4, $user->year_level);
        $this->assertSame(1, User::query()->count());
    }

    public function test_roles_are_mapped_as_the_contract_says(): void
    {
        foreach ($this->contrat['roles'] as $planner => $studylib) {
            $this->assertSame($studylib, UserRole::fromPlanner($planner)?->value);
        }
        $this->assertNull(UserRole::fromPlanner('service'));
    }

    public function test_both_repositories_hold_the_same_contract(): void
    {
        if (! is_file(self::COPIE_PLANNER)) {
            $this->markTestSkipped('Dépôt Planner absent à côté de StudyLib : copies non comparées.');
        }

        $this->assertSame(
            json_decode((string) file_get_contents(self::COPIE_PLANNER), true, flags: JSON_THROW_ON_ERROR),
            $this->contrat,
            'Le contrat du jeton diffère entre Planner et StudyLib.',
        );
    }
}
