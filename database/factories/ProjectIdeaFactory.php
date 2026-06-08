<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IdeaSource;
use App\Enums\ProjectDifficulty;
use App\Enums\StudyLevel;
use App\Models\Filiere;
use App\Models\ProjectIdea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectIdea>
 */
class ProjectIdeaFactory extends Factory
{
    protected $model = ProjectIdea::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'filiere_id' => Filiere::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'level' => fake()->randomElement(StudyLevel::cases())->value,
            'difficulty' => fake()->randomElement(ProjectDifficulty::cases())->value,
            'estimated_weeks' => fake()->numberBetween(2, 16),
            'source' => IdeaSource::Student->value,
            'repo_url' => fake()->optional()->url(),
        ];
    }

    public function ai(): static
    {
        return $this->state(fn (): array => [
            'source' => IdeaSource::Ai->value,
        ]);
    }
}
