<?php

namespace Crumbls\HelpDesk\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Crumbls\HelpDesk\Models\TicketStatus;

class TicketStatusFactory extends Factory
{
    protected $model = TicketStatus::class;

    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'description' => fake()->sentence(),
            'color_background' => fake()->randomElement(['#3B82F6', '#F59E0B', '#10B981', '#EF4444']),
            'is_active' => true,
            'is_default' => false,
            'is_closed' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_closed' => true,
            'color_background' => '#10B981',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
