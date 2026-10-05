<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Importe l'annuaire des entreprises d'accueil depuis un CSV (export Excel ou Google Sheets).
 *
 * Ligne d'en-tête obligatoire : name/nom/entreprise, et facultativement city/ville et
 * sector/secteur ; séparateur « , » ou « ; », UTF-8 ou Windows-1252 (Excel français).
 * Idempotente : une entreprise est reconnue par son nom et sa ville ; un secteur manquant
 * est complété, une valeur existante n'est jamais écrasée.
 */
class ImportCompanies extends Command
{
    protected $signature = 'studylib:import-companies {file : chemin du fichier CSV}';

    protected $description = 'Importe les entreprises d\'accueil depuis un fichier CSV';

    private const COLUMNS = [
        'name' => ['name', 'nom', 'entreprise', 'societe', 'company'],
        'city' => ['city', 'ville'],
        'sector' => ['sector', 'secteur', 'domaine'],
    ];

    private const MAX = ['name' => 150, 'city' => 100, 'sector' => 100];

    public function handle(): int
    {
        $path = (string) $this->argument('file');
        $raw = is_file($path) && is_readable($path) ? file_get_contents($path) : false;
        if ($raw === false) {
            $this->error("Fichier introuvable ou illisible : {$path}");

            return self::FAILURE;
        }

        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);
        if (! mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        }
        $rows = $this->rows($raw);

        $header = array_map(fn (?string $h) => $this->key((string) $h), $rows[0] ?? []);
        $index = [];
        foreach (self::COLUMNS as $column => $aliases) {
            $position = collect($header)->search(fn (string $h) => in_array($h, $aliases, true));
            if ($position !== false) {
                $index[$column] = $position;
            }
        }
        if (! isset($index['name'])) {
            $this->error('En-tête attendu : name (ou nom, entreprise), puis city (ville) et sector (secteur) facultatifs.');

            return self::FAILURE;
        }

        $counts = ['créées' => 0, 'complétées' => 0, 'inchangées' => 0, 'rejetées' => 0];
        DB::transaction(function () use ($rows, $index, &$counts): void {
            foreach (array_slice($rows, 1) as $number => $cells) {
                if ($cells === [null]) {
                    continue;
                }
                $row = [];
                foreach (array_keys(self::COLUMNS) as $column) {
                    $value = isset($index[$column]) ? $this->clean((string) ($cells[$index[$column]] ?? '')) : '';
                    $row[$column] = $value === '' ? null : $value;
                }

                $tooLong = collect(self::MAX)->contains(fn (int $max, string $column) => mb_strlen($row[$column] ?? '') > $max);
                if ($row['name'] === null || $tooLong) {
                    $counts['rejetées']++;
                    $this->warn('  ligne '.($number + 2).' ignorée : '.($row['name'] === null ? 'nom manquant' : 'valeur trop longue'));

                    continue;
                }

                $company = Company::query()->firstOrCreate(
                    ['name' => $row['name'], 'city' => $row['city']],
                    ['sector' => $row['sector']],
                );
                if ($company->wasRecentlyCreated) {
                    $counts['créées']++;
                } elseif ($company->sector === null && $row['sector'] !== null) {
                    $company->update(['sector' => $row['sector']]);
                    $counts['complétées']++;
                } else {
                    $counts['inchangées']++;
                }
            }
        });

        $this->table(['Entreprises', 'Nombre'], collect($counts)->map(fn (int $n, string $k) => [$k, $n])->values());

        return self::SUCCESS;
    }

    /**
     * fgetcsv sur un flux plutôt qu'un découpage par ligne : une cellule entre guillemets
     * peut contenir un retour à la ligne. Le séparateur est déduit de la ligne d'en-tête.
     *
     * @return list<list<string|null>>
     */
    private function rows(string $raw): array
    {
        $firstLine = strtok($raw, "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $raw);
        rewind($stream);
        $rows = [];
        while (($cells = fgetcsv($stream, null, $delimiter, '"', '')) !== false) {
            $rows[] = $cells;
        }
        fclose($stream);

        return $rows;
    }

    private function key(string $header): string
    {
        return preg_replace('/[^a-z]/', '', Str::lower(Str::ascii($header)));
    }

    private function clean(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value));
    }
}
