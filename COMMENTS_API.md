# Comments API Documentation

This document describes the Comments API endpoints for the Goal Digger application.

## Authentication

All endpoints require authentication using Laravel Passport. Include the Bearer token in the Authorization header:

```
Authorization: Bearer <your-token>
```

## Endpoints

### GET /api/goals/{goalId}/comments

Retrieve all comments for a specific goal.

**Authorization Requirements:**

-   You must own the goal, OR
-   You must be an accepted buddy for the goal, OR
-   You must be an accepted buddy for any parent goal

**Response:**

```json
{
    "data": [
        {
            "id": 1,
            "content": "This is a root comment",
            "user": {
                "id": 1,
                "name": "John Doe"
            },
            "created_at": "2025-01-01T12:00:00.000000Z",
            "updated_at": "2025-01-01T12:00:00.000000Z",
            "replies": [
                {
                    "id": 2,
                    "content": "This is a reply",
                    "user": {
                        "id": 2,
                        "name": "Jane Smith"
                    },
                    "created_at": "2025-01-01T12:05:00.000000Z",
                    "updated_at": "2025-01-01T12:05:00.000000Z"
                }
            ]
        }
    ]
}
```

### POST /api/goals/{goalId}/comments

Create a new comment for a specific goal.

**Authorization Requirements:**

-   You must own the goal, OR
-   You must be an accepted buddy for the goal, OR
-   You must be an accepted buddy for any parent goal

**Request Body:**

```json
{
    "content": "Your comment content here",
    "parent_id": null // Optional: ID of parent comment for replies
}
```

**Validation Rules:**

-   `content`: Required, string, max 1000 characters
-   `parent_id`: Optional, must exist in comments table
-   If `parent_id` is provided, it must be a root comment (no parent)
-   Only one reply is allowed per root comment
-   Cannot reply to a reply (only one level deep)

**Response:**

```json
{
    "data": {
        "id": 1,
        "content": "Your comment content here",
        "user": {
            "id": 1,
            "name": "John Doe"
        },
        "parent_id": null,
        "created_at": "2025-01-01T12:00:00.000000Z",
        "updated_at": "2025-01-01T12:00:00.000000Z"
    }
}
```

### PATCH /api/goals/{goalId}/comments/{commentId}

Update an existing comment.

**Authorization Requirements:**

-   You must own the comment (be the user who created it)

**Request Body:**

```json
{
    "content": "Updated comment content"
}
```

**Validation Rules:**

-   `content`: Required, string, max 1000 characters

**Response:**

```json
{
    "data": {
        "id": 1,
        "content": "Updated comment content",
        "user": {
            "id": 1,
            "name": "John Doe"
        },
        "parent_id": null,
        "created_at": "2025-01-01T12:00:00.000000Z",
        "updated_at": "2025-01-01T12:10:00.000000Z"
    }
}
```

### DELETE /api/goals/{goalId}/comments/{commentId}

Delete a comment.

**Authorization Requirements:**

-   You must own the comment (be the user who created it)

**Response:**

```json
{
    "message": "Comment deleted successfully"
}
```

## Error Responses

### 403 Forbidden

Returned when the user doesn't have permission to access the goal or modify the comment.

```json
{
    "message": "Unauthorized"
}
```

### 422 Unprocessable Entity

Returned when validation fails.

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "content": ["The content field is required."],
        "parent_id": [
            "Cannot add more replies to this comment. Only one level of replies is allowed."
        ]
    }
}
```

### 404 Not Found

Returned when the goal or comment doesn't exist.

## Comment Structure

Comments support a simple two-level hierarchy:

1. **Root Comments**: Top-level comments with no parent
2. **Replies**: Direct responses to root comments (only one level deep)

**Rules:**

-   Each root comment can have at most one reply
-   You cannot reply to a reply (no deeper nesting)
-   All comments must be associated with a goal
-   Comments are ordered by creation date (newest first)

## Buddy Access

Buddies can access comments on goals they have been invited to, including:

-   Direct access to goals they are buddies for
-   Access to child goals if they are buddies for the parent goal
-   Access is inherited up the goal hierarchy

**Buddy Status Requirements:**

-   Only `ACCEPTED` buddy relationships grant access
-   `PENDING` and `REJECTED` relationships are denied access
