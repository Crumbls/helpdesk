# Changelog

All notable changes to `HelpDesk` will be documented in this file.

## 1.0.0 - 202X-XX-XX

- Initial release.

## Unreleased

### Added

- Added a GitHub Actions compatibility matrix for Laravel 9 through 13.
- Added Dependabot updates for GitHub Actions dependencies.
- Added a security policy with a private vulnerability reporting process.
- Added secure API defaults with `web` and `auth` middleware.
- Added department-agent membership through `helpdesk_department_user`.
- Added protected attachment metadata and download endpoints.
- Added custom fields with department and ticket-type scoping.
- Added `last_activity_at`, per-user read/unread state, and ticket queue filters.
- Added direct assign/unassign endpoints with least-loaded and round-robin assignment helpers.
- Added user-key-aware migrations for configurable user models.
- Added dirty-file Pint tooling and a local pre-commit hook installer.
- Added PHPStan baseline and default static analysis config.

### Changed

- Package distribution now excludes development-only files, and the library no longer commits `composer.lock`.
- API, guest, and inbound email routes are independently configurable.
- Activity log and webhook listeners no longer depend on notification listener config.
- Ticket references are generated from the persisted ticket ID.
- Package source and tests have been normalized with Laravel Pint.

### Fixed

- Fixed configured user model resolution on Laravel 9.
- Fixed webhook delivery when the host application uses Laravel 9 without Guzzle installed.
- Fixed nullable system comments for stale-ticket auto-close.
- Fixed notification text that referenced `name` fields on status, priority, and department models that expose `title`.

### Upgrade Notes

- Run the new migrations before using custom fields, department agents, protected attachments, read/unread state, or activity queues.
- Published configs should update `helpdesk.api.middleware` to `['web', 'auth']` unless the API is intentionally public.
- Published configs should add the `models.custom_field`, `models.ticket_read`, and `user_keys` entries.
- Apps with custom user primary keys should configure `helpdesk.user_keys` before running package migrations.
- Optional Filament classes are excluded from default PHPStan analysis unless Filament is installed in the host app.
