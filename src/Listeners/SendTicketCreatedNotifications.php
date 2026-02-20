<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Listeners;

use Crumbls\HelpDesk\Events\TicketCreated;
use Crumbls\HelpDesk\Models;
use Crumbls\HelpDesk\Notifications\AdminNewTicket;
use Crumbls\HelpDesk\Notifications\TicketConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTicketCreatedNotifications implements ShouldQueue
{
    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;
        $config = config('helpdesk.notifications', []);

        // Notify admins.
        if ($config['admin_new_ticket'] ?? true) {
            $this->notifyAdmins(new AdminNewTicket($ticket));
        }

        // Confirm to submitter.
        if ($config['submitter_confirmation'] ?? true) {
            $ticket->submitter?->notify(new TicketConfirmation($ticket));
        }
    }

    protected function notifyAdmins($notification): void
    {
        $recipients = $this->resolveAdminRecipients();

        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
        }
    }

    protected function resolveAdminRecipients(): array
    {
        $userClass = Models::user();
        $recipients = [];

        // By admin IDs from config.
        $adminIds = (array) config('helpdesk.notifications.admin_user_ids', []);
        if ($adminIds) {
            $recipients = $userClass::whereIn('id', $adminIds)->get()->all();
        }

        // By admin emails from config (fallback / additional).
        $adminEmails = (array) config('helpdesk.notifications.admin_emails', []);
        if ($adminEmails) {
            $byEmail = $userClass::whereIn('email', $adminEmails)->get()->all();
            $existingIds = array_map(fn($r) => $r->id, $recipients);
            foreach ($byEmail as $user) {
                if (!in_array($user->id, $existingIds)) {
                    $recipients[] = $user;
                }
            }
        }

        return $recipients;
    }
}
