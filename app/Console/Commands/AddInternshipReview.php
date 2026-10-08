<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Filiere;
use App\Models\InternshipReview;
use App\Services\InternshipReviewService;
use Illuminate\Console\Command;
use Illuminate\Http\File;

/**
 * Ajoute un retour de stage au nom de l'administration (démonstration, retours recueillis hors de
 * l'application), avec une photo facultative. Sans effet si le même poste existe déjà pour cette
 * entreprise. L'accord de publication doit avoir été donné par l'ancien stagiaire.
 *
 *   php artisan studylib:add-internship-review --company="HESTIM FabLab" --city=Casablanca \
 *     --position="Stage en robotique" --description-file=stage.txt --rating=4 --year-done=2026 \
 *     --year-level=3 --filiere=IIIA --photo=robot.jpg --consent
 */
class AddInternshipReview extends Command
{
    protected $signature = 'studylib:add-internship-review
        {--company= : Nom de l\'entreprise}
        {--city= : Ville}
        {--sector= : Secteur}
        {--position= : Intitulé du stage}
        {--description-file= : Fichier texte (UTF-8) contenant le retour}
        {--rating= : Note de 1 à 5}
        {--year-done= : Année du stage}
        {--year-level= : Année d\'études pendant le stage (1 à 5)}
        {--filiere= : Code de la filière (ex. IIIA)}
        {--paid : Stage rémunéré}
        {--photo= : Photo d\'illustration (JPEG, PNG ou WebP, 2 Mo au plus)}
        {--consent : L\'ancien stagiaire a accepté la publication}';

    protected $description = 'Ajoute un retour de stage (démonstration ou retour recueilli), avec une photo facultative';

    private const PHOTO_MAX_OCTETS = 2 * 1024 * 1024;

    private const PHOTO_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function handle(InternshipReviewService $reviews): int
    {
        if (! $this->option('consent')) {
            $this->error('Ajoutez --consent : la publication exige l\'accord de l\'ancien stagiaire.');

            return self::FAILURE;
        }

        $company = trim((string) $this->option('company'));
        $descriptionFile = (string) $this->option('description-file');
        $rating = (int) $this->option('rating');
        if ($company === '' || ! is_file($descriptionFile) || $rating < 1 || $rating > 5) {
            $this->error('--company, --description-file (fichier existant) et --rating (1 à 5) sont obligatoires.');

            return self::FAILURE;
        }

        $position = $this->option('position') ? trim((string) $this->option('position')) : null;
        $existe = InternshipReview::query()
            ->where('position', $position)
            ->whereHas('company', fn ($q) => $q->where('name', $company))
            ->exists();
        if ($existe) {
            $this->info('Ce retour de stage existe déjà : rien à faire.');

            return self::SUCCESS;
        }

        $photo = null;
        if ($chemin = $this->option('photo')) {
            $photo = new File((string) $chemin);
            if ($photo->getSize() > self::PHOTO_MAX_OCTETS || ! in_array($photo->getMimeType(), self::PHOTO_TYPES, true)) {
                $this->error('Photo refusée : JPEG, PNG ou WebP de 2 Mo au plus.');

                return self::FAILURE;
            }
        }

        $filiereId = null;
        if ($code = $this->option('filiere')) {
            $filiereId = Filiere::query()->where('code', $code)->value('id');
            if (! $filiereId) {
                $this->warn("Filière {$code} introuvable : retour enregistré sans filière.");
            }
        }

        $review = $reviews->create(null, [
            'company_name' => $company,
            'company_city' => $this->option('city'),
            'company_sector' => $this->option('sector'),
            'filiere_id' => $filiereId,
            'position' => $position,
            'description' => trim((string) file_get_contents($descriptionFile)),
            'rating' => $rating,
            'year_level' => $this->option('year-level') !== null ? (int) $this->option('year-level') : null,
            'year_done' => $this->option('year-done') !== null ? (int) $this->option('year-done') : null,
            'is_paid' => (bool) $this->option('paid'),
            'photo' => $photo,
        ]);

        $this->info("Retour de stage ajouté ({$review->id})".($review->photo_path ? ', avec photo.' : '.'));

        return self::SUCCESS;
    }
}
