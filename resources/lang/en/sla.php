<?php

return [
    'label' => 'SLA',
    'plural' => 'SLAs',

    'fields' => [
        'response_due_at' => 'Response Due',
        'resolution_due_at' => 'Resolution Due',
        'first_response_at' => 'First Response',
        'response_breached' => 'Response Breached',
        'resolution_breached' => 'Resolution Breached',
        'response_hours' => 'Response Hours',
        'resolution_hours' => 'Resolution Hours',
    ],

    'messages' => [
        'response_breached' => 'SLA response time has been breached.',
        'resolution_breached' => 'SLA resolution time has been breached.',
        'response_warning' => 'SLA response deadline approaching.',
        'resolution_warning' => 'SLA resolution deadline approaching.',
        'met' => 'SLA requirements met.',
        'not_applicable' => 'SLA not applicable for this ticket.',
    ],

    'status' => [
        'on_track' => 'On Track',
        'at_risk' => 'At Risk',
        'breached' => 'Breached',
        'paused' => 'Paused',
        'met' => 'Met',
    ],

    'types' => [
        'response' => 'First Response',
        'resolution' => 'Resolution',
    ],

    'business_hours' => [
        'enabled' => 'Business Hours Enabled',
        'disabled' => 'Business Hours Disabled',
        'label' => 'Business Hours',
    ],
];
