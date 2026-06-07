<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AiKind;
use App\Enums\IdeaSource;
use App\Enums\StudyLevel;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Repositories\Contracts\ProjectIdeaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class ProjectIdeaService
{
    public function __construct(
        private readonly ProjectIdeaRepositoryInterface $ideas,
        private readonly ClaudeService $claude,
    ) {}

    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return $this->ideas->search($filters);
    }

    /** @param array<string, mixed> $filters */
    public function browse(array $filters): LengthAwarePaginator
    {
        return $this->ideas->search($filters);
    }

    public function find(string $id): ?ProjectIdea
    {
        return $this->ideas->findWithRelations($id);
    }

    /**
     * @param  array{title: string, description: string, level: string, filiere_id?: string|null, repo_url?: string|null}  $data
     */
    public function create(User $author, array $data, IdeaSource $source = IdeaSource::Student): ProjectIdea
    {
        return $this->ideas->create([
            'user_id' => $author->getKey(),
            'filiere_id' => $data['filiere_id'] ?? $author->filiere_id,
            'title' => $data['title'],
            'description' => $data['description'],
            'level' => $data['level'],
            'source' => $source->value,
            'repo_url' => $data['repo_url'] ?? null,
        ]);
    }

    public function difficultyDots(StudyLevel $level): int
    {
        return match ($level) {
            StudyLevel::L1, StudyLevel::L2 => 1,
            StudyLevel::L3 => 2,
            StudyLevel::M1, StudyLevel::M2 => 3,
        };
    }

    public function maskedAuthorName(ProjectIdea $idea): string
    {
        if ($idea->source === IdeaSource::Ai) {
            return 'StudyLib IA';
        }

        $name = $idea->user?->name;

        if ($name === null || trim($name) === '') {
            return 'Étudiant anonyme';
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $first = $parts[0] ?? '';
        $lastInitial = isset($parts[1]) ? mb_substr($parts[1], 0, 1).'.' : '';

        return trim($first.' '.$lastInitial);
    }

    public function authorInitials(ProjectIdea $idea): string
    {
        $name = $this->maskedAuthorName($idea);
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(
            fn (string $part): string => mb_substr($part, 0, 1),
        )->implode(''));
    }

    /**
     * @return list<ProjectIdea>
     */
    public function generateAiIdeas(User $user, string $filiereName, StudyLevel $level, string $interests): array
    {
        $prompt = sprintf(
            'Tu es un conseiller pédagogique HESTIM. Propose exactement 3 idées de projets CV concrètes pour un étudiant en %s (%s). Centres d\'intérêt : %s. Réponds UNIQUEMENT avec un JSON valide sous la forme [{"title":"...","description":"..."}] sans markdown ni texte autour.',
            $filiereName,
            $level->label(),
            $interests !== '' ? $interests : 'non précisés',
        );

        try {
            $response = $this->claude->suggest($user, AiKind::Project, $prompt);
            $payload = $this->decodeIdeasPayload($this->extractTextFromClaudeResponse($response));

            return $this->persistGeneratedIdeas($user, $level, $payload);
        } catch (\Throwable) {
            return $this->persistGeneratedIdeas($user, $level, $this->fallbackAiPayload($level, $interests));
        }
    }

    /**
     * @param  list<array{title: string, description: string}>  $payload
     * @return list<ProjectIdea>
     */
    private function persistGeneratedIdeas(User $user, StudyLevel $level, array $payload): array
    {
        $created = [];

        foreach (array_slice($payload, 0, 3) as $idea) {
            $created[] = $this->create($user, [
                'title' => Str::limit($idea['title'], 200, ''),
                'description' => $idea['description'],
                'level' => $level->value,
                'filiere_id' => $user->filiere_id,
            ], IdeaSource::Ai);
        }

        return $created;
    }

    /**
     * @return list<array{title: string, description: string}>
     */
    private function decodeIdeasPayload(string $text): array
    {
        $normalized = trim($text);

        if (preg_match('/```(?:json)?\s*(.*?)\s*```/s', $normalized, $matches)) {
            $normalized = trim($matches[1]);
        }

        $decoded = json_decode($normalized, true);

        if (! is_array($decoded)) {
            throw new \RuntimeException('Invalid AI payload');
        }

        $ideas = [];

        foreach ($decoded as $item) {
            if (! is_array($item) || ! isset($item['title'], $item['description'])) {
                continue;
            }

            $ideas[] = [
                'title' => (string) $item['title'],
                'description' => (string) $item['description'],
            ];
        }

        if ($ideas === []) {
            throw new \RuntimeException('Empty AI payload');
        }

        return $ideas;
    }

    /**
     * @return list<array{title: string, description: string}>
     */
    private function fallbackAiPayload(StudyLevel $level, string $interests): array
    {
        $topic = $interests !== '' ? $interests : 'technologies web';

        return [
            [
                'title' => 'Portfolio interactif orienté '.$topic,
                'description' => 'Créez un site portfolio responsive mettant en avant 3 réalisations avec une section dédiée à '.$topic.'. Documentez vos choix techniques dans un README clair.',
            ],
            [
                'title' => 'Mini-application utile à la promo',
                'description' => 'Concevez une application légère qui résout un problème concret rencontré par les étudiants (planning, ressources, entraide). Déployez une démo accessible en ligne.',
            ],
            [
                'title' => 'Projet d\'analyse de données appliqué',
                'description' => 'Collectez un jeu de données lié à '.$topic.', nettoyez-le, produisez 3 visualisations et rédigez une synthèse de recommandations pour un public '.$level->label().'.',
            ],
        ];
    }

    /** @param array<string, mixed> $response */
    private function extractTextFromClaudeResponse(array $response): string
    {
        foreach ($response['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                return trim((string) ($block['text'] ?? ''));
            }
        }

        throw new \RuntimeException('Missing Claude text content');
    }
}
