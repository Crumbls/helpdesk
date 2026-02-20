<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Models;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class InboundEmailController extends Controller
{
    /**
     * Handle inbound email webhook.
     *
     * POST /helpdesk/inbound-email
     */
    public function handle(Request $request): Response
    {
        if (!config('helpdesk.inbound_email.enabled', false)) {
            return $this->buildResponse([
                'error' => ['message' => 'Inbound email is disabled', 'status' => 403],
            ], $request, Response::HTTP_FORBIDDEN);
        }

        $provider = config('helpdesk.inbound_email.provider');

        // Validate webhook signature if secret is configured.
        if ($secret = config('helpdesk.inbound_email.webhook_secret')) {
            if (!$this->validateWebhookSignature($request, $provider, $secret)) {
                return $this->buildResponse([
                    'error' => ['message' => 'Invalid webhook signature', 'status' => 403],
                ], $request, Response::HTTP_FORBIDDEN);
            }
        }

        // Parse based on provider.
        $parsed = match ($provider) {
            'sendgrid' => $this->parseSendGrid($request),
            'mailgun' => $this->parseMailgun($request),
            'postmark' => $this->parsePostmark($request),
            default => null,
        };

        if (!$parsed) {
            return $this->buildResponse([
                'error' => ['message' => 'Failed to parse email', 'status' => 422],
            ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->createTicketOrComment($parsed);

        return $this->buildResponse([
            'message' => 'Email processed successfully',
        ], $request, Response::HTTP_OK);
    }

    protected function validateWebhookSignature(Request $request, string $provider, string $secret): bool
    {
        return match ($provider) {
            'sendgrid' => true, // SendGrid doesn't use HMAC signatures by default
            'mailgun' => $this->validateMailgunSignature($request, $secret),
            'postmark' => true, // Postmark uses basic auth or tokens
            default => false,
        };
    }

    protected function validateMailgunSignature(Request $request, string $secret): bool
    {
        $signature = $request->input('signature');
        $timestamp = $request->input('timestamp');
        $token = $request->input('token');

        if (!$signature || !$timestamp || !$token) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . $token, $secret);

        return hash_equals($expected, $signature);
    }

    protected function parseSendGrid(Request $request): ?array
    {
        $from = $request->input('from');
        $subject = $request->input('subject');
        $text = $request->input('text');

        if (!$from || !$subject) {
            return null;
        }

        return [
            'from' => $from,
            'subject' => $subject,
            'body' => $text ?? '',
        ];
    }

    protected function parseMailgun(Request $request): ?array
    {
        $from = $request->input('sender');
        $subject = $request->input('subject');
        $text = $request->input('body-plain');

        if (!$from || !$subject) {
            return null;
        }

        return [
            'from' => $from,
            'subject' => $subject,
            'body' => $text ?? '',
        ];
    }

    protected function parsePostmark(Request $request): ?array
    {
        $from = $request->input('From');
        $subject = $request->input('Subject');
        $text = $request->input('TextBody');

        if (!$from || !$subject) {
            return null;
        }

        return [
            'from' => $from,
            'subject' => $subject,
            'body' => $text ?? '',
        ];
    }

    protected function createTicketOrComment(array $parsed): void
    {
        $userClass = Models::user();
        $ticketClass = Models::ticket();
        $commentClass = Models::comment();

        // Extract email from "Name <email@domain.com>" format.
        preg_match('/<([^>]+)>/', $parsed['from'], $emailMatch);
        $fromEmail = $emailMatch[1] ?? $parsed['from'];

        // Find or create user.
        $user = $userClass::where('email', $fromEmail)->first();

        if (!$user) {
            // Extract name from email.
            $name = explode('@', $fromEmail)[0];
            $name = ucwords(str_replace(['.', '_', '-'], ' ', $name));

            $user = $userClass::create([
                'name' => $name,
                'email' => $fromEmail,
                'password' => Hash::make(Str::random(32)),
            ]);
        }

        // Check if subject matches [Ticket #123] or Re: [Ticket #123] pattern.
        if (preg_match('/\[Ticket #(\d+)\]/', $parsed['subject'], $matches)) {
            $ticketId = (int) $matches[1];
            $ticket = $ticketClass::find($ticketId);

            if ($ticket && $ticket->submitter_id === $user->id) {
                // Add as comment.
                $commentClass::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'content' => $parsed['body'],
                    'is_private' => false,
                ]);

                return;
            }
        }

        // Create new ticket.
        $ticketClass::create([
            'title' => $parsed['subject'],
            'description' => $parsed['body'],
            'submitter_id' => $user->id,
            'submitter_name' => $user->name,
            'submitter_email' => $user->email,
            'department_id' => config('helpdesk.inbound_email.default_department_id'),
            'priority_id' => config('helpdesk.inbound_email.default_priority_id'),
            'source' => 'email',
        ]);
    }
}
