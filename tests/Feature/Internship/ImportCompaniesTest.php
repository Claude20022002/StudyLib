<?php

declare(strict_types=1);

namespace Tests\Feature\Internship;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * php artisan studylib:import-companies : annuaire des entreprises d'accueil depuis un CSV.
 */
class ImportCompaniesTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        array_map('unlink', $this->files);
        parent::tearDown();
    }

    private function csv(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($path, $content);
        $this->files[] = $path;

        return $path;
    }

    public function test_imports_a_french_excel_export_without_duplicates(): void
    {
        // Excel français : point-virgule, BOM UTF-8, en-têtes accentués, cellule sur deux lignes
        $file = $this->csv("\xEF\xBB\xBFEntreprise;Ville;Secteur\r\n"
            ."CapFinance;Casablanca;Finance\r\n"
            ."\"OCP  Group\";Khouribga;\"Mines\net chimie\"\r\n"
            .";Rabat;Sans nom\r\n"
            ."VoltEdge;;\r\n");

        $this->artisan('studylib:import-companies', ['file' => $file])
            ->expectsOutputToContain('ligne 4 ignorée : nom manquant')
            ->assertSuccessful();
        $this->artisan('studylib:import-companies', ['file' => $file])->assertSuccessful();

        $this->assertSame(3, Company::query()->count());
        $this->assertDatabaseHas('companies', ['name' => 'OCP Group', 'city' => 'Khouribga', 'sector' => 'Mines et chimie']);
        $this->assertDatabaseHas('companies', ['name' => 'VoltEdge', 'city' => null, 'sector' => null]);
    }

    public function test_completes_a_missing_sector_but_never_overwrites_one(): void
    {
        Company::query()->create(['name' => 'CapFinance', 'city' => 'Casablanca', 'sector' => 'Banque']);
        Company::query()->create(['name' => 'VoltEdge', 'city' => 'Tanger', 'sector' => null]);
        $file = $this->csv("name,city,sector\nCapFinance,Casablanca,Finance\nVoltEdge,Tanger,Industrie\n");

        $this->artisan('studylib:import-companies', ['file' => $file])->assertSuccessful();

        $this->assertDatabaseHas('companies', ['name' => 'CapFinance', 'sector' => 'Banque']);
        $this->assertDatabaseHas('companies', ['name' => 'VoltEdge', 'sector' => 'Industrie']);
    }

    public function test_reads_windows_1252_and_rejects_a_file_without_a_name_column(): void
    {
        $this->artisan('studylib:import-companies', ['file' => $this->csv(mb_convert_encoding("nom;ville\nSociété Générale;Fès\n", 'Windows-1252', 'UTF-8'))])
            ->assertSuccessful();
        $this->assertDatabaseHas('companies', ['name' => 'Société Générale', 'city' => 'Fès']);

        $this->artisan('studylib:import-companies', ['file' => $this->csv("ville,secteur\nRabat,IT\n")])
            ->expectsOutputToContain('En-tête attendu')
            ->assertFailed();
        $this->artisan('studylib:import-companies', ['file' => sys_get_temp_dir().'/absent.csv'])
            ->assertFailed();
    }
}
