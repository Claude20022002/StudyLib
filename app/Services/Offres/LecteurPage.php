<?php

declare(strict_types=1);

namespace App\Services\Offres;

use Closure;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Lit la page d'une offre donnée par un utilisateur, sans que le serveur puisse être détourné vers
 * le réseau interne (Planner, MinIO, la base…) :
 *  - https seulement, port 443, pas d'identifiants dans l'adresse ;
 *  - chaque adresse IP de l'hôte doit être publique, et la connexion est épinglée sur l'adresse
 *    vérifiée (pas de seconde résolution DNS qui changerait de cible) ;
 *  - trois redirections au plus, chacune vérifiée de la même façon ;
 *  - 8 secondes, 1,5 Mo, du HTML seulement ; aucun JavaScript n'est exécuté.
 * Les sites qui refusent la lecture automatique (conditions d'utilisation, ou refus constaté) ne
 * sont pas lus : l'utilisateur colle le texte de l'offre.
 */
class LecteurPage
{
    public const TAILLE_MAX = 1_500_000;

    /** Hôtes à ne pas lire : interdit par leurs conditions (LinkedIn), ou refus constaté (Indeed, Emploi.ma : 403), tous pays */
    public const REFUSES = '/(^|\.)(linkedin|indeed)\.[a-z.]{2,12}$|(^|\.)emploi\.ma$/';

    /** @var Closure(string): list<string> */
    private Closure $resoudre;

    /** @param  (Closure(string): list<string>)|null  $resoudre  résolution DNS (remplaçable dans les tests) */
    public function __construct(?Closure $resoudre = null)
    {
        $this->resoudre = $resoudre ?? fn (string $hote): array => array_values(array_unique(array_merge(
            gethostbynamel($hote) ?: [],
            array_column(@dns_get_record($hote, DNS_AAAA) ?: [], 'ipv6'),
        )));
    }

    /** Vrai si l'hôte refuse la lecture automatique (on proposera de coller le texte). */
    public static function refuse(string $url): bool
    {
        return preg_match(self::REFUSES, strtolower((string) parse_url($url, PHP_URL_HOST))) === 1;
    }

    /**
     * Rend le HTML de la page, ou lève une ErreurLecture avec un message lisible.
     */
    public function lire(string $url): string
    {
        for ($saut = 0; $saut <= 3; $saut++) {
            [$hote, $ip] = $this->verifier($url);
            $tropGros = false;
            try {
                $reponse = Http::withOptions([
                    'allow_redirects' => false,
                    // Connexion épinglée sur l'adresse vérifiée
                    'curl' => [CURLOPT_RESOLVE => ["$hote:443:$ip"]],
                    // Téléchargement interrompu dès que la page dépasse la taille permise
                    'progress' => function (int $total, int $recu) use (&$tropGros): bool {
                        return $tropGros = $total > self::TAILLE_MAX || $recu > self::TAILLE_MAX;
                    },
                ])->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; HESTIM-Planner/1.0; +https://planner.finadmintech.fr)', 'Accept' => 'text/html', 'Accept-Language' => 'fr'])
                    ->connectTimeout(4)->timeout(8)->get($url);
            } catch (Throwable) {
                throw new ErreurLecture($tropGros ? 'Cette page est trop volumineuse.' : 'Le site ne répond pas.');
            }
            if ($reponse->redirect()) {
                $url = $this->absolue((string) $reponse->header('Location'), $url);

                continue;
            }
            if (! $reponse->successful()) {
                throw new ErreurLecture("Le site refuse la lecture de cette page ({$reponse->status()}).");
            }
            if (! str_contains(strtolower((string) $reponse->header('Content-Type')), 'text/html')) {
                throw new ErreurLecture("Ce lien ne mène pas à une page d'offre.");
            }

            return substr($reponse->body(), 0, self::TAILLE_MAX);
        }
        throw new ErreurLecture('Trop de redirections.');
    }

    /** @return array{0: string, 1: string} hôte et adresse IP publique vérifiée */
    private function verifier(string $url): array
    {
        $p = parse_url($url);
        if (($p['scheme'] ?? '') !== 'https' || empty($p['host']) || isset($p['user']) || isset($p['pass']) || (isset($p['port']) && (int) $p['port'] !== 443)) {
            throw new ErreurLecture('Adresse invalide : un lien https:// est attendu.');
        }
        if (self::refuse($url)) {
            throw new ErreurLecture('Ce site ne permet pas la lecture automatique : collez le texte de l’offre.', refuse: true);
        }
        $hote = strtolower($p['host']);
        $ips = ($this->resoudre)($hote);
        $publiques = array_filter($ips, fn (string $ip) => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false);
        if ($ips === [] || count($publiques) !== count($ips)) {
            throw new ErreurLecture('Adresse invalide.');
        }

        return [$hote, str_contains($ips[0], ':') ? "[{$ips[0]}]" : $ips[0]];
    }

    private function absolue(string $cible, string $base): string
    {
        if ($cible === '') {
            throw new ErreurLecture('Redirection sans destination.');
        }
        if (preg_match('#^https?://#i', $cible)) {
            return $cible;
        }
        $b = parse_url($base);

        return "https://{$b['host']}".(str_starts_with($cible, '/') ? $cible : '/'.$cible);
    }
}
