<?php

namespace Crumbls\HelpDesk\Database\Factories;

use Crumbls\HelpDesk\Models\CannedResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

class CannedResponseFactory extends Factory
{
    protected $model = CannedResponse::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'content' => fake()->paragraph(),
            'department_id' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
