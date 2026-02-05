# Crumbls HelpDesk

A helpdesk and ticket management package for Laravel. Provides a complete REST API for managing tickets, priorities, statuses, departments, and ticket types.

## Beta Status

This package is in active development. While core API functionality is working and tested, please note:

- APIs are subject to change
- Not recommended for production use without thorough testing
- Breaking changes may occur between releases
- Some features (custom fields, topics) are not yet implemented

## Features

- **REST API** with full CRUD for all resources
- **Content negotiation** -- responds with JSON or XML based on `Accept` header
- **Config-driven models** -- swap any model class via config
- **Soft deletes** on all resources
- **Policy support** -- optional, per-resource authorization via Laravel policies
- **Auto-calculated foreground colors** from background hex via the `HasColors` trait

## Requirements

- PHP 8.0+
- Laravel 9.0, 10.0, 11.0, or 12.0

## Installation

```bash
composer require crumbls/helpdesk
```

The service provider is auto-discovered. Run migrations:

```bash
php artisan migrate
```

To publish the config file:

```bash
php artisan vendor:publish --provider="Crumbls\HelpDesk\HelpDeskServiceProvider"
```

## Configuration

After publishing, configure your helpdesk in `config/helpdesk.php`:

```php
return [
    'api' => [
        'enabled' => true,
        'route-prefix' => 'api/helpdesk',
        'middleware' => ['web'],

        'department'  => ['usePolicy' => false],
        'priority'    => ['usePolicy' => false],
        'status'      => ['usePolicy' => false],
        'ticket'      => ['usePolicy' => false],
        'ticket_type' => ['usePolicy' => false],
    ],
];
```

### Options

| Key              | Description                                      | Default          |
|------------------|--------------------------------------------------|------------------|
| `api.enabled`    | Enable or disable the API entirely               | `true`           |
| `api.route-prefix` | URL prefix for all API routes                 | `api/helpdesk`   |
| `api.middleware`  | Middleware applied to all API routes             | `['web']`        |
| `api.{resource}.usePolicy` | Enable policy authorization per resource | `false`          |

### Swappable Models

Override any model class via `config/helpdesk.php`:

```php
'models' => [
    'department'   => \App\Models\CustomDepartment::class,
    'priority'     => \App\Models\CustomPriority::class,
    'status'       => \App\Models\CustomTicketStatus::class,
    'ticket'       => \App\Models\CustomTicket::class,
    'type'         => \App\Models\CustomTicketType::class,
],
```

## API Endpoints

All endpoints are prefixed with your configured `route-prefix` (default: `/api/helpdesk`).

| Resource     | Endpoint          | Methods                          |
|--------------|-------------------|----------------------------------|
| Departments  | `/departments`    | GET, POST, GET/:id, PUT/:id, DELETE/:id |
| Priorities   | `/priorities`     | GET, POST, GET/:id, PUT/:id, DELETE/:id |
| Statuses     | `/statuses`       | GET, POST, GET/:id, PUT/:id, DELETE/:id |
| Tickets      | `/tickets`        | GET, POST, GET/:id, PUT/:id, DELETE/:id |
| Types        | `/types`          | GET, POST, GET/:id, PUT/:id, DELETE/:id |

A `GET /` endpoint lists all available API routes.

### Quick Example

```bash
curl -X POST http://localhost/api/helpdesk/priorities \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"title": "Critical", "color_background": "#EF4444", "level": 5}'
```

### XML Responses

Set the `Accept` header to receive XML instead of JSON:

```bash
curl -X GET http://localhost/api/helpdesk/priorities/1 \
  -H "Accept: application/xml"
```

## API Documentation

Detailed API documentation with curl examples for each endpoint:

- [Departments](docs/api-departments.md)
- [Priorities](docs/api-priorities.md)
- [Statuses](docs/api-statuses.md)
- [Tickets](docs/api-tickets.md)
- [Types](docs/api-types.md)

## Testing

```bash
composer test:unit
```

Or run just the Pest tests directly:

```bash
./vendor/bin/pest
```

## Roadmap

- [x] REST API for tickets, priorities, statuses, departments, types
- [x] JSON and XML content negotiation
- [x] Config-driven model swapping
- [x] Soft deletes
- [ ] Custom fields
- [ ] Topics
- [ ] Email integration
- [ ] Frontend client portal
- [ ] Automated ticket routing
- [ ] SLA management
- [ ] Knowledge base
- [ ] Webhook support

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Credits

- Created by [Chase C. Miller](https://crumbls.com)
- Built with [Laravel](https://laravel.com)

## Support

1. Check the [API documentation](docs/)
2. Search [existing issues](https://github.com/crumbls/helpdesk/issues)
3. Open a new issue if needed

As this is beta software, support is limited and response times may vary.
