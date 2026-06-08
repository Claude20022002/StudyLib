<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Recommendation\Criteria;

use App\Models\Filiere;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Criteria\FiliereMatchCriterion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiliereMatchCriterionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_full_score_when_filiere_matches(): void
    {
        $filiere = Filiere::factory()->create();
        $student = User::factory()->create(['filiere_id' => $filiere->id]);
        $project = ProjectIdea::factory()->create(['filiere_id' => $filiere->id]);

        $this->assertSame(1.0, (new FiliereMatchCriterion())->score($student, $project));
    }

    public function test_returns_zero_when_filiere_differs(): void
    {
        $student = User::factory()->create(['filiere_id' => Filiere::factory()->create()->id]);
        $project = ProjectIdea::factory()->create(['filiere_id' => Filiere::factory()->create()->id]);

        $this->assertSame(0.0, (new FiliereMatchCriterion())->score($student, $project));
    }

    public function test_returns_neutral_score_when_project_has_no_filiere(): void
    {
        $student = User::factory()->create(['filiere_id' => Filiere::factory()->create()->id]);
        $project = ProjectIdea::factory()->create(['filiere_id' => null]);

        $this->assertSame(0.5, (new FiliereMatchCriterion())->score($student, $project));
    }

    public function test_returns_neutral_score_when_student_has_no_filiere(): void
    {
        $student = User::factory()->create(['filiere_id' => null]);
        $project = ProjectIdea::factory()->create(['filiere_id' => Filiere::factory()->create()->id]);

        $this->assertSame(0.5, (new FiliereMatchCriterion())->score($student, $project));
    }
}
