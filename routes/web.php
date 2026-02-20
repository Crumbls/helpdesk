<?php

if (!config('helpdesk.api.enabled', false)) {
	return;
}


Route::group([
	'prefix' => config('helpdesk.api.route-prefix', 'api'),
	'middleware' => (array)config('helpdesk.api.middleware', ['auth:sanctum'])
], function() {
	Route::get('/', \Crumbls\HelpDesk\Http\Controllers\ExposeEndpointsController::class);
	// TODO: CustomField API not yet implemented
	// Route::apiResource('custom-fields', \Crumbls\HelpDesk\Http\Controllers\CustomFieldController::class);
	Route::apiResource('departments', \Crumbls\HelpDesk\Http\Controllers\DepartmentController::class);
	Route::apiResource('priorities', \Crumbls\HelpDesk\Http\Controllers\PriorityController::class);
	Route::apiResource('statuses', \Crumbls\HelpDesk\Http\Controllers\StatusController::class);
	Route::apiResource('tickets', \Crumbls\HelpDesk\Http\Controllers\TicketController::class);
	Route::post('tickets/{id}/merge', [\Crumbls\HelpDesk\Http\Controllers\TicketController::class, 'merge']);
	Route::get('tickets/{id}/activity', [\Crumbls\HelpDesk\Http\Controllers\TicketController::class, 'activity']);
	Route::apiResource('types', \Crumbls\HelpDesk\Http\Controllers\TypeController::class);
	Route::apiResource('comments', \Crumbls\HelpDesk\Http\Controllers\CommentController::class);
	Route::get('canned-responses', [\Crumbls\HelpDesk\Http\Controllers\CannedResponseController::class, 'index']);
	Route::get('canned-responses/{id}', [\Crumbls\HelpDesk\Http\Controllers\CannedResponseController::class, 'show']);
	Route::apiResource('attachments', \Crumbls\HelpDesk\Http\Controllers\AttachmentController::class)->only(['store', 'show', 'destroy']);
});

/**
 * Guest ticket submission — no auth required.
 * Configurable via helpdesk.guest.enabled (defaults to false).
 */
if (config('helpdesk.guest.enabled', false)) {
	$guestController = \Crumbls\HelpDesk\Http\Controllers\GuestTicketController::class;

	Route::group([
		'prefix' => config('helpdesk.guest.route-prefix', 'helpdesk'),
		'middleware' => (array) config('helpdesk.guest.middleware', ['web', 'throttle:6,1']),
	], function () use ($guestController) {
		// Public (no signature needed) — submit ticket + lookup.
		// Honeypot middleware protects against bot spam.
		$honeypotMiddleware = config('helpdesk.honeypot.enabled', true)
			? [\Crumbls\HelpDesk\Http\Middleware\HoneypotProtection::class]
			: [];

		Route::post('tickets', [$guestController, 'store'])
			->name('helpdesk.guest.store')
			->middleware($honeypotMiddleware);

		Route::post('lookup', [$guestController, 'lookup'])
			->name('helpdesk.guest.lookup');

		// Signed URLs — ticket access without logging in.
		Route::get('tickets/{user}', [$guestController, 'listTickets'])
			->name('helpdesk.guest.tickets')
			->middleware('signed');

		Route::get('tickets/{ticket}/view/{user}', [$guestController, 'showTicket'])
			->name('helpdesk.guest.ticket.show')
			->middleware('signed');

		Route::post('tickets/{ticket}/comment/{user}', [$guestController, 'addComment'])
			->name('helpdesk.guest.ticket.comment')
			->middleware('signed');

		Route::get('tickets/{ticket}/activity/{user}', [$guestController, 'ticketActivity'])
			->name('helpdesk.guest.ticket.activity')
			->middleware('signed');

		Route::post('tickets/{ticket}/rate/{user}', [$guestController, 'rateTicket'])
			->name('helpdesk.guest.ticket.rate')
			->middleware('signed');

		Route::post('tickets/{ticket}/attach/{user}', [$guestController, 'attachFile'])
			->name('helpdesk.guest.ticket.attach')
			->middleware('signed');

		// Signed login link.
		Route::get('login/{user}', [$guestController, 'login'])
			->name('helpdesk.guest.login')
			->middleware('signed');
	});
}

/**
 * Inbound email webhook.
 */
if (config('helpdesk.inbound_email.enabled', false)) {
	Route::post('helpdesk/inbound-email', [\Crumbls\HelpDesk\Http\Controllers\InboundEmailController::class, 'handle'])
		->name('helpdesk.inbound-email')
		->middleware(config('helpdesk.inbound_email.middleware', ['throttle:30,1']));
}
