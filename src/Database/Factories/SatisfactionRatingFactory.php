<?php

namespace Crumbls\HelpDesk\Database\Factories;

use Crumbls\HelpDesk\Models\SatisfactionRating;
use Illuminate\Database\Eloquent\Factories\Factory;

class SatisfactionRatingFactory extends Factory
{
    protected $model = SatisfactionRating::class;

    public function definition(): array
    {
        return [
            'ticket_id' => null,
            'user_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
