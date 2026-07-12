# Crumbls HelpDesk

A Laravel helpdesk and ticketing package with an API-first ticket workflow, guest intake, activity timelines, attachments, custom fields, SLA helpers, notifications, and optional Filament resources.

## Status

This package is pre-1.0 and actively hardening for production use. The core API, events, notifications, activity log, attachments, guest ticket flow, custom fields, read state, queues, SLA calculations, and webhooks are covered by tests.

Breaking schema and config changes can still happen before 1.0. Review [CHANGELOG.md](CHANGELOG.md) before updating.

## Features

- REST API for departments, priorities, statuses, ticket types, tickets, comments, attachments, and custom fields.
- Secure API defaults with `web` and `auth` middleware.
- JSON and XML responses based on the `Accept` header.
- Config-driven models, including configurable user model and user-key-aware migrations.
- Department-agent membership via `helpdesk_department_user`.
- Protected attachment metadata and downloads.
- Custom fields scoped globally, by department, or by ticket type.
- Ticket references generated from the persisted ticket ID.
- Activity timelines, read/unread state, `last_activity_at`, and operational queues.
- Guest ticket intake with signed ticket access links.
- Inbound email command/webhook support.
- SLA due-date calculation and stale-ticket auto-close command.
- Event, notification, webhook, and activity-log listeners with independent config toggles.
- Optional Filament resources.
- Pint and PHPStan quality gates, plus a dirty-file Pint pre-commit hook.

## Requirements

- PHP 8.3+
- Laravel 9, 10, 11, 12, or 13

## Installation

```bash
composer require crumbls/helpdesk
php artisan migrate
```

Publish the config when you need to customize routes, middleware, models, notifications, guest access, webhooks, SLA behavior, or user key handling:

```bash
php artisan vendor:publish --provider="Crumbls\HelpDesk\HelpDeskServiceProvider"
```

## Configuration

API routes are enabled by default at `/api/helpdesk` and require authentication by default:

```php
'api' => [
    'enabled' => true,
    'route-prefix' => 'api/helpdesk',
    'middleware' => ['web', 'auth'],
],
```

Guest ticket routes and inbound email routes are configured independently:

```php
'guest' => [
    'enabled' => false,
    'route-prefix' => 'helpdesk',
    'middleware' => ['web', 'throttle:6,1'],
],

'inbound_email' => [
    'enabled' => false,
],
```

### Swappable Models

Override any package model in `config/helpdesk.php`:

```php
'models' => [
    'ticket' => \App\Models\SupportTicket::class,
    'user' => \App\Models\User::class,
],
```

### Custom User Keys

Migrations infer the configured user model key type by default. Override `helpdesk.user_keys` when your app uses custom string, char, UUID, or ULID user keys:

```php
'user_keys' => [
    'table' => 'users',
    'key' => 'id',
    'column' => [
        'type' => 'ulid',
    ],
],
```

## API Endpoints

All endpoints are prefixed with `api/helpdesk` unless you change `api.route-prefix`.

| Resource | Endpoint | Methods |
| --- | --- | --- |
| Departments | `/departments` | GET, POST, GET `/{id}`, PUT `/{id}`, DELETE `/{id}` |
| Priorities | `/priorities` | GET, POST, GET `/{id}`, PUT `/{id}`, DELETE `/{id}` |
| Statuses | `/statuses` | GET, POST, GET `/{id}`, PUT `/{id}`, DELETE `/{id}` |
| Ticket types | `/types` | GET, POST, GET `/{id}`, PUT `/{id}`, DELETE `/{id}` |
| Custom fields | `/custom-fields` | GET, POST, GET `/{id}`, PUT `/{id}`, DELETE `/{id}` |
| Tickets | `/tickets` | GET, POST, GET `/{id}`, PUT `/{id}`, DELETE `/{id}` |
| Comments | `/comments` | GET, POST, GET `/{id}`, PUT `/{id}`, DELETE `/{id}` |
| Attachments | `/attachments` | POST, GET `/{id}`, DELETE `/{id}` |

Ticket workflow endpoints:

| Endpoint | Purpose |
| --- | --- |
| `POST /tickets/{id}/merge` | Merge one ticket into another |
| `POST /tickets/{id}/assign` | Assign directly, least-loaded, or round-robin |
| `POST /tickets/{id}/unassign` | Remove an assignment |
| `GET /tickets/{id}/activity` | Fetch the activity timeline |
| `POST /tickets/{id}/read` | Mark a ticket read for a user |
| `POST /tickets/{id}/unread` | Mark a ticket unread for a user |
| `GET /attachments/{id}/download` | Download a protected attachment |

Ticket index filters:

```text
GET /api/helpdesk/tickets?queue=open
GET /api/helpdesk/tickets?queue=closed
GET /api/helpdesk/tickets?queue=unassigned
GET /api/helpdesk/tickets?queue=assigned_to_me&user_id=1
GET /api/helpdesk/tickets?assigned_to=1
GET /api/helpdesk/tickets?department_id=1
GET /api/helpdesk/tickets?priority_id=1
GET /api/helpdesk/tickets?ticket_status_id=1
GET /api/helpdesk/tickets?ticket_type_id=1
GET /api/helpdesk/tickets?unread_for=1
```

Assignment examples:

```json
{ "user_id": 5 }
```

```json
{ "strategy": "least_loaded" }
```

```json
{ "strategy": "round_robin", "department_id": 2 }
```

## Custom Fields

Create custom fields through `/custom-fields`, then submit values in `tickets.custom_fields`:

```json
{
  "key": "customer_plan",
  "label": "Customer Plan",
  "type": "select",
  "options": {
    "free": "Free",
    "pro": "Pro"
  },
  "is_required": true
}
```

Supported custom field types include `text`, `textarea`, `email`, `number`, `url`, `select`, `radio`, `checkbox`, `boolean`, `date`, and `datetime`.

## Development

Install the local pre-commit hook:

```bash
composer hooks:install
```

Run quality gates:

```bash
composer test:lint
composer test:lint:dirty
composer test:types
./vendor/bin/pest --no-coverage
```

`phpstan-baseline.neon` is deliberate. Keep reducing it as model generics, typed config helpers, and dynamic Eloquent calls are hardened.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits

- Created by [Chase C. Miller](https://crumbls.com)
- Built with [Laravel](https://laravel.com)
