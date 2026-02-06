<?php

use Crumbls\HelpDesk\Filament\Resources\DepartmentResource;
use Crumbls\HelpDesk\Filament\Resources\PriorityResource;
use Crumbls\HelpDesk\Filament\Resources\TicketResource;
use Crumbls\HelpDesk\Filament\Resources\TicketStatusResource;
use Crumbls\HelpDesk\Filament\Resources\TicketTypeResource;

return [
    'api' => [
        'enabled' => true,
        'route-prefix' => 'api/helpdesk',
        'middleware' => [
            'web'
        ],

        'department' => [
            'policy' => null,
        ],

        'priority' => [
            'policy' => null,
        ],

        'status' => [
            'policy' => null,
        ],

        'ticket' => [
            'policy' => null,
        ],

        'type' => [
            'policy' => null,
        ],

        'comment' => [
            'policy' => null,
        ],
    ],

    'models' => [
        'comment' => \Crumbls\HelpDesk\Models\TicketComment::class,
        'custom_field' => \Crumbls\HelpDesk\Models\CustomField::class,
        'department' => \Crumbls\HelpDesk\Models\Department::class,
        'priority' => \Crumbls\HelpDesk\Models\Priority::class,
        'status' => \Crumbls\HelpDesk\Models\TicketStatus::class,
        'ticket' => \Crumbls\HelpDesk\Models\Ticket::class,
        'ticket_assignment' => \Crumbls\HelpDesk\Models\TicketAssignment::class,
        'type' => \Crumbls\HelpDesk\Models\TicketType::class,
        'user' => null, // null = auto-detect from auth config, or specify a custom User model
    ],

    'sla' => [
        'enabled' => false,
        'calculator' => \Crumbls\HelpDesk\Services\SlaService::class,
        'defaults' => [
            'response_hours' => 24,
            'resolution_hours' => 72,
        ],
        'business_hours' => [
            'enabled' => false,
            'timezone' => 'UTC',
            'start' => '09:00',
            'end' => '17:00',
            'days' => [1, 2, 3, 4, 5],
        ],
    ],

    'events' => [
        'enabled' => true,
        'dispatch' => [
            'ticket_created' => true,
            'ticket_updated' => true,
            'ticket_deleted' => true,
            'ticket_status_changed' => true,
            'ticket_assigned' => true,
            'comment_created' => true,
            'comment_updated' => true,
            'comment_deleted' => true,
            'sla_breached' => true,
        ],
    ],

    'filament' => [
        'navigation_group' => 'Helpdesk',
        'settings_navigation_group' => 'Helpdesk Settings',
        'resources' => [
            'ticket' => [
                'class' => TicketResource::class,
                'sort' => 100,
            ],

            'department' => [
                'class' => DepartmentResource::class,
                'sort' => 110,
            ],

            'priority' => [
                'class' => PriorityResource::class,
                'sort' => 120,
            ],

            'status' => [
                'class' => TicketStatusResource::class,
                'sort' => 130,
            ],

            'type' => [
                'class' => TicketTypeResource::class,
                'sort' => 140,
            ],
        ],
    ],
];
