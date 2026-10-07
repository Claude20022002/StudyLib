<?php

declare(strict_types=1);

namespace App\Services\GoogleDrive;

use Firebase\JWT\JWT;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Accès en lecture seule à Google Drive avec un compte de service (API REST v3).
 *
 * Le compte de service s'authentifie par un JWT RS256 échangé contre un jeton d'accès
 * (OAuth 2.0, grant jwt-bearer) : pas besoin du SDK Google, firebase/php-jwt suffit.
 * Le contenu de la clé n'est jamais journalisé ni repris dans un message d'erreur.
 */
class GoogleDriveClient
{
    public const FOLDER = 'application/vnd.google-apps.folder';

    public const SHORTCUT = 'application/vnd.google-apps.shortcut';

    private const API = 'https://www.googleapis.com/drive/v3';

    private const SCOPE = 'https://www.googleapis.com/auth/drive.readonly';

    private const TOKEN_URI = 'https://oauth2.googleapis.com/token';

    private ?string $token = null;

    public function __construct(private readonly string $credentialsPath) {}

    /**
     * @return array{id: string, name: string, mimeType: string, size?: string}
     */
    public function file(string $fileId): array
    {
        $response = $this->get(self::API.'/files/'.$this->id($fileId), [
            'fields' => 'id, name, mimeType, size',
            'supportsAllDrives' => 'true',
        ]);

        return $this->ensure($response)->json();
    }

    /**
     * Contenu direct d'un dossier (sans la corbeille), toutes pages confondues.
     *
     * @return list<array{id: string, name: string, mimeType: string, size?: string, shortcutDetails?: array{targetId: string, targetMimeType: string}}>
     */
    public function children(string $folderId): array
    {
        $files = [];
        $pageToken = null;

        do {
            $response = $this->get(self::API.'/files', array_filter([
                'q' => "'{$this->id($folderId)}' in parents and trashed = false",
                'fields' => 'nextPageToken, files(id, name, mimeType, size, shortcutDetails(targetId, targetMimeType))',
                'pageSize' => 1000,
                'supportsAllDrives' => 'true',
                'includeItemsFromAllDrives' => 'true',
                'pageToken' => $pageToken,
            ]));
            array_push($files, ...$this->ensure($response)->json('files', []));
            $pageToken = $response->json('nextPageToken');
        } while (is_string($pageToken) && $pageToken !== '');

        return $files;
    }

    public function download(string $fileId): string
    {
        $response = $this->get(self::API.'/files/'.$this->id($fileId), [
            'alt' => 'media',
            'supportsAllDrives' => 'true',
        ]);

        return $this->ensure($response)->body();
    }

    /** Export d'un fichier Google (Docs, Slides) dans un format bureautique, PDF en général. */
    public function export(string $fileId, string $mimeType): string
    {
        $response = $this->get(self::API.'/files/'.$this->id($fileId).'/export', [
            'mimeType' => $mimeType,
        ]);

        return $this->ensure($response)->body();
    }

    /** @param array<string, mixed> $query */
    private function get(string $url, array $query): Response
    {
        $request = Http::timeout(60)->withToken($this->accessToken());

        return $this->connect(fn () => $request->get($url, $query));
    }

    /**
     * Une panne réseau (DNS, proxy, certificats racine absents du PHP local) devient un
     * message lisible au lieu d'une trace Guzzle.
     *
     * @param  callable(): Response  $send
     */
    private function connect(callable $send): Response
    {
        try {
            return $send();
        } catch (ConnectionException $e) {
            $hint = str_contains($e->getMessage(), 'cURL error 60')
                ? 'certificats racine introuvables : renseignez curl.cainfo et openssl.cafile dans php.ini'
                : $e->getMessage();

            throw new GoogleDriveException("Connexion à Google impossible ({$hint}).", previous: $e);
        }
    }

    private function accessToken(): string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        $raw = is_file($this->credentialsPath) && is_readable($this->credentialsPath)
            ? file_get_contents($this->credentialsPath)
            : false;
        if ($raw === false) {
            throw new GoogleDriveException("Clé du compte de service introuvable ou illisible : {$this->credentialsPath}");
        }

        $key = json_decode($raw, true);
        if (! is_array($key) || ($key['type'] ?? null) !== 'service_account'
            || ! is_string($key['client_email'] ?? null) || ! is_string($key['private_key'] ?? null)) {
            throw new GoogleDriveException("{$this->credentialsPath} n'est pas une clé de compte de service Google.");
        }

        $now = time();
        // L'audience est fixée ici plutôt que lue dans la clé : l'assertion signée ne part que chez Google
        $assertion = JWT::encode([
            'iss' => $key['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URI,
            'iat' => $now,
            'exp' => $now + 3600,
        ], $key['private_key'], 'RS256', is_string($key['private_key_id'] ?? null) ? $key['private_key_id'] : null);

        $response = $this->connect(fn () => Http::asForm()->timeout(15)->post(self::TOKEN_URI, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ]));
        $token = $response->json('access_token');
        if (! $response->successful() || ! is_string($token) || $token === '') {
            $error = is_string($response->json('error')) ? $response->json('error') : 'réponse inattendue';
            throw new GoogleDriveException("Google a refusé la clé du compte de service ({$response->status()}, {$error}).");
        }

        return $this->token = $token;
    }

    private function ensure(Response $response): Response
    {
        if ($response->successful()) {
            return $response;
        }

        $message = $response->json('error.message');
        $detail = match ($response->status()) {
            404 => 'introuvable : le dossier est-il partagé avec le compte de service ?',
            403 => 'accès refusé'.(is_string($message) ? " ({$message})" : ''),
            default => is_string($message) ? $message : 'erreur inattendue',
        };

        throw new GoogleDriveException("Drive a répondu {$response->status()} : {$detail}");
    }

    /** Les identifiants Drive sont alphanumériques ; on refuse tout le reste avant de les insérer dans une requête. */
    private function id(string $id): string
    {
        if (preg_match('/^[A-Za-z0-9_-]{10,200}$/', $id) !== 1) {
            throw new GoogleDriveException('Identifiant Drive invalide.');
        }

        return $id;
    }
}
