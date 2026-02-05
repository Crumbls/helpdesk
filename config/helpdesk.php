<?php

return [
	'api' => [
		'enabled' => true,
		'route-prefix' => 'api/helpdesk',
		'middleware' => [
			'web'
			// TODO: Switch to sanctum...
//			'auth:sanctum'
		],

		'department' => [
			'usePolicy' => false
		],

		'priority' => [
			'usePolicy' => false
		],

		'status' => [
			'usePolicy' => false
		],

		'ticket' => [
			'usePolicy' => false
		],

		'ticket_type' => [
			'usePolicy' => false
		]
	],
];
