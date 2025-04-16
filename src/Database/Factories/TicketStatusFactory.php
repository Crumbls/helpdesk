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
            'color_name' => fake()->randomElement(['primary', 'warning', 'success', 'danger']),
            'is_active' => true,
            'is_default' => false,
            'is_closed' => false,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_closed' => true,
            'color_name' => 'success',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
