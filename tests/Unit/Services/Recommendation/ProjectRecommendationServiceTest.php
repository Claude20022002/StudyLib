<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Recommendation;

use App\Enums\StudyLevel;
use App\Models\Filiere;
use App\Models\Module;
use App\Models\ProjectIdea;
use App\Models\Tag;
use App\Models\User;
use App\Services\Recommendation\ProjectRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectRecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_recommends_projects_ranked_by_compatibility_with_student_profile(): void
    {
        $genieInformatique = Filiere::factory()->create(['name' => 'Génie Informatique', 'code' => 'GI']);
        $autreFiliere = Filiere::factory()->create();

        [$laravel, $react, $postgres, $vue, $java, $spring] = Tag::factory()->createMany([
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'React', 'slug' => 'react'],
            ['name' => 'PostgreSQL', 'slug' => 'postgresql'],
            ['name' => 'Vue.js', 'slug' => 'vuejs'],
            ['name' => 'Java', 'slug' => 'java'],
            ['name' => 'Spring', 'slug' => 'spring'],
        ]);

        [$moduleA, $moduleB, $moduleC] = Module::factory()->count(3)->create(['filiere_id' => $genieInformatique->id]);

        // Étudiant de l'exemple de l'énoncé : Génie Informatique, L3, Laravel/React/PostgreSQL
        $student = User::factory()->create([
            'filiere_id' => $genieInformatique->id,
            'year_level' => 3,
        ]);
        $student->tags()->attach([$laravel->id, $react->id, $postgres->id]);
        $student->modules()->attach([$moduleA->id, $moduleB->id]);

        // Compatibilité parfaite : même filière, même niveau, compétences et module couverts
        $bestMatch = ProjectIdea::factory()->create([
            'filiere_id' => $genieInformatique->id,
            'level' => StudyLevel::L3->value,
        ]);
        $bestMatch->tags()->attach([$laravel->id, $react->id]);
        $bestMatch->modules()->attach($moduleA->id);

        // Compatibilité partielle : filière ok, niveau adjacent, une seule compétence couverte
        $partialMatch = ProjectIdea::factory()->create([
            'filiere_id' => $genieInformatique->id,
            'level' => StudyLevel::L2->value,
        ]);
        $partialMatch->tags()->attach([$laravel->id, $vue->id]);

        // Aucune compatibilité réelle : autre filière, niveau distant, autre stack technique
        $weakMatch = ProjectIdea::factory()->create([
            'filiere_id' => $autreFiliere->id,
            'level' => StudyLevel::M2->value,
        ]);
        $weakMatch->tags()->attach([$java->id, $spring->id]);
        $weakMatch->modules()->attach($moduleC->id);

        // Idée publiée par l'étudiant lui-même : ne doit jamais lui être "recommandée"
        $ownIdea = ProjectIdea::factory()->create([
            'user_id' => $student->id,
            'filiere_id' => $genieInformatique->id,
            'level' => StudyLevel::L3->value,
        ]);
        $ownIdea->tags()->attach([$laravel->id, $react->id, $postgres->id]);

        $recommendations = app(ProjectRecommendationService::class)->recommendFor($student);

        $this->assertCount(2, $recommendations);

        $this->assertSame($bestMatch->id, $recommendations[0]->project->id);
        $this->assertSame(100.0, $recommendations[0]->score);

        $this->assertSame($partialMatch->id, $recommendations[1]->project->id);
        $this->assertSame(65.0, $recommendations[1]->score);

        $recommendedIds = collect($recommendations)->map(fn ($match) => $match->project->id);

        $this->assertFalse(
            $recommendedIds->contains($weakMatch->id),
            'Un projet sans aucune affinité réelle ne doit pas être recommandé.',
        );
        $this->assertFalse(
            $recommendedIds->contains($ownIdea->id),
            'Un étudiant ne doit jamais se voir recommander ses propres idées.',
        );
    }

    public function test_limits_the_number_of_returned_recommendations(): void
    {
        $filiere = Filiere::factory()->create();
        $student = User::factory()->create(['filiere_id' => $filiere->id, 'year_level' => 3]);

        ProjectIdea::factory()->count(5)->create([
            'filiere_id' => $filiere->id,
            'level' => StudyLevel::L3->value,
        ]);

        $recommendations = app(ProjectRecommendationService::class)->recommendFor($student, limit: 2);

        $this->assertCount(2, $recommendations);
    }

    public function test_match_for_exposes_the_score_breakdown_for_a_single_project(): void
    {
        $filiere = Filiere::factory()->create();
        $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

        $student = User::factory()->create(['filiere_id' => $filiere->id, 'year_level' => 3]);
        $student->tags()->attach($tag->id);

        $project = ProjectIdea::factory()->create([
            'filiere_id' => $filiere->id,
            'level' => StudyLevel::L3->value,
        ]);
        $project->tags()->attach($tag->id);

        $match = app(ProjectRecommendationService::class)->matchFor($student, $project);

        $this->assertSame(100.0, $match->score);
        $this->assertSame(
            ['filiere' => 1.0, 'level' => 1.0, 'modules' => 0.5, 'tags' => 1.0],
            $match->breakdown,
        );
    }
}
