# Departments API

Base URL: `/api/helpdesk/departments`

## List Departments

```bash
curl -X GET http://localhost/api/helpdesk/departments \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "title": "Customer Support",
      "description": "Handles customer inquiries",
      "color_background": "#3B82F6",
      "color_foreground": "#ffffff",
      "is_active": true,
      "created_at": "2026-02-05T12:00:00.000000Z",
      "updated_at": "2026-02-05T12:00:00.000000Z",
      "deleted_at": null,
      "background_color": "#3B82F6",
      "foreground_color": "#ffffff",
      "color_scheme": "background-color: #3B82F6; color: #ffffff;"
    }
  ],
  "per_page": 15,
  "total": 1
}
```

## Show Department

```bash
curl -X GET http://localhost/api/helpdesk/departments/1 \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "id": 1,
  "title": "Customer Support",
  "description": "Handles customer inquiries",
  "color_background": "#3B82F6",
  "color_foreground": "#ffffff",
  "is_active": true,
  "created_at": "2026-02-05T12:00:00.000000Z",
  "updated_at": "2026-02-05T12:00:00.000000Z",
  "deleted_at": null,
  "background_color": "#3B82F6",
  "foreground_color": "#ffffff",
  "color_scheme": "background-color: #3B82F6; color: #ffffff;"
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

## Create Department

```bash
curl -X POST http://localhost/api/helpdesk/departments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Customer Support",
    "description": "Handles customer inquiries",
    "color_background": "#3B82F6",
    "is_active": true
  }'
```

Response (201):

```json
{
  "id": 1,
  "title": "Customer Support",
  "description": "Handles customer inquiries",
  "color_background": "#3B82F6",
  "color_foreground": "#ffffff",
  "is_active": true,
  "created_at": "2026-02-05T12:00:00.000000Z",
  "updated_at": "2026-02-05T12:00:00.000000Z",
  "deleted_at": null,
  "background_color": "#3B82F6",
  "foreground_color": "#ffffff",
  "color_scheme": "background-color: #3B82F6; color: #ffffff;"
}
```

### Fields

| Field              | Type    | Required | Description                          |
|--------------------|---------|----------|--------------------------------------|
| `title`            | string  | Yes      | Department name (max 255)            |
| `description`      | string  | No       | Description of the department        |
| `color_background` | string  | No       | Background hex color (e.g. #3B82F6)  |
| `color_foreground` | string  | No       | Foreground hex color (auto-calculated if omitted) |
| `is_active`        | boolean | No       | Whether the department is active (default true) |

## Update Department

```bash
curl -X PUT http://localhost/api/helpdesk/departments/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Customer Success"
  }'
```

Response (200):

```json
{
  "id": 1,
  "title": "Customer Success",
  "..."
}
```

### Deactivate a Department

```bash
curl -X PUT http://localhost/api/helpdesk/departments/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "is_active": false
  }'
```

## Delete Department

```bash
curl -X DELETE http://localhost/api/helpdesk/departments/1 \
  -H "Accept: application/json"
```

Response (200):

```json
{
  "message": "Department deleted"
}
```

## XML Responses

Request XML by setting the `Accept` header:

```bash
curl -X GET http://localhost/api/helpdesk/departments/1 \
  -H "Accept: application/xml"
```

Response:

```xml
<?xml version="1.0"?>
<response>
  <id>1</id>
  <title>Customer Support</title>
  <description>Handles customer inquiries</description>
  <color_background>#3B82F6</color_background>
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
