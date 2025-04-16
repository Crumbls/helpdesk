<?php

namespace Crumbls\HelpDesk\Database\Factories;

use App\Models\User;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketComment;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketCommentFactory extends Factory
{
    protected $model = TicketComment::class;

    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'user_id' => User::inRandomOrder()->first()?->id,
            'content' => fake()->paragraphs(rand(1, 3), true),
            'is_private' => fake()->boolean(20), // 20% chance of being private
            'is_resolution' => false,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Configure the factory to create a private comment.
     */
    public function private(): self
    {
        return $this->state([
            'is_private' => true,
        ]);
    }

    /**
     * Configure the factory to create a resolution comment.
     */
    public function resolution(): self
    {
        return $this->state([
            'is_resolution' => true,
            'content' => fake()->paragraphs(2, true),
        ]);
    }

    /**
     * Configure the factory to create a comment by a specific user.
     */
    public function byUser(User $user): self
    {
        return $this->state([
            'user_id' => $user->id,
        ]);
    }

    /**
     * Configure the factory to create a comment for a specific ticket.
     */
    public function forTicket(Ticket $ticket): self
    {
        return $this->state([
            'ticket_id' => $ticket->id,
            'created_at' => fake()->dateTimeBetween($ticket->created_at, 'now'),
        ]);
    }
}
