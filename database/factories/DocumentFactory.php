<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Document;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'module_id' => Module::factory(),
            'type' => fake()->randomElement(DocumentType::cases())->value,
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'file_size' => fake()->numberBetween(100_000, 5_000_000),
            'mime_type' => 'application/pdf',
            'year_concern' => fake()->numberBetween(2020, 2026),
            'status' => DocumentStatus::Approved->value,
            'downloads_count' => 0,
            'ratings_count' => 0,
            'avg_rating' => 0,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => DocumentStatus::Pending->value]);
    }
}
