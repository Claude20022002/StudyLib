<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Recommendation\Criteria;

use App\Models\ProjectIdea;
use App\Models\Tag;
use App\Models\User;
use App\Services\Recommendation\Criteria\SkillTagsMatchCriterion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillTagsMatchCriterionTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_neutral_score_when_project_requires_no_skill(): void
    {
        $student = User::factory()->create();
        $project = ProjectIdea::factory()->create();

        $this->assertSame(0.5, (new SkillTagsMatchCriterion)->score($student, $project));
    }

    public function test_returns_full_score_when_student_masters_every_required_skill(): void
    {
        [$laravel, $react, $postgres] = Tag::factory()->createMany([
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'React', 'slug' => 'react'],
            ['name' => 'PostgreSQL', 'slug' => 'postgresql'],
        ]);

        $student = User::factory()->create();
        $student->tags()->attach([$laravel->id, $react->id, $postgres->id]);

        $project = ProjectIdea::factory()->create();
        $project->tags()->attach([$laravel->id, $react->id]);

        $student->load('tags');
        $project->load('tags');

        $this->assertSame(1.0, (new SkillTagsMatchCriterion)->score($student, $project));
    }

    public function test_returns_partial_score_when_student_masters_only_some_required_skills(): void
    {
        [$laravel, $vue] = Tag::factory()->createMany([
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'Vue.js', 'slug' => 'vuejs'],
        ]);

        $student = User::factory()->create();
        $student->tags()->attach($laravel->id);

        $project = ProjectIdea::factory()->create();
        $project->tags()->attach([$laravel->id, $vue->id]);

        $student->load('tags');
        $project->load('tags');

        $this->assertSame(0.5, (new SkillTagsMatchCriterion)->score($student, $project));
    }
}
