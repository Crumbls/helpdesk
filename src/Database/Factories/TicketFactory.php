<?php

namespace Crumbls\HelpDesk\Database\Factories;

use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'ticket_type_id' => TicketType::factory(),
            'ticket_status_id' => TicketStatus::factory(),
            'priority_id' => Priority::factory(),
            'department_id' => Department::factory(),
            'submitter_id' => 1,
            'source' => fake()->randomElement(['web', 'email', 'phone', 'chat']),
            'due_at' => fake()->optional(0.7)->dateTimeBetween('now', '+30 days'),
        ];
    }

    public function closed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'ticket_status_id' => TicketStatus::factory()->closed(),
                'closed_at' => fake()->dateTimeBetween('-30 days', 'now'),
                'resolution' => fake()->paragraphs(2, true),
            ];
        });
    }

    public function urgent(): self
    {
        return $this->state(function () {
            return [
                'priority_id' => Priority::factory()->state(['level' => 10]),
                'due_at' => fake()->dateTimeBetween('now', '+2 days'),
            ];
        });
    }

    public function withParentTicket(): self
    {
        return $this->state(function () {
            return [
                'parent_ticket_id' => Ticket::factory(),
            ];
        });
    }
}
