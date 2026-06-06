<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Event $event): void {
            if ($event->starts_at === null) {
                return;
            }

            if ($event->ends_at === null || $event->starts_at >= $event->ends_at) {
                $event->ends_at = $event->starts_at->copy()->addHours(2);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+1 month');

        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+'.fake()->numberBetween(1, 3).' hours'),
            'location' => fake()->optional()->words(2, true),
        ];
    }
}
