# Production Readiness TODO

## Phase 1: Security and Package Foundations

- [x] Require authentication by default for the API.
- [x] Keep API, guest, and inbound email routes independently configurable.
- [x] Register activity log and webhook listeners independently from notification listeners.
- [x] Add department-agent membership table/model and wire the existing relation.
- [x] Protect attachment metadata and downloads with ticket/comment access checks.
- [x] Add user-key-aware migrations for configurable user models.
- [x] Remove or finish incomplete custom field stubs.
- [x] Replace race-prone ticket reference generation.

## Phase 2: Support Workflow

- [ ] Build customer-facing ticket portal and conversation view.
- [x] Add read/unread state and `last_activity_at` tracking.
- [x] Add operational ticket queues: open, closed, unassigned, assigned to me, department.
- [ ] Add department-scoped agent permissions.
- [x] Add assignment actions and round-robin/least-loaded assignment helpers.
- [ ] Add private/internal note handling across API and Filament.

## Phase 3: Product Depth

- [ ] Add dynamic ticket forms and department-form assignment.
- [ ] Add automation rules, conditions, actions, and execution logs.
- [ ] Add spam filters and spam logs beyond honeypot protection.
- [ ] Add dashboard/stat widgets.
- [ ] Add SLA breach scanner and notifications.
- [ ] Harden inbound email parsing and ticket reference matching.

## Phase 4: Quality Gates and Documentation

- [x] Bring `composer.lock` back in sync with `composer.json`.
- [x] Exclude temporary/vendor-like files from Pint.
- [x] Add dirty-file Pint command and pre-commit hook.
- [x] Exclude temporary/vendor-like files from static analysis.
- [x] Fix Pint style drift in package source and tests.
- [x] Fix PHPStan errors or introduce a deliberate baseline with follow-up cleanup.
- [ ] Burn down `phpstan-baseline.neon` by adding relation generics and typed config/model helpers.
- [x] Update README to match implemented features and secure defaults.
- [x] Add upgrade notes for breaking schema/config changes.
