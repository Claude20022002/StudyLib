<?php

declare(strict_types=1);

namespace App\Services\Offres;

use Carbon\CarbonImmutable;
use Throwable;

/**
 * Tire une offre d'une page HTML : d'abord les données structurées JobPosting (schema.org, que
 * ReKrute, LinkedIn et les pages carrières publient pour Google Jobs), sinon l'aperçu Open Graph
 * (titre et résumé). Rend des champs prêts à pré-remplir le formulaire, tous nettoyés (aucun HTML)
 * et bornés ; le contenu d'une page n'est jamais considéré comme sûr.
 *
 * @phpstan-type Champs array{title: ?string, company_name: ?string, city: ?string, type: string, deadline: ?string, salary: ?string, description: ?string}
 */
class ExtracteurOffre
{
    /** @return array{source: string, champs: array<string, mixed>} source : « jobposting », « apercu » ou « aucune » */
    public function extraire(string $html): array
    {
        $offre = $this->jobPosting($html);
        if ($offre !== null) {
            return ['source' => 'jobposting', 'champs' => $this->depuisJobPosting($offre)];
        }
        $og = $this->openGraph($html);
        if (filled($og['og:title'] ?? null)) {
            return ['source' => 'apercu', 'champs' => [
                'title' => $this->texte($og['og:title'], 200),
                'company_name' => $this->texte($og['og:site_name'] ?? null, 150),
                'city' => null,
                'type' => $this->typeDepuisTitre((string) $og['og:title']),
                'deadline' => null,
                'salary' => null,
                'description' => $this->texte($og['og:description'] ?? null, 5000),
            ]];
        }

        return ['source' => 'aucune', 'champs' => []];
    }

    /** Premier objet JobPosting des blocs JSON-LD (y compris dans @graph ou une liste). */
    private function jobPosting(string $html): ?array
    {
        preg_match_all('#<script[^>]+type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $blocs);
        foreach ($blocs[1] as $bloc) {
            foreach ($this->objets($this->json(trim($bloc))) as $objet) {
                $type = $objet['@type'] ?? null;
                if ($type === 'JobPosting' || (is_array($type) && in_array('JobPosting', $type, true))) {
                    return $objet;
                }
            }
        }

        return null;
    }

    /**
     * JSON tolérant : certains sites (ReKrute) laissent des retours à la ligne bruts dans les
     * chaînes, ce que le JSON interdit ; on les échappe, puis on essaie le bloc encodé en entités HTML.
     */
    private function json(string $bloc): mixed
    {
        foreach ([$bloc, $this->echapperControles($bloc), html_entity_decode($bloc, ENT_QUOTES | ENT_HTML5)] as $essai) {
            $donnees = json_decode($essai, true);
            if (is_array($donnees)) {
                return $donnees;
            }
        }

        return null;
    }

    /** Échappe les caractères de contrôle à l'intérieur des chaînes JSON seulement. */
    private function echapperControles(string $json): string
    {
        $sortie = '';
        $dansChaine = false;
        $echappe = false;
        $n = strlen($json);
        for ($i = 0; $i < $n; $i++) {
            $c = $json[$i];
            if ($dansChaine && ! $echappe && ord($c) < 0x20) {
                $sortie .= match (ord($c)) {
                    10 => '\n',
                    9 => '\t',
                    13 => '',
                    default => ' ',
                };

                continue;
            }
            if ($c === '"' && ! $echappe) {
                $dansChaine = ! $dansChaine;
            }
            $echappe = $c === '\\' && ! $echappe;
            $sortie .= $c;
        }

        return $sortie;
    }

    /** @return iterable<array<string, mixed>> */
    private function objets(mixed $donnees): iterable
    {
        if (! is_array($donnees)) {
            return;
        }
        if (array_is_list($donnees)) {
            foreach ($donnees as $d) {
                yield from $this->objets($d);
            }

            return;
        }
        yield $donnees;
        if (isset($donnees['@graph'])) {
            yield from $this->objets($donnees['@graph']);
        }
    }

    private function depuisJobPosting(array $o): array
    {
        $organisation = $o['hiringOrganization'] ?? null;
        $lieu = $o['jobLocation'] ?? null;
        $lieu = is_array($lieu) && array_is_list($lieu) ? ($lieu[0] ?? null) : $lieu;
        $types = (array) ($o['employmentType'] ?? []);
        $titre = $this->texte($o['title'] ?? null, 200);

        return [
            'title' => $titre,
            'company_name' => $this->texte(is_array($organisation) ? ($organisation['name'] ?? null) : $organisation, 150),
            'city' => $this->texte(is_array($lieu) ? ($lieu['address']['addressLocality'] ?? $lieu['address']['addressRegion'] ?? null) : null, 120),
            'type' => in_array('INTERN', array_map('strtoupper', array_filter($types, 'is_string')), true) ? 'stage' : $this->typeDepuisTitre((string) $titre),
            'deadline' => $this->date($o['validThrough'] ?? null),
            'salary' => $this->salaire($o['baseSalary'] ?? null),
            'description' => $this->texte($o['description'] ?? null, 5000, lignes: true),
        ];
    }

    /** « Stage », « stagiaire », « PFE » : un stage ; « alternance » ; sinon un emploi. */
    private function typeDepuisTitre(string $titre): string
    {
        $t = mb_strtolower($titre);

        return match (true) {
            (bool) preg_match('/\b(stag|pfe|intern)/u', $t) => 'stage',
            str_contains($t, 'alternan') => 'alternance',
            default => 'emploi',
        };
    }

    private function date(mixed $valeur): ?string
    {
        if (! is_string($valeur) || $valeur === '') {
            return null;
        }
        try {
            $date = CarbonImmutable::parse($valeur);
        } catch (Throwable) {
            return null;
        }

        return $date->isPast() ? null : $date->toDateString();
    }

    private function salaire(mixed $s): ?string
    {
        if (! is_array($s)) {
            return null;
        }
        $v = $s['value'] ?? null;
        $montant = is_array($v) ? (isset($v['minValue'], $v['maxValue']) ? "{$v['minValue']} – {$v['maxValue']}" : ($v['value'] ?? null)) : $v;
        if ($montant === null || $montant === '') {
            return null;
        }
        $unite = is_array($v) ? ($v['unitText'] ?? null) : null;

        return $this->texte(trim("$montant ".($s['currency'] ?? '').($unite ? ' / '.mb_strtolower((string) $unite) : '')), 150);
    }

    /** @return array<string, string> */
    private function openGraph(string $html): array
    {
        preg_match_all('#<meta[^>]+property=["\'](og:[a-z_]+)["\'][^>]+content=["\']([^"\']*)["\']#i', $html, $m, PREG_SET_ORDER);

        return collect($m)->mapWithKeys(fn ($x) => [strtolower($x[1]) => html_entity_decode($x[2], ENT_QUOTES | ENT_HTML5)])->all();
    }

    /** Texte brut : balises retirées, entités décodées, espaces resserrés (sauts de ligne gardés si demandé), borné. */
    private function texte(mixed $valeur, int $max, bool $lignes = false): ?string
    {
        if (! is_string($valeur)) {
            return null;
        }
        // Scripts et styles retirés avec leur contenu, puis les balises ; les fins de paragraphe deviennent des sauts de ligne
        $sans = preg_replace(['#<(script|style)\b[^>]*>.*?</\1>#is', '#<(br|/p|/li|/div)[^>]*>#i'], ['', "\n"], $valeur);
        $texte = html_entity_decode(strip_tags((string) $sans), ENT_QUOTES | ENT_HTML5);
        $texte = $lignes ? preg_replace(["/[ \t\u{00A0}]+/u", "/ ?\n ?/", "/\n{3,}/"], [' ', "\n", "\n\n"], $texte) : preg_replace('/\s+/u', ' ', $texte);
        $texte = trim((string) $texte);

        return $texte === '' ? null : mb_substr($texte, 0, $max);
    }
}
