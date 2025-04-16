<?php

namespace Crumbls\HelpDesk\Database\Factories;

use App\Models\User;
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
        $closedStatus = TicketStatus::where('is_closed', true)->first();
        $defaultStatus = TicketStatus::where('is_default', true)->first();

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'resolution' => null,
            'ticket_type_id' => TicketType::inRandomOrder()->first()?->id,
            'ticket_status_id' => $defaultStatus?->id,
            'priority_id' => Priority::inRandomOrder()->first()?->id,
            'department_id' => Department::inRandomOrder()->first()?->id,
            'submitter_id' => User::inRandomOrder()->first()?->id,
            'source' => fake()->randomElement(['web', 'email', 'phone', 'chat']),
            'due_at' => fake()->optional(0.7)->dateTimeBetween('now', '+30 days'),
            'closed_at' => null,
            'created_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'updated_at' => function (array $attributes) {
                return fake()->dateTimeBetween($attributes['created_at'], 'now');
            },
        ];
    }

    /**
     * Configure the factory to create a closed ticket.
     */
    public function closed(): self
    {
        return $this->state(function (array $attributes) {
            $closedStatus = TicketStatus::where('is_closed', true)->first();
            $closedAt = fake()->dateTimeBetween($attributes['created_at'], 'now');
            
            return [
                'ticket_status_id' => $closedStatus?->id,
                'closed_at' => $closedAt,
                'resolution' => fake()->paragraphs(2, true),
                'updated_at' => fake()->dateTimeBetween($closedAt, 'now'),
            ];
        });
    }

    /**
     * Configure the factory to create an urgent ticket.
     */
    public function urgent(): self
    {
        return $this->state(function () {
            $urgentPriority = Priority::orderByDesc('level')->first();
            
            return [
                'priority_id' => $urgentPriority?->id,
                'due_at' => fake()->dateTimeBetween('now', '+2 days'),
            ];
        });
    }

    /**
     * Configure the factory to assign random users to the ticket.
     */
    public function withRandomAssignees(int $count = 1): self
    {
        return $this->afterCreating(function (Ticket $ticket) use ($count) {
            $assignees = User::inRandomOrder()->limit($count)->get();
            $ticket->assignees()->attach($assignees);
        });
    }

    /**
     * Configure the factory to add random watchers to the ticket.
     */
    public function withRandomWatchers(int $count = 1): self
    {
        return $this->afterCreating(function (Ticket $ticket) use ($count) {
            $watchers = User::inRandomOrder()
                ->whereNotIn('id', $ticket->assignees->pluck('id'))
                ->limit($count)
                ->get();
            $ticket->watchers()->attach($watchers);
        });
    }

    /**
     * Configure the factory to create a ticket with a parent ticket.
     */
    public function withParentTicket(): self
    {
        return $this->state(function () {
            return [
                'parent_ticket_id' => Ticket::factory(),
            ];
        });
    }

    /**
     * Configure the factory to add random comments to the ticket.
     */
    public function withComments(?int $count = null): self
    {
        return $this->afterCreating(function (Ticket $ticket) use ($count) {
            $commentsCount = $count ?? fake()->numberBetween(1, 5);
            
            // Create regular comments
            TicketComment::factory()
                ->count($commentsCount)
                ->forTicket($ticket)
                ->create();

            // 30% chance of having a private comment
            if (fake()->boolean(30)) {
                TicketComment::factory()
                    ->private()
                    ->forTicket($ticket)
                    ->create();
            }

            // If ticket is closed, add a resolution comment
            if ($ticket->closed_at) {
                TicketComment::factory()
                    ->resolution()
                    ->forTicket($ticket)
                    ->create();
            }
        });
    }
}
