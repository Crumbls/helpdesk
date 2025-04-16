<?php

namespace Crumbls\HelpDesk\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Crumbls\HelpDesk\Models\Priority;

class PriorityFactory extends Factory
{
    protected $model = Priority::class;

    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'description' => fake()->sentence(),
            'color_name' => fake()->randomElement(['danger', 'warning', 'success', 'info']),
            'level' => fake()->numberBetween(1, 5),
            'is_active' => true,
            'is_default' => false,
        ];
    }
}
