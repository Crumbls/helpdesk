<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Listeners;

use Crumbls\HelpDesk\Events\CommentCreated;
use Crumbls\HelpDesk\Events\TicketAssigned;
use Crumbls\HelpDesk\Events\TicketCreated;
use Crumbls\HelpDesk\Events\TicketStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches webhook POSTs for helpdesk events.
 *
 * Configure webhooks in helpdesk.webhooks:
 *   [
 *     ['url' => 'https://...', 'events' => ['ticket_created', 'comment_created'], 'secret' => '...'],
 *   ]
 */
class DispatchWebhook implements ShouldQueue
{
    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function handle(object $event): void
    {
        $webhooks = config('helpdesk.webhooks', []);

        if (empty($webhooks)) {
            return;
        }

        $eventName = $this->resolveEventName($event);
        $payload = $this->buildPayload($event, $eventName);

        foreach ($webhooks as $webhook) {
            $allowedEvents = $webhook['events'] ?? ['*'];

            if (!in_array('*', $allowedEvents) && !in_array($eventName, $allowedEvents)) {
                continue;
            }

            $this->send($webhook, $payload);
        }
    }

    protected function resolveEventName(object $event): string
    {
        return match (true) {
            $event instanceof TicketCreated => 'ticket_created',
            $event instanceof TicketStatusChanged => 'ticket_status_changed',
            $event instanceof TicketAssigned => 'ticket_assigned',
            $event instanceof CommentCreated => 'comment_created',
            default => class_basename($event),
        };
    }

    protected function buildPayload(object $event, string $eventName): array
    {
        $data = ['event' => $eventName, 'timestamp' => now()->toIso8601String()];

        if ($event instanceof TicketCreated) {
            $data['ticket'] = $event->ticket->load(['submitter', 'status', 'priority', 'department'])->toArray();
        } elseif ($event instanceof TicketStatusChanged) {
            $data['ticket'] = $event->ticket->toArray();
            $data['old_status_id'] = $event->oldStatus;
            $data['new_status_id'] = $event->newStatus;
        } elseif ($event instanceof TicketAssigned) {
            $data['ticket'] = $event->ticket->toArray();
            $data['user_id'] = $event->user->id ?? $event->user;
            $data['role'] = $event->role;
        } elseif ($event instanceof CommentCreated) {
            $data['comment'] = $event->comment->load(['user', 'ticket'])->toArray();
        }

        return $data;
    }

    protected function send(array $webhook, array $payload): void
    {
        $url = $webhook['url'] ?? null;
        if (!$url) {
            return;
        }

        $headers = ['Content-Type' => 'application/json'];

        // HMAC signature for verification.
        if (!empty($webhook['secret'])) {
            $body = json_encode($payload);
            $headers['X-Helpdesk-Signature'] = hash_hmac('sha256', $body, $webhook['secret']);
        }

        try {
            Http::withHeaders($headers)
                ->timeout(10)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::warning('Helpdesk webhook failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
