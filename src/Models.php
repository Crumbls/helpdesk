<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk;

use App\Models\User;
use Crumbls\HelpDesk\Models\ActivityLog;
use Crumbls\HelpDesk\Models\Attachment;
use Crumbls\HelpDesk\Models\CannedResponse;
use Crumbls\HelpDesk\Models\CustomField;
use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\SatisfactionRating;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketAssignment;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Models\TicketRead;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;

class Models
{
    public static function customField(): string
    {
        return config('helpdesk.models.custom_field', CustomField::class);
    }

    public static function comment(): string
    {
        return config('helpdesk.models.comment', TicketComment::class);
    }

    public static function department(): string
    {
        return config('helpdesk.models.department', Department::class);
    }

    public static function priority(): string
    {
        return config('helpdesk.models.priority', Priority::class);
    }

    public static function status(): string
    {
        return config('helpdesk.models.status', TicketStatus::class);
    }

    public static function ticket(): string
    {
        return config('helpdesk.models.ticket', Ticket::class);
    }

    public static function ticketAssignment(): string
    {
        return config('helpdesk.models.ticket_assignment', TicketAssignment::class);
    }

    public static function ticketRead(): string
    {
        return config('helpdesk.models.ticket_read', TicketRead::class);
    }

    public static function type(): string
    {
        return config('helpdesk.models.type', TicketType::class);
    }

    public static function user(): string
    {
        return (string) once(function () {
            $configured = config('helpdesk.models.user');

            if ($configured !== null) {
                return $configured;
            }

            $guard = config('auth.defaults.guard');
            $provider = config("auth.guards.$guard.provider");

            return config("auth.providers.$provider.model", User::class);
        });
    }

    public static function satisfactionRating(): string
    {
        return config('helpdesk.models.satisfaction_rating', SatisfactionRating::class);
    }

    public static function cannedResponse(): string
    {
        return config('helpdesk.models.canned_response', CannedResponse::class);
    }

    public static function attachment(): string
    {
        return config('helpdesk.models.attachment', Attachment::class);
    }

    public static function activityLog(): string
    {
        return config('helpdesk.models.activity_log', ActivityLog::class);
    }
}
