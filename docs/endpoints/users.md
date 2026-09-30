# Users API Documentation

Manage users, user codes, department / project assignment, roles, and soft deletes (trash & restore). All routes require `Authorization: Bearer <token>`.

---

## 1. List Users

List active users with pagination and search.

- **Method**: `GET`
- **URL**: `/api/users?per_page=15&search=John`
- **Headers**:
  - `Authorization: Bearer <token>`
  - `Accept: application/json`

### Response (200 OK)
```json
{
  "success": true,
  "message": "Users retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@tasker.test",
      "code": "EMP-001",
      "project_id": 1,
      "project": {
        "id": 1,
        "name": "Engineering Department"
      },
      "user_type": null,
      "roles": ["admin"],
      "created_tasks_count": 5,
      "fixed_tasks_count": 3,
      "created_at": "2026-09-30T10:00:00.000000Z",
      "updated_at": "2026-09-30T10:00:00.000000Z",
      "deleted_at": null
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

---

## 2. Create User

Create a new user. `code` is optional and nullable. `project_id` (Department) is required; if missing, returns Arabic error `"برجاء اختيار القسم"`.

- **Method**: `POST`
- **URL**: `/api/users`
- **Headers**:
  - `Authorization: Bearer <token>`
  - `Accept: application/json`
  - `Content-Type: application/json`

### Request Body (JSON)
```json
{
  "name": "Jane Smith",
  "email": "jane.smith@tasker.test",
  "password": "Password123!",
  "code": "EMP-102",
  "project_id": 1,
  "role": "user"
}
```

### Validation Error (422 Unprocessable Entity - Missing `project_id`)
```json
{
  "message": "برجاء اختيار القسم",
  "errors": {
    "project_id": [
      "برجاء اختيار القسم"
    ]
  }
}
```

---

## 3. Get Trashed (Soft-Deleted) Users

Retrieves list of soft-deleted users.

- **Method**: `GET`
- **URL**: `/api/users/trashed?per_page=15`
- **Headers**:
  - `Authorization: Bearer <token>`
  - `Accept: application/json`

### Response (200 OK)
```json
{
  "success": true,
  "message": "Deleted users retrieved successfully.",
  "data": [
    {
      "id": 5,
      "name": "Old Staff",
      "email": "old@tasker.test",
      "code": "EMP-099",
      "project_id": 1,
      "deleted_at": "2026-09-30T11:00:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 1,
    "last_page": 1
  }
}
```

---

## 4. Restore Soft-Deleted User

Restores a previously soft-deleted user.

- **Method**: `POST`
- **URL**: `/api/users/{id}/restore`
- **Headers**:
  - `Authorization: Bearer <token>`
  - `Accept: application/json`

### Response (200 OK)
```json
{
  "success": true,
  "message": "User restored successfully.",
  "data": {
    "id": 5,
    "name": "Old Staff",
    "email": "old@tasker.test",
    "code": "EMP-099",
    "project_id": 1,
    "deleted_at": null
  }
}
```

---

## 5. Show User

- **Method**: `GET`
- **URL**: `/api/users/{user}`

---

## 6. Update User

- **Method**: `PUT` / `POST`
- **URL**: `/api/users/{user}`

---

## 7. Soft Delete User

- **Method**: `DELETE`
- **URL**: `/api/users/{user}`

### Response (200 OK)
```json
{
  "success": true,
  "message": "User deleted successfully.",
  "data": null
}
```
