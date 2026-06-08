<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Recommendation\Criteria;

use App\Models\Module;
use App\Models\ProjectIdea;
use App\Models\User;
use App\Services\Recommendation\Criteria\FollowedModulesMatchCriterion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowedModulesMatchCriterionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_neutral_score_when_project_has_no_required_modules(): void
    {
        $student = User::factory()->create();
        $project = ProjectIdea::factory()->create();

        $this->assertSame(0.5, (new FollowedModulesMatchCriterion())->score($student, $project));
    }

    public function test_returns_full_score_when_every_required_module_is_followed(): void
    {
        $modules = Module::factory()->count(2)->create();

        $student = User::factory()->create();
        $student->modules()->attach($modules->pluck('id'));

        $project = ProjectIdea::factory()->create();
        $project->modules()->attach($modules->first()->id);

        $student->load('modules');
        $project->load('modules');

        $this->assertSame(1.0, (new FollowedModulesMatchCriterion())->score($student, $project));
    }

    public function test_returns_partial_score_for_partial_overlap(): void
    {
        $modules = Module::factory()->count(2)->create();

        $student = User::factory()->create();
        $student->modules()->attach($modules->first()->id);

        $project = ProjectIdea::factory()->create();
        $project->modules()->attach($modules->pluck('id'));

        $student->load('modules');
        $project->load('modules');

        $this->assertSame(0.5, (new FollowedModulesMatchCriterion())->score($student, $project));
    }

    public function test_returns_zero_when_no_required_module_is_followed(): void
    {
        $followed = Module::factory()->create();
        $required = Module::factory()->create();

        $student = User::factory()->create();
        $student->modules()->attach($followed->id);

        $project = ProjectIdea::factory()->create();
        $project->modules()->attach($required->id);

        $student->load('modules');
        $project->load('modules');

        $this->assertSame(0.0, (new FollowedModulesMatchCriterion())->score($student, $project));
    }
}
