<?php

declare(strict_types=1);

namespace Tests\Feature\Planner;

use App\Livewire\Admin\OffresAModerer;
use App\Models\InternshipOffer;
use App\Models\User;
use App\Services\Offres\LecteurPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\Concerns\IssuesPlannerTokens;
use Tests\TestCase;

/**
 * Offres de stage partagées par un lien : lecture sûre de la page (adresses internes refusées,
 * redirections vérifiées, sites refusés), extraction JobPosting puis Open Graph, texte collé mis en
 * forme par l'IA, publication (modération des étudiants), liste et expiration.
 */
class InternshipOfferTest extends TestCase
{
    use IssuesPlannerTokens, RefreshDatabase;

    private const PAGE_REKRUTE = <<<'HTML'
        <html><head><meta property="og:title" content="Titre OG">
        <script type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"Organization","name":"x"},{"@type":"JobPosting","title":"Stagiaire &amp; développeur web","hiringOrganization":{"@type":"Organization","name":"OCP Group"},"jobLocation":[{"@type":"Place","address":{"addressLocality":"Casablanca"}}],"employmentType":["INTERN"],"validThrough":"2099-12-31","baseSalary":{"currency":"MAD","value":{"minValue":3000,"maxValue":4000,"unitText":"MONTH"}},"description":"<p>Rejoignez l’équipe <b>web</b>.</p><script>alert(1)<\/script>"}]}</script>
        </head><body></body></html>
        HTML;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakePlanner();
        $this->publique();
    }

    /** DNS simulé : l'hôte a ces adresses (liste vide : aucune) */
    private function dns(array $ips): void
    {
        $this->app->instance(LecteurPage::class, new LecteurPage(fn () => $ips));
    }

    private function publique(): void
    {
        $this->dns(['93.184.216.34']);
    }

    private function en(string $token): static
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token);
    }

    private function etudiant(): string
    {
        return $this->plannerToken();
    }

    private function enseignant(): string
    {
        return $this->plannerToken(['sub' => '7', 'role' => 'enseignant', 'email' => 'prof@hestim.ma']);
    }

    public function test_a_job_posting_page_prefills_the_form_without_any_html(): void
    {
        Http::fake(['offres.exemple.ma/*' => Http::response(self::PAGE_REKRUTE, 200, ['Content-Type' => 'text/html; charset=utf-8'])]);
        $this->en($this->etudiant())->postJson('/api/offres/analyser', ['url' => 'https://offres.exemple.ma/offre/42'])
            ->assertOk()
            ->assertJsonPath('source', 'jobposting')
            ->assertJsonPath('champs.title', 'Stagiaire & développeur web')
            ->assertJsonPath('champs.company_name', 'OCP Group')
            ->assertJsonPath('champs.city', 'Casablanca')
            ->assertJsonPath('champs.type', 'stage')
            ->assertJsonPath('champs.deadline', '2099-12-31')
            ->assertJsonPath('champs.salary', '3000 – 4000 MAD / month')
            ->assertJsonPath('champs.description', 'Rejoignez l’équipe web.');
    }

    public function test_a_job_posting_with_raw_line_breaks_is_still_read(): void
    {
        // ReKrute laisse des retours à la ligne bruts dans ses chaînes JSON
        $page = "<script type=\"application/ld+json\">{\"@type\": \"JobPosting\", \"title\": \"Contrôleur Qualité\", \"description\": \"Missions :\n- Contrôler\n\n\n- Valider\"}</script>";
        Http::fake(['*' => Http::response($page, 200, ['Content-Type' => 'text/html'])]);
        $this->en($this->etudiant())->postJson('/api/offres/analyser', ['url' => 'https://offres.exemple.ma/1'])
            ->assertOk()->assertJsonPath('source', 'jobposting')->assertJsonPath('champs.description', "Missions :\n- Contrôler\n\n- Valider");
    }

    public function test_without_job_posting_the_open_graph_preview_is_used(): void
    {
        Http::fake(['*' => Http::response('<meta property="og:title" content="Stage PFE data"><meta property="og:site_name" content="Inwi"><meta property="og:description" content="Six mois">', 200, ['Content-Type' => 'text/html'])]);
        $this->en($this->etudiant())->postJson('/api/offres/analyser', ['url' => 'https://carrieres.exemple.ma/a'])
            ->assertOk()->assertJsonPath('source', 'apercu')->assertJsonPath('champs.company_name', 'Inwi')->assertJsonPath('champs.type', 'stage');
    }

    public function test_refused_sites_are_not_read_and_the_text_is_asked_instead(): void
    {
        Http::fake();
        foreach (['https://ma.linkedin.com/jobs/view/1', 'https://www.linkedin.com/jobs/view/2', 'https://ma.indeed.com/viewjob?jk=3', 'https://www.emploi.ma/offre/4'] as $url) {
            $this->en($this->etudiant())->postJson('/api/offres/analyser', ['url' => $url])->assertOk()->assertJsonPath('source', 'refuse');
        }
        $this->aucuneLecture();
    }

    public function test_internal_addresses_and_unsafe_links_are_refused_before_any_request(): void
    {
        Http::fake();
        foreach ([['10.0.0.5'], ['127.0.0.1'], ['169.254.169.254'], ['::1'], ['93.184.216.34', '172.17.0.1'], []] as $ips) {
            $this->dns($ips);
            $this->en($this->etudiant())->postJson('/api/offres/analyser', ['url' => 'https://interne.exemple.ma/'])->assertStatus(422);
        }
        $this->publique();
        foreach (['http://exemple.ma/offre', 'https://user:pass@exemple.ma/', 'https://exemple.ma:8080/', 'file:///etc/passwd'] as $url) {
            $this->en($this->etudiant())->postJson('/api/offres/analyser', ['url' => $url])->assertStatus(422);
        }
        $this->aucuneLecture();
    }

    public function test_a_redirect_to_an_unsafe_target_is_refused(): void
    {
        Http::fake(['public.exemple.ma/*' => Http::response('', 302, ['Location' => 'http://minio:9000/studylib'])]);
        $this->en($this->etudiant())->postJson('/api/offres/analyser', ['url' => 'https://public.exemple.ma/offre'])->assertStatus(422);
        Http::fake(['public.exemple.ma/*' => Http::response('%PDF', 200, ['Content-Type' => 'application/pdf'])]);
        $this->en($this->etudiant())->postJson('/api/offres/analyser', ['url' => 'https://public.exemple.ma/offre.pdf'])->assertStatus(422);
    }

    public function test_pasted_text_is_formatted_by_the_ai_and_validated(): void
    {
        config(['services.claude.key' => 'cle-de-test']);
        Http::fake(['api.anthropic.com/*' => Http::response(['model' => 'm', 'content' => [['type' => 'text', 'text' => 'Voici : {"titre":"Stagiaire RH","entreprise":"ADKA Auto","ville":"Tanger","type":"inconnu","date_limite":"2001-01-01","remuneration":"null","description":"<b>Six mois</b>"}']]])]);
        $texte = str_repeat('Stage en ressources humaines chez ADKA Auto à Tanger. ', 3);
        $this->en($this->etudiant())->postJson('/api/offres/analyser-texte', ['texte' => $texte])
            ->assertOk()->assertJsonPath('champs.title', 'Stagiaire RH')->assertJsonPath('champs.type', 'stage')
            ->assertJsonPath('champs.deadline', null)->assertJsonPath('champs.salary', null)->assertJsonPath('champs.description', 'Six mois');
        config(['services.claude.key' => '']);
        $this->en($this->etudiant())->postJson('/api/offres/analyser-texte', ['texte' => $texte])->assertStatus(503);
    }

    private function publier(string $token, array $champs = []): TestResponse
    {
        return $this->en($token)->postJson('/api/offres', $champs + ['title' => 'Stage data', 'company_name' => 'OCP', 'city' => 'Casablanca', 'type' => 'stage', 'apply_url' => 'https://www.rekrute.com/offre-1.html', 'source' => 'page']);
    }

    public function test_students_go_through_moderation_teachers_publish_directly(): void
    {
        $this->publier($this->etudiant(), ['title' => 'Offre étudiante'])->assertCreated()->assertJsonPath('statut', 'pending');
        $this->publier($this->enseignant(), ['title' => 'Offre du prof', 'deadline' => now()->addDays(10)->toDateString()])->assertCreated()->assertJsonPath('statut', 'approved');
        $this->assertSame(['Offre du prof'], array_column($this->en($this->etudiant())->getJsonPath('/api/offres'), 'title'));

        // L'administration publie l'offre de l'étudiant depuis la page de modération
        $admin = User::factory()->admin()->create();
        Livewire::actingAs($admin)->test(OffresAModerer::class)->assertSee('Offre étudiante')->call('approuver', InternshipOffer::query()->where('title', 'Offre étudiante')->value('id'));
        $this->assertCount(2, $this->en($this->etudiant())->getJsonPath('/api/offres'));
    }

    public function test_listing_filters_expiry_and_my_offers(): void
    {
        $this->publier($this->enseignant(), ['title' => 'Alternance Rabat', 'type' => 'alternance', 'city' => 'Rabat']);
        $this->publier($this->enseignant(), ['title' => 'Stage Casa']);
        InternshipOffer::query()->create(['title' => 'Expirée', 'company_name' => 'X', 'type' => 'stage', 'apply_url' => 'https://x.ma', 'status' => 'approved', 'expires_at' => now()->subDay()]);
        $titres = fn (string $q) => array_column($this->en($this->etudiant())->getJsonPath('/api/offres'.$q), 'title');
        $this->assertEqualsCanonicalizing(['Alternance Rabat', 'Stage Casa'], $titres(''));
        $this->assertSame(['Alternance Rabat'], $titres('?type=alternance'));
        $this->assertSame(['Stage Casa'], $titres('?city=Casablanca'));
        $this->assertSame(['Alternance Rabat'], $titres('?q=rabat'));
        $this->publier($this->etudiant(), ['deadline' => '2001-01-01'])->assertUnprocessable();
        $this->publier($this->etudiant(), ['apply_url' => 'javascript:alert(1)'])->assertUnprocessable();

        $this->publier($this->etudiant(), ['title' => 'La mienne']);
        $miennes = $this->en($this->etudiant())->getJson('/api/offres/miennes')->assertOk()->json('data');
        $this->assertSame(['La mienne'], array_column($miennes, 'title'));
        $id = $miennes[0]['id'];
        $this->en($this->enseignant())->deleteJson("/api/offres/{$id}")->assertNotFound();
        $this->en($this->etudiant())->deleteJson("/api/offres/{$id}")->assertNoContent();
    }

    /** Aucune requête n'est partie vers un autre hôte que le Planner simulé (clés de signature) */
    private function aucuneLecture(): void
    {
        Http::assertNotSent(fn ($requete) => ! str_contains($requete->url(), 'planner.test'));
    }

    /** Raccourci : les données d'une liste paginée */
    private function getJsonPath(string $url): array
    {
        return $this->getJson($url)->assertOk()->json('data');
    }
}
