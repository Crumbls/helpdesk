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
	        'color_foreground' => fake()->randomElement(['#EF4444', '#F59E0B', '#10B981', '#3B82F6']),
            'color_background' => fake()->randomElement(['#EF4444', '#F59E0B', '#10B981', '#3B82F6']),
            'level' => fake()->numberBetween(1, 5),
            'is_active' => true,
            'is_default' => false,
        ];
    }
}
