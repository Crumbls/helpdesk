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
            'title' => fake()->department(),
            'description' => fake()->sentence(),
            'color_name' => fake()->randomElement(['primary', 'secondary', 'info']),
            'is_active' => true,
        ];
    }
}
