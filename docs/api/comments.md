# Comments API

The Comments API allows you to manage ticket comments/threads.

## Base URL

```
/api/helpdesk/comments
```

## Endpoints

### List Comments

```
GET /api/helpdesk/comments
```

**Query Parameters:**

| Parameter   | Type    | Required | Description                        |
|-------------|---------|----------|------------------------------------|
| ticket_id   | integer | No       | Filter comments by ticket ID       |
| page        | integer | No       | Page number for pagination         |

**Example Request:**

```bash
curl -X GET "http://localhost/api/helpdesk/comments?ticket_id=1" \
  -H "Accept: application/json"
```

**Example Response:**

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "ticket_id": 1,
      "user_id": 1,
      "content": "Thank you for reporting this issue.",
      "is_private": false,
      "is_resolution": false,
      "created_at": "2026-02-05T10:00:00.000000Z",
      "updated_at": "2026-02-05T10:00:00.000000Z",
      "deleted_at": null,
      "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
      }
    }
  ],
  "first_page_url": "http://localhost/api/helpdesk/comments?page=1",
  "last_page": 1,
  "per_page": 15,
  "total": 1
}
```

### Show Comment

```
GET /api/helpdesk/comments/{id}
```

**Path Parameters:**

| Parameter | Type    | Required | Description           |
|-----------|---------|----------|-----------------------|
| id        | integer | Yes      | The comment ID        |

**Example Request:**

```bash
curl -X GET "http://localhost/api/helpdesk/comments/1" \
  -H "Accept: application/json"
```

**Example Response:**

```json
{
  "id": 1,
  "ticket_id": 1,
  "user_id": 1,
  "content": "Thank you for reporting this issue.",
  "is_private": false,
  "is_resolution": false,
  "created_at": "2026-02-05T10:00:00.000000Z",
  "updated_at": "2026-02-05T10:00:00.000000Z",
  "deleted_at": null,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Error Response (404):**

```json
{
  "error": {
    "message": "Record not found",
    "status": 404
  }
}
```

### Create Comment

```
POST /api/helpdesk/comments
```

**Request Body:**

| Field         | Type    | Required | Description                                    |
|---------------|---------|----------|------------------------------------------------|
| ticket_id     | integer | Yes      | The ticket ID this comment belongs to          |
| user_id       | integer | Yes      | The user ID who authored the comment           |
| content       | string  | Yes      | The comment text content                       |
| is_private    | boolean | No       | Whether the comment is private (default: false)|
| is_resolution | boolean | No       | Whether this marks a resolution (default: false)|

**Example Request:**

```bash
curl -X POST "http://localhost/api/helpdesk/comments" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "ticket_id": 1,
    "user_id": 1,
    "content": "I have identified the issue and will fix it.",
    "is_private": false,
    "is_resolution": false
  }'
```

**Example Response (201):**

```json
{
  "id": 2,
  "ticket_id": 1,
  "user_id": 1,
  "content": "I have identified the issue and will fix it.",
  "is_private": false,
  "is_resolution": false,
  "created_at": "2026-02-05T11:00:00.000000Z",
  "updated_at": "2026-02-05T11:00:00.000000Z",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Validation Error Response (422):**

```json
{
  "message": "The ticket id field is required. (and 2 more errors)",
  "errors": {
    "ticket_id": ["The ticket id field is required."],
    "user_id": ["The user id field is required."],
    "content": ["The content field is required."]
  }
}
```

**Note:** When creating a comment with `is_resolution: true`, any existing resolution comments on the same ticket will have their `is_resolution` flag set to `false`.

### Update Comment

```
PUT /api/helpdesk/comments/{id}
```

**Path Parameters:**

| Parameter | Type    | Required | Description           |
|-----------|---------|----------|-----------------------|
| id        | integer | Yes      | The comment ID        |

**Request Body:**

| Field         | Type    | Required | Description                                    |
|---------------|---------|----------|------------------------------------------------|
| ticket_id     | integer | No       | The ticket ID this comment belongs to          |
| user_id       | integer | No       | The user ID who authored the comment           |
| content       | string  | No       | The comment text content                       |
| is_private    | boolean | No       | Whether the comment is private                 |
| is_resolution | boolean | No       | Whether this marks a resolution                |

**Example Request:**

```bash
curl -X PUT "http://localhost/api/helpdesk/comments/1" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Updated comment content",
    "is_resolution": true
  }'
```

**Example Response:**

```json
{
  "id": 1,
  "ticket_id": 1,
  "user_id": 1,
  "content": "Updated comment content",
  "is_private": false,
  "is_resolution": true,
  "created_at": "2026-02-05T10:00:00.000000Z",
  "updated_at": "2026-02-05T12:00:00.000000Z",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Note:** When updating a comment with `is_resolution: true`, any other resolution comments on the same ticket will have their `is_resolution` flag set to `false`.

### Delete Comment

```
DELETE /api/helpdesk/comments/{id}
```

**Path Parameters:**

| Parameter | Type    | Required | Description           |
|-----------|---------|----------|-----------------------|
| id        | integer | Yes      | The comment ID        |

**Example Request:**

```bash
curl -X DELETE "http://localhost/api/helpdesk/comments/1" \
  -H "Accept: application/json"
```

**Example Response:**

```json
{
  "message": "Comment deleted"
}
```

**Note:** Comments are soft-deleted and can be restored if needed.

## Response Formats

The API supports both JSON and XML responses. Use the `Accept` header to specify the desired format:

- `Accept: application/json` (default)
- `Accept: application/xml`

## Comment Fields

| Field         | Type      | Description                                          |
|---------------|-----------|------------------------------------------------------|
| id            | integer   | Unique comment identifier                            |
| ticket_id     | integer   | ID of the ticket this comment belongs to             |
| user_id       | integer   | ID of the user who created the comment               |
| content       | text      | The comment content                                  |
| is_private    | boolean   | Whether the comment is private (internal notes)      |
| is_resolution | boolean   | Whether this comment represents the ticket resolution|
| created_at    | timestamp | When the comment was created                         |
| updated_at    | timestamp | When the comment was last updated                    |
| deleted_at    | timestamp | When the comment was soft-deleted (null if active)   |
| user          | object    | The user object (included via eager loading)         |

## Private Comments

Private comments (`is_private: true`) are intended for internal notes that should not be visible to the ticket submitter. Your application logic should filter these appropriately based on user permissions.

## Resolution Comments

Only one comment per ticket can be marked as the resolution (`is_resolution: true`). When a new resolution comment is created or an existing comment is updated to be a resolution, any previous resolution comment on the same ticket will automatically have its `is_resolution` flag set to `false`.
