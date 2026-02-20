<?php

use Crumbls\HelpDesk\Filament\Resources\CannedResponseResource;
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
        'satisfaction_rating' => \Crumbls\HelpDesk\Models\SatisfactionRating::class,
        'canned_response' => \Crumbls\HelpDesk\Models\CannedResponse::class,
        'attachment' => \Crumbls\HelpDesk\Models\Attachment::class,
        'activity_log' => \Crumbls\HelpDesk\Models\ActivityLog::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ticket Reference Numbers
    |--------------------------------------------------------------------------
    |
    | Human-readable ticket identifiers (e.g., HD-00042).
    |
    */
    'reference' => [
        'prefix' => 'HD',
        'pad' => 5, // HD-00001 through HD-99999
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    |
    | Automatically log ticket events (created, status changes, comments,
    | assignments, merges, ratings) into a timeline visible to customers
    | and agents.
    |
    */
    'activity_log' => [
        'enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Responder
    |--------------------------------------------------------------------------
    |
    | Customize the estimated response time shown in ticket confirmation
    | emails. Falls back to SLA defaults if SLA is enabled.
    |
    */
    'auto_responder' => [
        'response_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | Honeypot Bot Protection
    |--------------------------------------------------------------------------
    |
    | Protects guest ticket forms from automated spam submissions.
    | Frontend must include the hidden honeypot field and timestamp.
    |
    */
    'honeypot' => [
        'enabled' => true,
        'field' => 'website',           // Hidden field name — bots fill this
        'timestamp_field' => '_hp_timestamp', // Time-based trap field
        'min_seconds' => 3,             // Minimum seconds to fill form
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

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Control which email notifications are sent and to whom.
    | Admin recipients are resolved by user IDs or emails.
    |
    */
    'notifications' => [
        'enabled' => true,

        // Admin recipients — at least one of these should be set.
        'admin_user_ids' => [],    // e.g. [1, 2]
        'admin_emails' => [],      // e.g. ['admin@example.com']

        // Toggle individual notifications.
        'admin_new_ticket' => true,
        'admin_new_comment' => true,
        'submitter_confirmation' => true,
        'submitter_comment_reply' => true,
        'submitter_status_changed' => true,
        'agent_ticket_assigned' => true,
        'admin_satisfaction_rating' => true,
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

            'canned_response' => [
                'class' => CannedResponseResource::class,
                'sort' => 150,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Guest Ticket Submission
    |--------------------------------------------------------------------------
    |
    | Allow unauthenticated users to submit tickets via a public endpoint.
    |
    | When a guest submits a ticket:
    |  - If no user exists with that email, one is created and logged in.
    |  - If a user exists but has no tickets yet, they are logged in.
    |  - If a user exists with existing tickets, a signed login link is
    |    emailed instead (prevents impersonation / ticket snooping).
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | POST webhooks to external URLs when helpdesk events occur.
    | Each webhook can filter by event type. Payloads are signed with HMAC
    | if a secret is provided (X-Helpdesk-Signature header).
    |
    | Example:
    |   [
    |     ['url' => 'https://example.com/hooks/helpdesk', 'secret' => 'abc123', 'events' => ['*']],
    |     ['url' => 'https://crm.example.com/leads', 'events' => ['ticket_created']],
    |   ]
    |
    | Available events: ticket_created, ticket_status_changed, ticket_assigned, comment_created
    |
    */
    'webhooks' => [],

    'guest' => [
        'enabled' => false,
        'route-prefix' => 'helpdesk',
        'middleware' => ['web', 'throttle:6,1'],
        'link_expiry_minutes' => 60,
        'login_redirect' => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Close Stale Tickets
    |--------------------------------------------------------------------------
    |
    | Automatically close tickets after X days of inactivity.
    |
    */
    'auto_close' => [
        'enabled' => false,
        'days' => 14,
        'closed_status_id' => null, // must be set to a valid status ID
    ],

    /*
    |--------------------------------------------------------------------------
    | File Attachments
    |--------------------------------------------------------------------------
    |
    | Allow file uploads on tickets and comments.
    |
    */
    'attachments' => [
        'enabled' => true,
        'disk' => 'local',
        'path' => 'helpdesk-attachments',
        'max_size_kb' => 10240,
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'txt', 'zip'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Inbound Email
    |--------------------------------------------------------------------------
    |
    | Parse incoming emails into tickets or comments.
    |
    */
    'inbound_email' => [
        'enabled' => false,
        'provider' => null, // 'maildir', 'sendgrid', 'mailgun', 'postmark'
        'maildir' => storage_path('app/helpdesk-inbound'),
        'webhook_secret' => null,
        'default_department_id' => null,
        'default_priority_id' => null,
        'middleware' => ['throttle:30,1'],
    ],
];
