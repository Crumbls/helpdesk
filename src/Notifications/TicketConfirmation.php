<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent to the submitter when their ticket is created.
 * Includes estimated response time based on config.
 */
class TicketConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Model $ticket,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = $this->ticket;
        $responseHours = $this->estimatedResponseHours();

        $mail = (new MailMessage())
            ->subject(__('helpdesk::notifications.ticket_confirmation.subject', [
                'id' => $ticket->reference ?? $ticket->id,
            ]))
            ->greeting(__('helpdesk::notifications.ticket_confirmation.greeting', [
                'name' => $notifiable->name,
            ]))
            ->line(__('helpdesk::notifications.ticket_confirmation.line1', [
                'id' => $ticket->reference ?? $ticket->id,
                'title' => $ticket->title,
            ]))
            ->line(__('helpdesk::notifications.ticket_confirmation.line2'));

        // Add estimated response time.
        if ($responseHours) {
            $mail->line($this->formatResponseTime($responseHours));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'reference' => $this->ticket->reference,
            'type' => 'ticket_confirmation',
        ];
    }

    /**
     * Get estimated response hours from SLA config or fallback.
     */
    protected function estimatedResponseHours(): int
    {
        // If SLA is enabled and the ticket has a priority with SLA hours, use that.
        if (config('helpdesk.sla.enabled') && $this->ticket->priority) {
            $hours = $this->ticket->priority->sla_response_hours ?? null;
            if ($hours) {
                return (int) $hours;
            }
        }

        // Fallback to auto-responder config or SLA defaults.
        return (int) config(
            'helpdesk.auto_responder.response_hours',
            config('helpdesk.sla.defaults.response_hours', 24)
        );
    }

    /**
     * Format response time into a human-friendly string.
     */
    protected function formatResponseTime(int $hours): string
    {
        if ($hours <= 1) {
            return 'We typically respond within 1 hour.';
        }

        if ($hours < 24) {
            return "We typically respond within {$hours} hours.";
        }

        $days = (int) round($hours / 24);
        if ($days === 1) {
            return 'We typically respond within 1 business day.';
        }

        return "We typically respond within {$days} business days.";
    }
}
