<?php

namespace Database\Factories;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Issue>
 */
class IssueFactory extends Factory
{
    protected $model = Issue::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['planning', 'active', 'completed', 'on_hold']),
            'is_initiative' => false,
            'start_date' => fake()->date(),
            'tags' => [],
        ];
    }

    public function initiative(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_initiative' => true,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }
}
