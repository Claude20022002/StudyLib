<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Filiere;
use App\Models\InternshipReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InternshipReview>
 */
class InternshipReviewFactory extends Factory
{
    protected $model = InternshipReview::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'filiere_id' => Filiere::factory(),
            'position' => fake()->jobTitle(),
            'description' => fake()->paragraphs(2, true),
            'rating' => fake()->numberBetween(1, 5),
            'year_level' => fake()->numberBetween(1, 5),
            'year_done' => (int) fake()->year(),
            'is_paid' => fake()->boolean(),
        ];
    }
}
