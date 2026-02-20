<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Console\Commands;

use Crumbls\HelpDesk\Events\TicketStatusChanged;
use Crumbls\HelpDesk\Models;
use Illuminate\Console\Command;

class AutoCloseStaleTickets extends Command
{
    protected $signature = 'helpdesk:auto-close';

    protected $description = 'Close tickets with no activity for X days';

    public function handle(): int
    {
        if (!config('helpdesk.auto_close.enabled', false)) {
            $this->warn('Auto-close is disabled in config.');
            return Command::SUCCESS;
        }

        $days = (int) config('helpdesk.auto_close.days', 14);
        $closedStatusId = config('helpdesk.auto_close.closed_status_id');

        if (!$closedStatusId) {
            $this->error('Auto-close closed_status_id is not set in config.');
            return Command::FAILURE;
        }

        $ticketClass = Models::ticket();
        $statusClass = Models::status();
        $commentClass = Models::comment();

        // Verify the closed status exists.
        $closedStatus = $statusClass::find($closedStatusId);

        if (!$closedStatus) {
            $this->error("Closed status ID {$closedStatusId} not found.");
            return Command::FAILURE;
        }

        // Get all non-closed statuses.
        $nonClosedStatuses = $statusClass::where('is_closed', false)->pluck('id');

        // Find stale tickets.
        $staleTickets = $ticketClass::whereIn('ticket_status_id', $nonClosedStatuses)
            ->where('updated_at', '<', now()->subDays($days))
            ->whereNull('closed_at')
            ->get();

        $count = 0;

        foreach ($staleTickets as $ticket) {
            $oldStatusId = $ticket->ticket_status_id;

            $ticket->update([
                'ticket_status_id' => $closedStatusId,
                'closed_at' => now(),
            ]);

            // Add system comment.
            $commentClass::create([
                'ticket_id' => $ticket->id,
                'user_id' => null,
                'content' => 'Ticket automatically closed due to inactivity.',
                'is_private' => false,
                'is_resolution' => false,
            ]);

            // Fire event.
            if (config('helpdesk.events.enabled') && config('helpdesk.events.dispatch.ticket_status_changed')) {
                event(new TicketStatusChanged($ticket, $oldStatusId, $closedStatusId));
            }

            $count++;
        }

        $this->info("Closed {$count} stale ticket(s).");

        return Command::SUCCESS;
    }
}
