# Priorities API

Base URL: `{APP_URL}/api/helpdesk/priorities`

All endpoints return JSON by default. To receive XML, set the `Accept` header to `application/xml`.

---

## List Priorities

```
GET /api/helpdesk/priorities
```

Returns a paginated list of all priorities.

### curl

```bash
# JSON (default)
curl -X GET http://localhost/api/helpdesk/priorities \
  -H "Accept: application/json"

# XML
curl -X GET http://localhost/api/helpdesk/priorities \
  -H "Accept: application/xml"

# With pagination
curl -X GET "http://localhost/api/helpdesk/priorities?page=2&per_page=10" \
  -H "Accept: application/json"
```

### Response (200)

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "Urgent",
      "description": "Critical issues requiring immediate attention",
      "color_background": "#EF4444",
      "color_foreground": "#ffffff",
      "level": 1,
      "is_active": true,
      "is_default": false,
      "created_at": "2025-04-16T00:00:00.000000Z",
      "updated_at": "2025-04-16T00:00:00.000000Z",
      "deleted_at": null,
      "background_color": "#EF4444",
      "foreground_color": "#ffffff",
      "color_scheme": {
        "background": "#EF4444",
        "foreground": "#ffffff"
      }
    }
  ],
  "first_page_url": "http://localhost/api/helpdesk/priorities?page=1",
  "last_page": 1,
  "per_page": 15,
  "total": 1
}
```

---

## Show Priority

```
GET /api/helpdesk/priorities/{id}
```

Returns a single priority by ID.

### curl

```bash
curl -X GET http://localhost/api/helpdesk/priorities/1 \
  -H "Accept: application/json"
```

### Response (200)

```json
{
  "id": 1,
  "title": "Urgent",
  "description": "Critical issues requiring immediate attention",
  "color_background": "#EF4444",
  "color_foreground": "#ffffff",
  "level": 1,
  "is_active": true,
  "is_default": false,
  "created_at": "2025-04-16T00:00:00.000000Z",
  "updated_at": "2025-04-16T00:00:00.000000Z",
  "deleted_at": null,
  "background_color": "#EF4444",
  "foreground_color": "#ffffff",
  "color_scheme": {
    "background": "#EF4444",
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

## Create Priority

```
POST /api/helpdesk/priorities
```

### Fields

| Field              | Type    | Required | Description                        |
|--------------------|---------|----------|------------------------------------|
| `title`            | string  | yes      | Priority name (max 255 chars)      |
| `description`      | string  | no       | Description text                   |
| `color_background` | string  | no       | Background hex color (e.g. #EF4444)|
| `color_foreground` | string  | no       | Foreground hex color (e.g. #ffffff)|
| `level`            | integer | no       | Numeric level (min 0)              |
| `is_active`        | boolean | no       | Whether the priority is active     |
| `is_default`       | boolean | no       | Set as default priority            |

When `is_default` is `true`, all other priorities have their `is_default` set to `false`.

If `color_background` is provided without `color_foreground`, the foreground color is auto-calculated for contrast.

### curl

```bash
# Minimal (title only)
curl -X POST http://localhost/api/helpdesk/priorities \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"title": "Critical"}'

# Full
curl -X POST http://localhost/api/helpdesk/priorities \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Critical",
    "description": "Highest priority issues",
    "color_background": "#EF4444",
    "color_foreground": "#ffffff",
    "level": 1,
    "is_active": true,
    "is_default": false
  }'
```

### Response (201)

Returns the created priority object (same structure as Show).

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

## Update Priority

```
PUT /api/helpdesk/priorities/{id}
```

All fields are optional. Only provided fields are updated.

### curl

```bash
# Update title only
curl -X PUT http://localhost/api/helpdesk/priorities/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"title": "Very Urgent"}'

# Update colors
curl -X PUT http://localhost/api/helpdesk/priorities/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "color_background": "#DC2626",
    "color_foreground": "#ffffff"
  }'

# Set as default
curl -X PUT http://localhost/api/helpdesk/priorities/3 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"is_default": true}'
```

### Response (200)

Returns the updated priority object.

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

## Delete Priority

```
DELETE /api/helpdesk/priorities/{id}
```

Soft-deletes the priority. The record remains in the database with a `deleted_at` timestamp.

### curl

```bash
curl -X DELETE http://localhost/api/helpdesk/priorities/1 \
  -H "Accept: application/json"
```

### Response (200)

```json
{
  "message": "Priority deleted"
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
curl -X GET http://localhost/api/helpdesk/priorities \
  -H "Accept: application/json"

# XML
curl -X GET http://localhost/api/helpdesk/priorities/1 \
  -H "Accept: application/xml"
```

XML responses wrap data in a `<response>` root element with nested structure preserved.
