<?php

if (!config('helpdesk.api.enabled', false)) {
	return;
}


Route::group([
	'prefix' => config('helpdesk.api.route-prefix', 'api'),
	'middleware' => (array)config('helpdesk.api.middleware', ['auth:sanctum'])
], function() {
	Route::get('/', \Crumbls\HelpDesk\Http\Controllers\ExposeEndpointsController::class);
	Route::apiResource('custom-fields', \Crumbls\HelpDesk\Http\Controllers\CustomFieldController::class);
	Route::apiResource('departments', \Crumbls\HelpDesk\Http\Controllers\DepartmentController::class);
	Route::apiResource('priorities', \Crumbls\HelpDesk\Http\Controllers\PriorityController::class);
	Route::apiResource('statuses', \Crumbls\HelpDesk\Http\Controllers\StatusController::class);
	Route::apiResource('tickets', \Crumbls\HelpDesk\Http\Controllers\TicketController::class);
	Route::apiResource('types', \Crumbls\HelpDesk\Http\Controllers\TypeController::class);
//	Route::apiResource('topic', \Crumbls\HelpDesk\Http\Controllers\TopicController::class);
});
