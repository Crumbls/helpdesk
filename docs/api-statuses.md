# Ticket Statuses API

Base URL: `{APP_URL}/api/helpdesk/statuses`

All endpoints return JSON by default. To receive XML, set the `Accept` header to `application/xml`.

---

## List Statuses

```
GET /api/helpdesk/statuses
```

Returns a paginated list of all ticket statuses.

### curl

```bash
curl -X GET http://localhost/api/helpdesk/statuses \
  -H "Accept: application/json"

# With pagination
curl -X GET "http://localhost/api/helpdesk/statuses?page=2&per_page=10" \
  -H "Accept: application/json"
```

### Response (200)

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "New",
      "description": "Newly created ticket",
      "color_background": "#3B82F6",
      "color_foreground": "#ffffff",
      "is_active": true,
      "is_default": true,
      "is_closed": false,
      "created_at": "2025-04-16T00:00:00.000000Z",
      "updated_at": "2025-04-16T00:00:00.000000Z",
      "deleted_at": null,
      "background_color": "#3B82F6",
      "foreground_color": "#ffffff",
      "color_scheme": {
        "background": "#3B82F6",
        "foreground": "#ffffff"
      }
    }
  ],
  "first_page_url": "http://localhost/api/helpdesk/statuses?page=1",
  "last_page": 1,
  "per_page": 15,
  "total": 1
}
```

---

## Show Status

```
GET /api/helpdesk/statuses/{id}
```

Returns a single ticket status by ID.

### curl

```bash
curl -X GET http://localhost/api/helpdesk/statuses/1 \
  -H "Accept: application/json"
```

### Response (200)

```json
{
  "id": 1,
  "title": "New",
  "description": "Newly created ticket",
  "color_background": "#3B82F6",
  "color_foreground": "#ffffff",
  "is_active": true,
  "is_default": true,
  "is_closed": false,
  "created_at": "2025-04-16T00:00:00.000000Z",
  "updated_at": "2025-04-16T00:00:00.000000Z",
  "deleted_at": null,
  "background_color": "#3B82F6",
  "foreground_color": "#ffffff",
  "color_scheme": {
    "background": "#3B82F6",
    "foreground": "#ffffff"
  }
}
```

### Response (404)

```json
{
  "error": {
    "message": "Record not found",
    "status": 404
  }
}
```

---

## Create Status

```
POST /api/helpdesk/statuses
```

### Fields

| Field              | Type    | Required | Description                           |
|--------------------|---------|----------|---------------------------------------|
| `title`            | string  | yes      | Status name (max 255 chars)           |
| `description`      | string  | no       | Description text                      |
| `color_background` | string  | no       | Background hex color (e.g. #3B82F6)   |
| `color_foreground` | string  | no       | Foreground hex color (e.g. #ffffff)    |
| `is_active`        | boolean | no       | Whether the status is active          |
| `is_default`       | boolean | no       | Set as default status for new tickets |
| `is_closed`        | boolean | no       | Whether this status represents a closed/resolved ticket |

When `is_default` is `true`, all other statuses have their `is_default` set to `false`.

If `color_background` is provided without `color_foreground`, the foreground color is auto-calculated for contrast.

### curl

```bash
# Minimal (title only)
curl -X POST http://localhost/api/helpdesk/statuses \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"title": "In Progress"}'

# Full
curl -X POST http://localhost/api/helpdesk/statuses \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Resolved",
    "description": "Issue has been resolved",
    "color_background": "#10B981",
    "color_foreground": "#ffffff",
    "is_active": true,
    "is_default": false,
    "is_closed": true
  }'

# Create a closed status
curl -X POST http://localhost/api/helpdesk/statuses \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Closed",
    "description": "Ticket has been closed",
    "color_background": "#6B7280",
    "is_closed": true
  }'
```

### Response (201)

Returns the created status object (same structure as Show).

### Response (422)

```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

---

## Update Status

```
PUT /api/helpdesk/statuses/{id}
```

All fields are optional. Only provided fields are updated.

### curl

```bash
# Update title
curl -X PUT http://localhost/api/helpdesk/statuses/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"title": "Under Review"}'

# Mark as closed
curl -X PUT http://localhost/api/helpdesk/statuses/3 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"is_closed": true}'

# Set as default
curl -X PUT http://localhost/api/helpdesk/statuses/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"is_default": true}'
```

### Response (200)

Returns the updated status object.

### Response (404)

```json
{
  "error": {
    "message": "Record not found",
    "status": 404
  }
}
```

---

## Delete Status

```
DELETE /api/helpdesk/statuses/{id}
```

Soft-deletes the status. The record remains in the database with a `deleted_at` timestamp.

### curl

```bash
curl -X DELETE http://localhost/api/helpdesk/statuses/1 \
  -H "Accept: application/json"
```

### Response (200)

```json
{
  "message": "Status deleted"
}
```

### Response (404)

```json
{
  "error": {
    "message": "Record not found",
    "status": 404
  }
}
```

---

## Content Negotiation

All endpoints support JSON and XML responses via the `Accept` header.

```bash
# JSON (default)
curl -X GET http://localhost/api/helpdesk/statuses \
  -H "Accept: application/json"

# XML
curl -X GET http://localhost/api/helpdesk/statuses/1 \
  -H "Accept: application/xml"
```
