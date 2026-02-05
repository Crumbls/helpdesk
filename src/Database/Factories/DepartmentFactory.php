<?php

namespace Crumbls\HelpDesk\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Crumbls\HelpDesk\Models\Department;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'title' => fake()->word(),
            'description' => fake()->sentence(),
            'color_background' => fake()->randomElement(['#3B82F6', '#6B7280', '#3B82F6']),
            'is_active' => true,
        ];
    }
}
