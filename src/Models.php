<?php

declare(strict_types=1);

namespace Crumbls\HelpDesk;

use Crumbls\HelpDesk\Models\CustomField;
use Crumbls\HelpDesk\Models\Department;
use Crumbls\HelpDesk\Models\Priority;
use Crumbls\HelpDesk\Models\Ticket;
use Crumbls\HelpDesk\Models\TicketStatus;
use Crumbls\HelpDesk\Models\TicketType;

class Models {
	/**
	 * @return string
	 */
	public static function customField(): string
	{
		return config('helpdesk.models.custom-field', CustomField::class);
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
	public static function topic(): string
	{
		dd(__METHOD__);
		return config('helpdesk.models.topic', Topic::class);
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
		return (string)once(function() {
			$guard = config('auth.defaults.guard');
			$provider = config("auth.guards.$guard.provider");
			return config("auth.providers.$provider.model", \App\Models\User::class);
		});
	}

}