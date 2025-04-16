# Crumbls HelpDesk

A powerful, modern helpdesk solution built on Laravel and Filament. Currently in early beta.

## ⚠️ Beta Status

This package is currently in early beta. While the core functionality is working, please note:

- The frontend client portal is not yet implemented
- APIs are subject to change
- Not recommended for production use without thorough testing
- Breaking changes may occur in minor versions during beta

## 🚀 Features

- **Ticket Management**
  - Create and manage support tickets
  - Threaded comments with internal notes
  - File attachments
  - Priority levels and status tracking
  - Department routing
  - Custom ticket types

- **Admin Panel** (Powered by Filament)
  - Modern, responsive interface
  - Role-based access control
  - Real-time updates
  - Rich text editing
  - Advanced filtering and search

- **Organization**
  - Multi-department support
  - Team assignment
  - Ticket watchers
  - Custom ticket statuses and types

## 📋 Requirements

- PHP 8.1+
- Laravel 10.0+
- Filament 3.0+
- MySQL 5.7+ / PostgreSQL 10.0+

## 🔧 Installation

```bash
composer require crumbls/helpdesk
```

Then publish and run the migrations:

```bash
php artisan vendor:publish --provider="Crumbls\HelpDesk\HelpDeskServiceProvider"
php artisan migrate
```

## ⚙️ Configuration

After installation, configure your helpdesk in `config/helpdesk.php`:

```php
return [
    'route_prefix' => 'helpdesk',
    'middleware' => ['web', 'auth'],
    // ... other options
];
```

## 🔒 Security

- **Authentication**: Integrates with your Laravel authentication
- **Authorization**: Role-based access control using Laravel policies
- **Data Privacy**: Support for internal notes and private comments

## 🗺️ Roadmap

- [ ] Frontend client portal
- [ ] Email integration
- [ ] API endpoints
- [ ] Automated ticket routing
- [ ] SLA management
- [ ] Knowledge base
- [ ] Reporting and analytics
- [ ] Ticket templates
- [ ] Custom fields
- [ ] Webhook support

## 🤝 Contributing

Contributions are welcome! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📝 License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## 🙏 Credits

- Built with [Laravel](https://laravel.com)
- Admin panel powered by [Filament](https://filamentphp.com)
- Created by [Crumbls](https://crumbls.com)

## 📞 Support

For support, please:

1. Check the [documentation](docs/)
2. Search [existing issues](https://github.com/crumbls/helpdesk/issues)
3. Create a new issue if needed

Note: As this is beta software, support is limited and response times may vary.