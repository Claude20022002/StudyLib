<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Recommendation\Criteria;

use App\Enums\StudyLevel;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Criteria\LevelMatchCriterion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelMatchCriterionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_full_score_for_exact_level_match(): void
    {
        $student = User::factory()->create(['year_level' => 3]);
        $project = ProjectIdea::factory()->create(['level' => StudyLevel::L3->value]);

        $this->assertSame(1.0, (new LevelMatchCriterion)->score($student, $project));
    }

    public function test_returns_partial_score_for_adjacent_level(): void
    {
        $student = User::factory()->create(['year_level' => 3]);
        $project = ProjectIdea::factory()->create(['level' => StudyLevel::M1->value]);

        $this->assertSame(0.5, (new LevelMatchCriterion)->score($student, $project));
    }

    public function test_returns_zero_for_distant_level(): void
    {
        $student = User::factory()->create(['year_level' => 1]);
        $project = ProjectIdea::factory()->create(['level' => StudyLevel::M2->value]);

        $this->assertSame(0.0, (new LevelMatchCriterion)->score($student, $project));
    }

    public function test_returns_neutral_score_when_student_has_no_year_level(): void
    {
        $student = User::factory()->create(['year_level' => null]);
        $project = ProjectIdea::factory()->create(['level' => StudyLevel::L3->value]);

        $this->assertSame(0.5, (new LevelMatchCriterion)->score($student, $project));
    }
}
