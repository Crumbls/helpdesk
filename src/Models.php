<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk;

use App\Models\User;
use Crumbls\HelpDesk\Models\CustomField;
use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketAssignment;
use Crumbls\HelpDesk\Models\TicketComment;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;

class Models {
	/**
	 * @return string
	 */
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
	/**
	 * @return string
	 */
	public static function priority(): string
	{
		return config('helpdesk.models.priority', Priority::class);
	}

	/**
	 * @return string
	 */
	public static function status(): string
	{
		return config('helpdesk.models.status', TicketStatus::class);
	}

	/**
	 * @return string
	 */
	public static function ticket(): string
	{
		return config('helpdesk.models.ticket', Ticket::class);
	}


	/**
	 * @return string
	 */
	public static function ticketAssignment(): string
	{
		return config('helpdesk.models.ticket_assignment', TicketAssignment::class);
	}

	/**
	 * @return string
	 */
	public static function type(): string
	{
		return config('helpdesk.models.type', TicketType::class);
	}


	/**
	 * @return string
	 */
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

}