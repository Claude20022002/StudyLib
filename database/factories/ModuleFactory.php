<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Filiere;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'code' => strtoupper(fake()->unique()->bothify('MOD-###')),
            'filiere_id' => Filiere::factory(),
            'semester' => fake()->numberBetween(1, 6),
        ];
    }
}
