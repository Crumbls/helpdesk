<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Console\Commands;

use Crumbls\HelpDesk\Models;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProcessInboundEmail extends Command
{
    protected $signature = 'helpdesk:process-email {--file= : Process a single .eml file}';

    protected $description = 'Process inbound emails from mailbox into tickets/comments';

    public function handle(): int
    {
        if (!config('helpdesk.inbound_email.enabled', false)) {
            $this->warn('Inbound email is disabled in config.');
            return Command::SUCCESS;
        }

        $provider = config('helpdesk.inbound_email.provider');

        if ($provider !== 'maildir') {
            $this->error('Only maildir provider is supported via console. Use webhook for other providers.');
            return Command::FAILURE;
        }

        $maildir = config('helpdesk.inbound_email.maildir');

        if (!File::exists($maildir)) {
            $this->error("Maildir not found: {$maildir}");
            return Command::FAILURE;
        }

        // Process a single file if specified.
        if ($file = $this->option('file')) {
            if (!File::exists($file)) {
                $this->error("File not found: {$file}");
                return Command::FAILURE;
            }

            $this->processEmailFile($file);
            return Command::SUCCESS;
        }

        // Process all .eml files in maildir.
        $files = File::glob("{$maildir}/*.eml");

        $this->info("Found " . count($files) . " email file(s) to process.");

        foreach ($files as $file) {
            $this->processEmailFile($file);
        }

        return Command::SUCCESS;
    }

    protected function processEmailFile(string $filePath): void
    {
        $this->info("Processing: {$filePath}");

        $content = File::get($filePath);

        // Parse email (basic parsing - in production you'd use a library like php-mime-mail-parser).
        $parsed = $this->parseEmail($content);

        if (!$parsed) {
            $this->warn("Failed to parse email: {$filePath}");
            return;
        }

        $this->createTicketOrComment($parsed);

        // Move to processed directory.
        $processedDir = dirname($filePath) . '/processed';
        File::ensureDirectoryExists($processedDir);
        File::move($filePath, $processedDir . '/' . basename($filePath));

        $this->info("Processed and moved to: {$processedDir}");
    }

    protected function parseEmail(string $content): ?array
    {
        // Very basic parsing - extract headers and body.
        // In production, use a proper email parsing library.
        $lines = explode("\n", $content);
        $headers = [];
        $body = '';
        $inBody = false;

        foreach ($lines as $line) {
            if (!$inBody && trim($line) === '') {
                $inBody = true;
                continue;
            }

            if ($inBody) {
                $body .= $line . "\n";
            } else {
                if (preg_match('/^([\w-]+):\s*(.*)$/', $line, $matches)) {
                    $headers[strtolower($matches[1])] = trim($matches[2]);
                }
            }
        }

        if (empty($headers['from']) || empty($headers['subject'])) {
            return null;
        }

        // Extract email from "Name <email@domain.com>" format.
        preg_match('/<([^>]+)>/', $headers['from'], $emailMatch);
        $fromEmail = $emailMatch[1] ?? $headers['from'];

        return [
            'from' => trim($fromEmail),
            'subject' => $headers['subject'] ?? '',
            'body' => trim($body),
        ];
    }

    protected function createTicketOrComment(array $parsed): void
    {
        $userClass = Models::user();
        $ticketClass = Models::ticket();
        $commentClass = Models::comment();

        // Find user by email.
        $user = $userClass::where('email', $parsed['from'])->first();

        if (!$user) {
            $this->warn("User not found for email: {$parsed['from']}. Skipping.");
            return;
        }

        // Check if subject matches [Ticket #123] pattern.
        if (preg_match('/\[Ticket #(\d+)\]/', $parsed['subject'], $matches)) {
            $ticketId = (int) $matches[1];
            $ticket = $ticketClass::find($ticketId);

            if ($ticket) {
                // Add as comment.
                $commentClass::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'content' => $parsed['body'],
                    'is_private' => false,
                ]);

                $this->info("Added comment to ticket #{$ticketId}");
                return;
            }
        }

        // Create new ticket.
        $ticket = $ticketClass::create([
            'title' => $parsed['subject'],
            'description' => $parsed['body'],
            'submitter_id' => $user->id,
            'submitter_name' => $user->name,
            'submitter_email' => $user->email,
            'department_id' => config('helpdesk.inbound_email.default_department_id'),
            'priority_id' => config('helpdesk.inbound_email.default_priority_id'),
            'source' => 'email',
        ]);

        $this->info("Created new ticket #{$ticket->id}");
    }
}
