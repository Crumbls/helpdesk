<?php

namespace Crumbls\HelpDesk\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Crumbls\HelpDesk\Models\TicketType;

class TicketTypeFactory extends Factory
{
    protected $model = TicketType::class;

    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'description' => fake()->sentence(),
            'color_background' => fake()->randomElement(['#3B82F6', '#6B7280', '#10B981', '#F59E0B']),
            'is_active' => true,
        ];
    }
}
