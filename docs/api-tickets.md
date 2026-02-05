# Tickets API

Base URL: `/api/helpdesk/tickets`

## List Tickets

```bash
curl -X GET http://localhost/api/helpdesk/tickets \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "ticket_type_id": 1,
      "ticket_status_id": 1,
      "submitter_id": 1,
      "department_id": 1,
      "priority_id": 1,
      "parent_ticket_id": null,
      "title": "Login page returns 500",
      "description": "Getting a server error when trying to log in.",
      "resolution": null,
      "source": "web",
      "due_at": null,
      "closed_at": null,
      "created_at": "2026-02-05T12:00:00.000000Z",
      "updated_at": "2026-02-05T12:00:00.000000Z",
      "deleted_at": null
    }
  ],
  "per_page": 15,
  "total": 1
}
```

## Show Ticket

```bash
curl -X GET http://localhost/api/helpdesk/tickets/1 \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "id": 1,
  "ticket_type_id": 1,
  "ticket_status_id": 1,
  "submitter_id": 1,
  "department_id": 1,
  "priority_id": 1,
  "parent_ticket_id": null,
  "title": "Login page returns 500",
  "description": "Getting a server error when trying to log in.",
  "resolution": null,
  "source": "web",
  "due_at": null,
  "closed_at": null,
  "created_at": "2026-02-05T12:00:00.000000Z",
  "updated_at": "2026-02-05T12:00:00.000000Z",
  "deleted_at": null
}
```

Response (404):

```json
{
  "error": {
    "message": "Record not found",
    "status": 404
  }
}
```

## Create Ticket

```bash
curl -X POST http://localhost/api/helpdesk/tickets \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_type_id": 1,
    "ticket_status_id": 1,
    "submitter_id": 1,
    "department_id": 1,
    "priority_id": 2,
    "title": "Login page returns 500",
    "description": "Getting a server error when trying to log in.",
    "source": "email"
  }'
```

Response (201):

```json
{
  "id": 1,
  "ticket_type_id": 1,
  "ticket_status_id": 1,
  "submitter_id": 1,
  "department_id": 1,
  "priority_id": 2,
  "parent_ticket_id": null,
  "title": "Login page returns 500",
  "description": "Getting a server error when trying to log in.",
  "resolution": null,
  "source": "email",
  "due_at": null,
  "closed_at": null,
  "created_at": "2026-02-05T12:00:00.000000Z",
  "updated_at": "2026-02-05T12:00:00.000000Z",
  "deleted_at": null
}
```

### Required Fields

| Field              | Type    | Description                    |
|--------------------|---------|--------------------------------|
| `ticket_type_id`   | integer | FK to helpdesk_ticket_types    |
| `ticket_status_id` | integer | FK to helpdesk_ticket_statuses |
| `submitter_id`     | integer | FK to users                    |
| `title`            | string  | Ticket title (max 255)         |
| `description`      | string  | Ticket description             |

### Optional Fields

| Field              | Type    | Description                         |
|--------------------|---------|-------------------------------------|
| `department_id`    | integer | FK to helpdesk_departments          |
| `priority_id`      | integer | FK to helpdesk_priorities           |
| `parent_ticket_id` | integer | FK to helpdesk_tickets (self-ref)   |
| `resolution`       | string  | Resolution text                     |
| `source`           | string  | Source channel (web, email, phone, chat) |
| `due_at`           | date    | Due date                            |
| `closed_at`        | date    | Closed date                         |

## Create Sub-Ticket

```bash
curl -X POST http://localhost/api/helpdesk/tickets \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_type_id": 1,
    "ticket_status_id": 1,
    "submitter_id": 1,
    "title": "Sub-task: check database logs",
    "description": "Check the database logs for the parent ticket issue.",
    "parent_ticket_id": 1
  }'
```

## Update Ticket

```bash
curl -X PUT http://localhost/api/helpdesk/tickets/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Login page returns 500 - URGENT"
  }'
```

Response (200):

```json
{
  "id": 1,
  "title": "Login page returns 500 - URGENT",
  "..."
}
```

### Close a Ticket

```bash
curl -X PUT http://localhost/api/helpdesk/tickets/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_status_id": 3,
    "resolution": "Fixed the database connection pooling issue.",
    "closed_at": "2026-02-05"
  }'
```

### Reassign Priority and Department

```bash
curl -X PUT http://localhost/api/helpdesk/tickets/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "priority_id": 5,
    "department_id": 2
  }'
```

## Delete Ticket

```bash
curl -X DELETE http://localhost/api/helpdesk/tickets/1 \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "message": "Ticket deleted"
}
```

## XML Responses

Request XML by setting the `Accept` header:

```bash
curl -X GET http://localhost/api/helpdesk/tickets/1 \
  -H "Accept: application/xml"
```

Response:

```xml
<?xml version="1.0"?>
<response>
  <id>1</id>
  <ticket_type_id>1</ticket_type_id>
  <ticket_status_id>1</ticket_status_id>
  <submitter_id>1</submitter_id>
  <title>Login page returns 500</title>
  <description>Getting a server error when trying to log in.</description>
  <source>web</source>
</response>
```

## Error Responses

### Validation Error (422)

```json
{
  "message": "The ticket type id field is required. (and 4 more errors)",
  "errors": {
    "ticket_type_id": ["The ticket type id field is required."],
    "ticket_status_id": ["The ticket status id field is required."],
    "submitter_id": ["The submitter id field is required."],
    "title": ["The title field is required."],
    "description": ["The description field is required."]
  }
}
```

### Not Found (404)

```json
{
  "error": {
    "message": "Record not found",
    "status": 404
  }
}
```
