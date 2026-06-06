<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\IdeaSource;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Repositories\Contracts\ProjectIdeaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectIdeaService
{
    public function __construct(
        private readonly ProjectIdeaRepositoryInterface $ideas,
    ) {}

    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return $this->ideas->search($filters);
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
}
