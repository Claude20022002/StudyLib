<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Recommendation;

use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Contracts\ProjectMatchCriterion;
use App\Services\Recommendation\ProjectMatchScorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectMatchScorerTest extends TestCase
{
    use RefreshDatabase;

    public function test_aggregates_criteria_into_a_weighted_global_score_on_one_hundred(): void
    {
        $student = User::factory()->create();
        $project = ProjectIdea::factory()->create();

        $scorer = new ProjectMatchScorer([
            $this->criterionStub('a', weight: 1.0, score: 1.0),
            $this->criterionStub('b', weight: 3.0, score: 0.0),
        ]);

        $result = $scorer->score($student, $project);

        // (1.0 * 1.0 + 3.0 * 0.0) / (1.0 + 3.0) = 0.25 -> 25.0 / 100
        $this->assertSame(25.0, $result->score);
        $this->assertSame(['a' => 1.0, 'b' => 0.0], $result->breakdown);
        $this->assertSame($project, $result->project);
    }

    public function test_clamps_out_of_range_criterion_scores_before_aggregating(): void
    {
        $student = User::factory()->create();
        $project = ProjectIdea::factory()->create();

        $scorer = new ProjectMatchScorer([
            $this->criterionStub('over', weight: 1.0, score: 1.8),
            $this->criterionStub('under', weight: 1.0, score: -0.4),
        ]);

        $result = $scorer->score($student, $project);

        $this->assertSame(['over' => 1.0, 'under' => 0.0], $result->breakdown);
        $this->assertSame(50.0, $result->score);
    }

    public function test_default_composition_uses_only_business_rule_criteria(): void
    {
        $student = User::factory()->create([
            'filiere_id' => null,
            'year_level' => null,
        ]);
        $project = ProjectIdea::factory()->create(['filiere_id' => null]);

        $result = ProjectMatchScorer::default()->score($student, $project);

        // Tous les critères renvoient un score neutre (0.5) faute de signal exploitable
        // -> moyenne pondérée = 0.5 -> 50.0 / 100, sans qu'aucun LLM n'ait été sollicité.
        $this->assertEqualsWithDelta(50.0, $result->score, 0.01);
        $this->assertSame(['filiere', 'level', 'modules', 'tags'], array_keys($result->breakdown));
    }

    private function criterionStub(string $key, float $weight, float $score): ProjectMatchCriterion
    {
        return new class($key, $weight, $score) implements ProjectMatchCriterion
        {
            public function __construct(
                private readonly string $key,
                private readonly float $weight,
                private readonly float $score,
            ) {}

            public function weight(): float
            {
                return $this->weight;
            }

            public function key(): string
            {
                return $this->key;
            }

            public function score(User $student, ProjectIdea $project): float
            {
                return $this->score;
            }
        };
    }
}
