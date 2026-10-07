<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Filiere;
use App\Models\Module;
use App\Services\GoogleDrive\GoogleDriveClient;
use App\Services\GoogleDrive\GoogleDriveException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Importe les supports de cours d'un dossier Google Drive partagé avec le compte de service.
 *
 * Le module est reconnu à n'importe quel niveau de l'arborescence, par son code Planner
 * (« IIIA-ML » ou « IIIA-ML Machine learning ») ou par son nom exact ; un dossier au code
 * d'une filière lève l'ambiguïté quand deux filières partagent un code de module. Le type
 * (cours, TD, TP, examen) et l'année viennent des noms de dossiers ou du fichier.
 *
 * Idempotente : chaque fichier est marqué « drive:<id> » ; un fichier déjà importé, même
 * supprimé depuis par la modération, n'est pas repris. Les documents arrivent en attente
 * de modération, sauf avec --approve (dossier officiel de l'école).
 */
class ImportDriveDocuments extends Command
{
    protected $signature = 'studylib:import-drive
        {folder? : ID du dossier Drive (fin de l\'URL drive.google.com/drive/folders/…) ; par défaut GOOGLE_DRIVE_FOLDER_ID}
        {--approve : publier directement, sans modération (dossier officiel HESTIM)}
        {--dry-run : afficher ce qui serait importé sans rien enregistrer}
        {--map= : fichier JSON {"nom du dossier": "CODE-MODULE"} ; par défaut GOOGLE_DRIVE_MODULE_MAP s\'il existe}
        {--filiere= : ne rattacher qu\'aux modules de cette filière (ex. IIIA) ; par défaut GOOGLE_DRIVE_FILIERE}';

    protected $description = 'Importe les supports de cours d\'un dossier Google Drive';

    private const DISK = 'minio';

    /** Même plafond que le dépôt depuis l'interface (StoreDocumentRequest). */
    private const MAX_BYTES = 20 * 1024 * 1024;

    private const MAX_DEPTH = 8;

    /** Formats acceptés tels quels (ceux du dépôt depuis l'interface). */
    private const DIRECT = [
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-powerpoint' => 'ppt',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
    ];

    /** Fichiers Google exportés en PDF. */
    private const EXPORTED = [
        'application/vnd.google-apps.document',
        'application/vnd.google-apps.presentation',
    ];

    private const TYPE_WORDS = [
        'examen' => DocumentType::Examen, 'examens' => DocumentType::Examen, 'exam' => DocumentType::Examen,
        'exams' => DocumentType::Examen, 'partiel' => DocumentType::Examen, 'partiels' => DocumentType::Examen,
        'controle' => DocumentType::Examen, 'controles' => DocumentType::Examen, 'rattrapage' => DocumentType::Examen,
        'epreuve' => DocumentType::Examen, 'epreuves' => DocumentType::Examen,
        'td' => DocumentType::Td, 'tds' => DocumentType::Td, 'exercice' => DocumentType::Td,
        'exercices' => DocumentType::Td, 'serie' => DocumentType::Td, 'series' => DocumentType::Td,
        'tp' => DocumentType::Tp, 'tps' => DocumentType::Tp, 'lab' => DocumentType::Tp, 'labs' => DocumentType::Tp,
        'cours' => DocumentType::Cours, 'course' => DocumentType::Cours, 'courses' => DocumentType::Cours,
        'lecture' => DocumentType::Cours, 'chapitre' => DocumentType::Cours, 'slides' => DocumentType::Cours,
    ];

    /** @var Collection<int, Module> */
    private Collection $modules;

    /** @var Collection<int, Filiere> */
    private Collection $filieres;

    /** Filière imposée par --filiere (null : toutes) */
    private ?string $filiereId = null;

    /** @var array<string, Module> nom de dossier normalisé → module (fichier de correspondance) */
    private array $map = [];

    /** @var array<string, true> fichiers et dossiers déjà vus pendant ce passage (raccourcis multiples, boucles) */
    private array $seen = [];

    /** @var array<string, int> */
    private array $counts = ['importés' => 0, 'déjà importés' => 0, 'format non pris en charge' => 0,
        'trop volumineux' => 0, 'module introuvable' => 0, 'raccourci inaccessible' => 0, 'erreurs' => 0];

    public function handle(GoogleDriveClient $drive): int
    {
        $this->seen = [];
        $this->map = [];
        $this->counts = array_map(fn () => 0, $this->counts);

        $folderId = trim((string) ($this->argument('folder') ?? config('services.google_drive.folder')));
        // L'URL complète du dossier est acceptée : https://drive.google.com/drive/folders/<ID>?usp=sharing
        if (preg_match('#/folders/([A-Za-z0-9_-]+)#', $folderId, $match) === 1) {
            $folderId = $match[1];
        }
        if (ctype_digit($folderId)) {
            $this->error('Ce numéro ressemble au client_id du compte de service, pas à un dossier : ouvrez le dossier dans Drive et copiez la fin de son URL (drive.google.com/drive/folders/<ID>).');

            return self::FAILURE;
        }
        if (preg_match('/^[A-Za-z0-9_-]{10,200}$/', $folderId) !== 1) {
            $this->error('ID de dossier invalide : copiez la fin de l\'URL drive.google.com/drive/folders/<ID>.');

            return self::FAILURE;
        }

        try {
            $root = $drive->file($folderId);
        } catch (GoogleDriveException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        if ($root['mimeType'] !== GoogleDriveClient::FOLDER) {
            $this->error("« {$root['name']} » n'est pas un dossier.");

            return self::FAILURE;
        }

        $this->modules = Module::query()->get(['id', 'filiere_id', 'code', 'name', 'semester']);
        $this->filieres = Filiere::query()->get(['id', 'code']);
        if ($this->modules->isEmpty()) {
            $this->error('Aucun module : lancez d\'abord php artisan planner:sync.');

            return self::FAILURE;
        }
        if (! $this->loadMap()) {
            return self::FAILURE;
        }
        $filiereCode = trim((string) ($this->option('filiere') ?: config('services.google_drive.filiere')));
        $this->filiereId = null;
        if ($filiereCode !== '') {
            $this->filiereId = $this->filieres->first(fn (Filiere $f) => Str::upper($f->code) === Str::upper($filiereCode))?->id;
            if ($this->filiereId === null) {
                $this->error("Filière {$filiereCode} inconnue (codes : {$this->filieres->pluck('code')->sort()->implode(', ')}).");

                return self::FAILURE;
            }
        }

        $this->info(($this->option('dry-run') ? '[essai] ' : '')."Parcours de « {$root['name']} »…");
        $this->walk($drive, $folderId, [$root['name']], 0);

        $this->table(['Résultat', 'Fichiers'], collect($this->counts)->map(fn (int $n, string $k) => [$k, $n])->values());
        if (! $this->option('dry-run') && $this->counts['importés'] > 0 && ! $this->option('approve')) {
            $this->line('Les documents importés attendent la modération (Administration › Modération).');
        }
        if ($this->counts['raccourci inaccessible'] > 0) {
            $this->warn('Des raccourcis pointent vers des fichiers non partagés avec le compte de service : '
                .'partagez les fichiers d\'origine (ou leur dossier), pas seulement le dossier des raccourcis.');
        }

        return $this->counts['erreurs'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /** @param list<string> $path noms des dossiers depuis la racine importée */
    private function walk(GoogleDriveClient $drive, string $folderId, array $path, int $depth): void
    {
        if (isset($this->seen[$folderId]) || $depth >= self::MAX_DEPTH) {
            return;
        }
        $this->seen[$folderId] = true;

        try {
            $children = $drive->children($folderId);
        } catch (GoogleDriveException $e) {
            $this->counts['erreurs']++;
            $this->warn(implode(' / ', $path).' : '.$e->getMessage());

            return;
        }

        foreach ($children as $file) {
            if ($file['mimeType'] === GoogleDriveClient::SHORTCUT) {
                $file = $this->resolveShortcut($drive, $file, $path);
                if ($file === null) {
                    continue;
                }
            }
            if ($file['mimeType'] === GoogleDriveClient::FOLDER) {
                $this->walk($drive, $file['id'], [...$path, $file['name']], $depth + 1);

                continue;
            }
            $this->import($drive, $file, $path);
        }
    }

    /**
     * Raccourci (courant dans les dossiers Classroom) : on suit sa cible, à condition que le
     * compte de service puisse la lire ; partager le dossier des raccourcis ne partage pas les cibles.
     *
     * @param  array{id: string, name: string, mimeType: string, shortcutDetails?: array{targetId?: string, targetMimeType?: string}}  $shortcut
     * @param  list<string>  $path
     * @return array{id: string, name: string, mimeType: string, size?: string}|null
     */
    private function resolveShortcut(GoogleDriveClient $drive, array $shortcut, array $path): ?array
    {
        $targetId = $shortcut['shortcutDetails']['targetId'] ?? null;
        $targetMime = $shortcut['shortcutDetails']['targetMimeType'] ?? null;
        if (! is_string($targetId) || ! is_string($targetMime)) {
            return null;
        }
        $where = implode(' / ', [...$path, $shortcut['name']]);

        // Format refusé : inutile d'interroger Drive
        if ($targetMime !== GoogleDriveClient::FOLDER && ! in_array($targetMime, self::EXPORTED, true) && ! isset(self::DIRECT[$targetMime])) {
            $this->counts['format non pris en charge']++;
            $this->line("  ignoré (format) : {$where}", verbosity: 'v');

            return null;
        }

        try {
            $target = $drive->file($targetId);
        } catch (GoogleDriveException) {
            $this->counts['raccourci inaccessible']++;
            $this->line("  ignoré (cible non partagée) : {$where}", verbosity: 'v');

            return null;
        }

        // Le nom du raccourci est celui que l'étudiant voit dans Classroom
        return [...$target, 'name' => $shortcut['name']];
    }

    /**
     * @param  array{id: string, name: string, mimeType: string, size?: string}  $file
     * @param  list<string>  $path
     */
    private function import(GoogleDriveClient $drive, array $file, array $path): void
    {
        $where = implode(' / ', [...$path, $file['name']]);
        $sourceRef = 'drive:'.$file['id'];

        // Fichiers de verrouillage d'Office (« ~$cours.docx ») laissés par une synchronisation
        if (str_starts_with($file['name'], '~$')) {
            $this->counts['format non pris en charge']++;

            return;
        }

        if (isset($this->seen[$file['id']]) || Document::withTrashed()->where('source_ref', $sourceRef)->exists()) {
            $this->counts['déjà importés']++;

            return;
        }
        $this->seen[$file['id']] = true;

        $exported = in_array($file['mimeType'], self::EXPORTED, true);
        $extension = $exported ? 'pdf' : (self::DIRECT[$file['mimeType']] ?? null);
        if ($extension === null) {
            $this->counts['format non pris en charge']++;
            $this->line("  ignoré (format) : {$where}", verbosity: 'v');

            return;
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            $this->counts['trop volumineux']++;
            $this->warn("  ignoré (plus de 20 Mo) : {$where}");

            return;
        }

        $title = $exported ? $file['name'] : (pathinfo($file['name'], PATHINFO_FILENAME) ?: $file['name']);
        $segments = [...$path, $title];
        [$module, $reason] = $this->resolveModule($segments);
        if ($module === null) {
            $this->counts['module introuvable']++;
            $this->warn("  ignoré ({$reason}) : {$where}");

            return;
        }
        $type = $this->resolveType($segments);
        $year = $this->resolveYear($segments);

        if ($this->option('dry-run')) {
            $this->counts['importés']++;
            $this->line("  {$module->code} · {$type->label()}".($year ? " · {$year}" : '')." ← {$where}");

            return;
        }

        try {
            $content = $exported ? $drive->export($file['id'], 'application/pdf') : $drive->download($file['id']);
        } catch (GoogleDriveException $e) {
            $this->counts['erreurs']++;
            $this->warn("  {$where} : {$e->getMessage()}");

            return;
        }
        if (strlen($content) > self::MAX_BYTES) {
            $this->counts['trop volumineux']++;
            $this->warn("  ignoré (plus de 20 Mo) : {$where}");

            return;
        }

        $filePath = 'documents/'.Str::uuid().'.'.$extension;
        // Le disque ne lève pas d'exception ('throw' => false) : sans ce contrôle, un MinIO
        // injoignable laisserait des fiches pointant vers des fichiers absents
        if (! Storage::disk(self::DISK)->put($filePath, $content)) {
            $this->counts['erreurs']++;
            $this->warn("  {$where} : stockage impossible (MinIO est-il joignable ?)");

            return;
        }
        try {
            Document::query()->create([
                'user_id' => null,
                'module_id' => $module->id,
                'type' => $type->value,
                'title' => Str::limit($title, 200, ''),
                'description' => 'Importé depuis le Drive HESTIM.',
                'file_path' => $filePath,
                'source_ref' => $sourceRef,
                'file_size' => strlen($content),
                'mime_type' => $exported ? 'application/pdf' : $file['mimeType'],
                'year_concern' => $year,
                'status' => ($this->option('approve') ? DocumentStatus::Approved : DocumentStatus::Pending)->value,
            ]);
        } catch (Throwable $e) {
            Storage::disk(self::DISK)->delete($filePath);
            $this->counts['erreurs']++;
            $this->warn("  {$where} : enregistrement impossible ({$e->getMessage()})");

            return;
        }

        $this->counts['importés']++;
        $this->line("  {$module->code} · {$type->label()} ← {$where}", verbosity: 'v');
    }

    /**
     * Le module le plus proche du fichier l'emporte (on remonte depuis le fichier vers la racine).
     *
     * @param  list<string>  $segments
     * @return array{0: Module|null, 1: string|null}
     */
    private function resolveModule(array $segments): array
    {
        $filiereIds = $this->filieres
            ->filter(fn (Filiere $f) => collect($segments)->contains(fn (string $s) => $this->matchesCode($s, $f->code)))
            ->pluck('id');

        // Un dossier « S7 » (ou « Semestre 7 ») et l'option --filiere écartent les homonymes :
        // « Anglais » sous S7 ne doit pas tomber sur l'anglais de 1re année d'une autre filière
        $semester = collect($segments)
            ->map(fn (string $s) => preg_match('/^\s*(?:s|semestre\s*)(\d{1,2})\s*$/i', $s, $m) === 1 ? (int) $m[1] : null)
            ->filter()->last();
        $pool = $this->modules
            ->when($semester !== null, fn ($modules) => $modules->where('semester', $semester))
            ->when($this->filiereId !== null, fn ($modules) => $modules->where('filiere_id', $this->filiereId));

        foreach (array_reverse($segments) as $segment) {
            // Correspondance explicite d'abord (dossiers Classroom aux intitulés libres)
            if (isset($this->map[$this->normalize($segment)])) {
                return [$this->map[$this->normalize($segment)], null];
            }

            $byCode = $pool->filter(fn (Module $m) => $this->matchesCode($segment, $m->code));
            if ($byCode->isNotEmpty()) {
                // « IIIA-ML-AV » doit l'emporter sur « IIIA-ML » quand les deux correspondent
                $longest = $byCode->max(fn (Module $m) => strlen($m->code));
                $candidates = $byCode->filter(fn (Module $m) => strlen($m->code) === $longest);
            } else {
                // Nom exact ou début de nom : « Réseaux informatiques 3IIA_25-26 » → « Réseaux Informatiques » ;
                // le nom le plus long l'emporte (« Anglais technique » plutôt que « Anglais »)
                $name = $this->normalize($segment);
                $byName = $pool->filter(function (Module $m) use ($name): bool {
                    $moduleName = $this->normalize($m->name);

                    return $moduleName !== '' && ($name === $moduleName || str_starts_with($name, $moduleName.' '));
                });
                $longest = $byName->max(fn (Module $m) => strlen($this->normalize($m->name)));
                $candidates = $byName->filter(fn (Module $m) => strlen($this->normalize($m->name)) === $longest);
            }
            if ($candidates->count() > 1 && $filiereIds->isNotEmpty()) {
                $candidates = $candidates->whereIn('filiere_id', $filiereIds);
            }
            if ($candidates->count() === 1) {
                return [$candidates->first(), null];
            }
            if ($candidates->count() > 1) {
                return [null, "« {$segment} » existe dans plusieurs filières, rangez-le sous le dossier de la filière"];
            }
        }

        return [null, 'aucun dossier au code ou au nom d\'un module'];
    }

    /** @param list<string> $segments */
    private function resolveType(array $segments): DocumentType
    {
        foreach (array_reverse($segments) as $segment) {
            foreach (preg_split('/[^a-z0-9]+/', Str::lower(Str::ascii($segment)), flags: PREG_SPLIT_NO_EMPTY) as $word) {
                if (isset(self::TYPE_WORDS[$word])) {
                    return self::TYPE_WORDS[$word];
                }
            }
        }

        return DocumentType::Cours;
    }

    /** Année la plus récente citée (« 2023-2024 » → 2024), du fichier vers la racine. */
    private function resolveYear(array $segments): ?int
    {
        foreach (array_reverse($segments) as $segment) {
            if (preg_match_all('/(?<!\d)(20\d{2})(?!\d)/', $segment, $matches) > 0) {
                return max(array_map('intval', $matches[1]));
            }
        }

        return null;
    }

    /** « IIIA-ML », « iiia-ml », « IIIA-ML Machine learning » correspondent au code IIIA-ML ; « IIIA-MLX » non. */
    private function matchesCode(string $segment, string $code): bool
    {
        $segment = Str::upper(Str::ascii(trim($segment)));
        $code = Str::upper(Str::ascii(trim($code)));
        if ($code === '' || ! str_starts_with($segment, $code)) {
            return false;
        }

        return strlen($segment) === strlen($code) || ! ctype_alnum($segment[strlen($code)]);
    }

    /** « Développement Web Full-stack » et « developpement web full stack » se valent. */
    private function normalize(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii($value))));
    }

    /**
     * Fichier de correspondance facultatif : {"POO Java 2 IIIA année": "IIIA-2-POO", …}.
     * Un code inconnu arrête la commande avant tout import, plutôt que d'ignorer des fichiers en silence.
     */
    private function loadMap(): bool
    {
        $explicit = $this->option('map');
        $path = (string) ($explicit ?: config('services.google_drive.module_map'));
        if ($path === '' || (! $explicit && ! is_file($path))) {
            return true;
        }

        $map = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        if (! is_array($map)) {
            $this->error("Fichier de correspondance illisible (JSON attendu) : {$path}");

            return false;
        }

        foreach ($map as $folder => $code) {
            if (! is_string($code) || str_starts_with((string) $folder, '_')) {
                continue; // clés « _commentaire » et valeurs vides : ignorées
            }
            $module = $this->modules->filter(fn (Module $m) => Str::upper($m->code) === Str::upper(trim($code)));
            if ($module->count() !== 1) {
                $this->error("Correspondance « {$folder} » : module {$code} ".($module->isEmpty() ? 'inconnu' : 'ambigu').' (codes de php artisan planner:sync).');

                return false;
            }
            $this->map[$this->normalize((string) $folder)] = $module->first();
        }
        $this->line(count($this->map)." correspondance(s) chargée(s) depuis {$path}", verbosity: 'v');

        return true;
    }
}
