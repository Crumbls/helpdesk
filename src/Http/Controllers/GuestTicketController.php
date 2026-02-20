<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Controllers;

use Crumbls\HelpDesk\Events\SatisfactionRated;
use Crumbls\HelpDesk\Models;
use Crumbls\HelpDesk\Notifications\GuestLoginLink;
use Crumbls\HelpDesk\Notifications\GuestTicketLookup;
use Crumbls\HelpDesk\Notifications\SatisfactionRatingReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public-facing guest ticket endpoints (no auth required).
 *
 * Ticket creation flow:
 *  - Guest submits name + email + ticket details + optional lead fields.
 *  - If no user exists with that email, create one and log them in.
 *  - If user exists but has ZERO tickets, log them in (safe).
 *  - If user exists and HAS tickets, don't log in — send signed login link.
 *
 * Ticket lookup flow:
 *  - Guest submits email, receives a signed link to view their tickets.
 *
 * Signed endpoints:
 *  - View ticket + public comments.
 *  - Add a comment to a ticket.
 *  - List all tickets for a user.
 */
class GuestTicketController extends Controller
{
    /**
     * Create a ticket as a guest.
     *
     * POST /helpdesk/tickets
     */
    public function store(Request $request): Response
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:helpdesk_departments,id'],
            'priority_id' => ['nullable', 'integer', 'exists:helpdesk_priorities,id'],
            'ticket_type_id' => ['nullable', 'integer', 'exists:helpdesk_ticket_types,id'],
            // Lead gen / tracking metadata.
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:2048'],
            'page_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $userClass = Models::user();
        $ticketClass = Models::ticket();

        // Find or create user.
        $user = $userClass::where('email', $validated['email'])->first();
        $isNewUser = false;

        if (!$user) {
            $user = $userClass::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(32)),
            ]);
            $isNewUser = true;
        }

        $existingTicketCount = $ticketClass::where('submitter_id', $user->id)->count();

        // Build metadata from UTM / tracking params.
        $metadata = array_filter([
            'utm_source' => $validated['utm_source'] ?? null,
            'utm_medium' => $validated['utm_medium'] ?? null,
            'utm_campaign' => $validated['utm_campaign'] ?? null,
            'referrer' => $validated['referrer'] ?? null,
            'page_url' => $validated['page_url'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Create ticket.
        $ticket = $ticketClass::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'submitter_id' => $user->id,
            'submitter_name' => $validated['name'],
            'submitter_email' => $validated['email'],
            'submitter_phone' => $validated['phone'] ?? null,
            'submitter_company' => $validated['company'] ?? null,
            'department_id' => $validated['department_id'] ?? null,
            'priority_id' => $validated['priority_id'] ?? null,
            'ticket_type_id' => $validated['ticket_type_id'] ?? null,
            'source' => 'guest',
            'metadata' => $metadata ?: null,
        ]);

        // Auth decision.
        $loggedIn = false;

        if ($isNewUser || $existingTicketCount === 0) {
            Auth::login($user);
            $loggedIn = true;
        } else {
            $loginUrl = URL::temporarySignedRoute(
                'helpdesk.guest.login',
                now()->addMinutes((int) config('helpdesk.guest.link_expiry_minutes', 60)),
                ['user' => $user->id]
            );
            $user->notify(new GuestLoginLink($loginUrl, $ticket));
        }

        // Build a signed ticket view URL for the response.
        $ticketUrl = URL::temporarySignedRoute(
            'helpdesk.guest.ticket.show',
            now()->addDays(30),
            ['ticket' => $ticket->id, 'user' => $user->id]
        );

        return $this->buildResponse([
            'ticket' => $ticket->toArray(),
            'ticket_url' => $ticketUrl,
            'logged_in' => $loggedIn,
            'message' => $loggedIn
                ? 'Ticket created. You are now logged in.'
                : 'Ticket created. A login link has been sent to your email.',
        ], $request, Response::HTTP_CREATED);
    }

    /**
     * Request a ticket lookup link via email.
     *
     * POST /helpdesk/lookup
     */
    public function lookup(Request $request): Response
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $userClass = Models::user();
        $ticketClass = Models::ticket();

        $user = $userClass::where('email', $validated['email'])->first();

        // Always return success to avoid email enumeration.
        if ($user) {
            $ticketCount = $ticketClass::where('submitter_id', $user->id)->count();

            if ($ticketCount > 0) {
                $listUrl = URL::temporarySignedRoute(
                    'helpdesk.guest.tickets',
                    now()->addMinutes((int) config('helpdesk.guest.link_expiry_minutes', 60)),
                    ['user' => $user->id]
                );

                $user->notify(new GuestTicketLookup($listUrl, $ticketCount));
            }
        }

        return $this->buildResponse([
            'message' => 'If tickets exist for that email, a link has been sent.',
        ], $request, Response::HTTP_OK);
    }

    /**
     * List all tickets for a user (signed URL).
     *
     * GET /helpdesk/tickets/{user}
     */
    public function listTickets(Request $request, int $user): Response
    {
        $ticketClass = Models::ticket();

        $tickets = $ticketClass::where('submitter_id', $user)
            ->with(['status', 'priority', 'department'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->buildResponse($tickets->toArray(), $request, Response::HTTP_OK);
    }

    /**
     * View a single ticket with public comments (signed URL).
     *
     * GET /helpdesk/tickets/{ticket}/view/{user}
     */
    public function showTicket(Request $request, int $ticket, int $user): Response
    {
        $ticketClass = Models::ticket();

        $record = $ticketClass::where('id', $ticket)
            ->where('submitter_id', $user)
            ->with(['status', 'priority', 'department', 'publicComments.user'])
            ->first();

        if (!$record) {
            return $this->buildResponse([
                'error' => ['message' => 'Ticket not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($record->toArray(), $request, Response::HTTP_OK);
    }

    /**
     * Add a comment to a ticket as a guest (signed URL).
     *
     * POST /helpdesk/tickets/{ticket}/comment/{user}
     */
    public function addComment(Request $request, int $ticket, int $user): Response
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $ticketClass = Models::ticket();
        $commentClass = Models::comment();

        $record = $ticketClass::where('id', $ticket)
            ->where('submitter_id', $user)
            ->first();

        if (!$record) {
            return $this->buildResponse([
                'error' => ['message' => 'Ticket not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $comment = $commentClass::create([
            'ticket_id' => $record->id,
            'user_id' => $user,
            'content' => $validated['content'],
            'is_private' => false,
        ]);

        return $this->buildResponse(
            $comment->load('user')->toArray(),
            $request,
            Response::HTTP_CREATED
        );
    }

    /**
     * Get ticket activity timeline (signed URL).
     *
     * GET /helpdesk/tickets/{ticket}/activity/{user}
     */
    public function ticketActivity(Request $request, int $ticket, int $user): Response
    {
        $ticketClass = Models::ticket();

        $record = $ticketClass::where('id', $ticket)
            ->where('submitter_id', $user)
            ->first();

        if (!$record) {
            return $this->buildResponse([
                'error' => ['message' => 'Ticket not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        // Only show public activity (exclude internal_note type).
        $activity = $record->activityLog()
            ->where('type', '!=', 'internal_note')
            ->with('user:id,name')
            ->get();

        return $this->buildResponse($activity->toArray(), $request, Response::HTTP_OK);
    }

    /**
     * Rate a ticket after it's closed (signed URL).
     *
     * POST /helpdesk/tickets/{ticket}/rate/{user}
     */
    public function rateTicket(Request $request, int $ticket, int $user): Response
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $ticketClass = Models::ticket();
        $ratingClass = Models::satisfactionRating();

        $record = $ticketClass::where('id', $ticket)
            ->where('submitter_id', $user)
            ->with('status')
            ->first();

        if (!$record) {
            return $this->buildResponse([
                'error' => ['message' => 'Ticket not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        // Check if ticket is closed.
        $isClosed = $record->closed_at !== null || ($record->status && $record->status->is_closed);

        if (!$isClosed) {
            return $this->buildResponse([
                'error' => ['message' => 'Ticket must be closed before rating', 'status' => 422],
            ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check if already rated.
        $existing = $ratingClass::where('ticket_id', $ticket)->first();

        if ($existing) {
            return $this->buildResponse([
                'error' => ['message' => 'This ticket has already been rated', 'status' => 422],
            ], $request, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rating = $ratingClass::create([
            'ticket_id' => $ticket,
            'user_id' => $user,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        // Fire event.
        if (config('helpdesk.events.enabled')) {
            event(new SatisfactionRated($record, $rating));
        }

        // Notify admins if enabled.
        if (config('helpdesk.notifications.enabled') && config('helpdesk.notifications.admin_satisfaction_rating')) {
            $this->notifyAdmins($rating);
        }

        return $this->buildResponse(
            $rating->toArray(),
            $request,
            Response::HTTP_CREATED
        );
    }

    /**
     * Attach a file to a ticket (signed URL).
     *
     * POST /helpdesk/tickets/{ticket}/attach/{user}
     */
    public function attachFile(Request $request, int $ticket, int $user): Response
    {
        if (!config('helpdesk.attachments.enabled')) {
            return $this->buildResponse([
                'error' => ['message' => 'Attachments are disabled', 'status' => 403],
            ], $request, Response::HTTP_FORBIDDEN);
        }

        $maxSizeKb = config('helpdesk.attachments.max_size_kb', 10240);
        $allowedMimes = config('helpdesk.attachments.allowed_mimes', []);

        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'max:' . $maxSizeKb,
                'mimes:' . implode(',', $allowedMimes),
            ],
        ]);

        $ticketClass = Models::ticket();
        $attachmentClass = Models::attachment();

        $record = $ticketClass::where('id', $ticket)
            ->where('submitter_id', $user)
            ->first();

        if (!$record) {
            return $this->buildResponse([
                'error' => ['message' => 'Ticket not found', 'status' => 404],
            ], $request, Response::HTTP_NOT_FOUND);
        }

        $file = $request->file('file');
        $disk = config('helpdesk.attachments.disk', 'local');
        $path = config('helpdesk.attachments.path', 'helpdesk-attachments');

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs($path, $filename, $disk);

        $attachment = $attachmentClass::create([
            'attachable_type' => $ticketClass,
            'attachable_id' => $ticket,
            'user_id' => $user,
            'filename' => $filePath,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $disk,
        ]);

        return $this->buildResponse(
            $attachment->toArray(),
            $request,
            Response::HTTP_CREATED
        );
    }

    /**
     * Handle signed login link.
     *
     * GET /helpdesk/login/{user}
     */
    public function login(Request $request, int $user): Response
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired login link.');
        }

        $userClass = Models::user();
        $userModel = $userClass::findOrFail($user);

        Auth::login($userModel);

        return redirect(config('helpdesk.guest.login_redirect', '/'));
    }

    /**
     * Notify admin users.
     */
    protected function notifyAdmins($rating): void
    {
        $userClass = Models::user();

        $adminIds = config('helpdesk.notifications.admin_user_ids', []);
        $adminEmails = config('helpdesk.notifications.admin_emails', []);

        $admins = collect();

        if (!empty($adminIds)) {
            $admins = $admins->merge($userClass::whereIn('id', $adminIds)->get());
        }

        if (!empty($adminEmails)) {
            $admins = $admins->merge($userClass::whereIn('email', $adminEmails)->get());
        }

        $admins = $admins->unique('id');

        foreach ($admins as $admin) {
            $admin->notify(new SatisfactionRatingReceived($rating));
        }
    }
}
