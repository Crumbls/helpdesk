<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honeypot bot protection for guest ticket endpoints.
 *
 * Two-pronged approach:
 * 1. Hidden field trap — bots fill it, humans don't.
 * 2. Time trap — form must take at least N seconds to submit.
 *
 * Frontend must include:
 *   <input type="text" name="website" style="display:none" tabindex="-1" autocomplete="off">
 *   <input type="hidden" name="_hp_timestamp" value="{{ time() }}">
 */
class HoneypotProtection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('helpdesk.honeypot.enabled', true)) {
            return $next($request);
        }

        $honeypotField = config('helpdesk.honeypot.field', 'website');
        $timestampField = config('helpdesk.honeypot.timestamp_field', '_hp_timestamp');
        $minSeconds = config('helpdesk.honeypot.min_seconds', 3);

        // Trap 1: If the honeypot field has a value, it's a bot.
        if ($request->filled($honeypotField)) {
            return $this->rejectBot($request);
        }

        // Trap 2: If the form was submitted too fast, it's a bot.
        $timestamp = (int) $request->input($timestampField, 0);
        if ($timestamp > 0 && (time() - $timestamp) < $minSeconds) {
            return $this->rejectBot($request);
        }

        // Remove honeypot fields so they don't leak into validation/controllers.
        $request->request->remove($honeypotField);
        $request->request->remove($timestampField);

        return $next($request);
    }

    /**
     * Silently reject — return a fake success to not tip off the bot.
     */
    protected function rejectBot(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Ticket created.',
                'ticket' => ['id' => 0],
            ], 201);
        }

        return redirect()->back()->with('success', 'Ticket submitted successfully.');
    }
}
