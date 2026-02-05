# Ticket Types API

Base URL: `/api/helpdesk/types`

## List Types

```bash
curl -X GET http://localhost/api/helpdesk/types \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "Bug Report",
      "description": "Report a software defect",
      "color_background": "#EF4444",
      "color_foreground": "#ffffff",
      "is_active": true,
      "created_at": "2026-02-05T12:00:00.000000Z",
      "updated_at": "2026-02-05T12:00:00.000000Z",
      "deleted_at": null,
      "background_color": "#EF4444",
      "foreground_color": "#ffffff",
      "color_scheme": "background-color: #EF4444; color: #ffffff;"
    }
  ],
  "per_page": 15,
  "total": 1
}
```

## Show Type

```bash
curl -X GET http://localhost/api/helpdesk/types/1 \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "id": 1,
  "title": "Bug Report",
  "description": "Report a software defect",
  "color_background": "#EF4444",
  "color_foreground": "#ffffff",
  "is_active": true,
  "created_at": "2026-02-05T12:00:00.000000Z",
  "updated_at": "2026-02-05T12:00:00.000000Z",
  "deleted_at": null,
  "background_color": "#EF4444",
  "foreground_color": "#ffffff",
  "color_scheme": "background-color: #EF4444; color: #ffffff;"
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

## Create Type

```bash
curl -X POST http://localhost/api/helpdesk/types \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Bug Report",
    "description": "Report a software defect",
    "color_background": "#EF4444",
    "is_active": true
  }'
```

Response (201):

```json
{
  "id": 1,
  "title": "Bug Report",
  "description": "Report a software defect",
  "color_background": "#EF4444",
  "color_foreground": "#ffffff",
  "is_active": true,
  "created_at": "2026-02-05T12:00:00.000000Z",
  "updated_at": "2026-02-05T12:00:00.000000Z",
  "deleted_at": null,
  "background_color": "#EF4444",
  "foreground_color": "#ffffff",
  "color_scheme": "background-color: #EF4444; color: #ffffff;"
}
```

### Fields

| Field              | Type    | Required | Description                          |
|--------------------|---------|----------|--------------------------------------|
| `title`            | string  | Yes      | Type name (max 255)                  |
| `description`      | string  | No       | Description of the type              |
| `color_background` | string  | No       | Background hex color (e.g. #EF4444)  |
| `color_foreground` | string  | No       | Foreground hex color (auto-calculated if omitted) |
| `is_active`        | boolean | No       | Whether the type is active (default true) |

## Update Type

```bash
curl -X PUT http://localhost/api/helpdesk/types/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Bug Report - Critical"
  }'
```

Response (200):

```json
{
  "id": 1,
  "title": "Bug Report - Critical",
  "..."
}
```

### Deactivate a Type

```bash
curl -X PUT http://localhost/api/helpdesk/types/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "is_active": false
  }'
```

## Delete Type

```bash
curl -X DELETE http://localhost/api/helpdesk/types/1 \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "message": "Type deleted"
}
```

## XML Responses

Request XML by setting the `Accept` header:

```bash
curl -X GET http://localhost/api/helpdesk/types/1 \
  -H "Accept: application/xml"
```

Response:

```xml
<?xml version="1.0"?>
<response>
  <id>1</id>
  <title>Bug Report</title>
  <description>Report a software defect</description>
  <color_background>#EF4444</color_background>
  <color_foreground>#ffffff</color_foreground>
  <is_active>1</is_active>
</response>
```

## Error Responses

### Validation Error (422)

```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."]
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
