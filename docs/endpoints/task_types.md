# Task Types API Documentation

Manage task types categorized by `type` (`network`, `device`, `focus`, `other`). All routes require `Authorization: Bearer <token>`.

Permissions: `show_task_types`, `create_task_types`, `update_task_types`, `delete_task_types`.

---

## 1. List Task Types
- **Method**: `GET`
- **URL**: `/api/task-types`

### Response (200 OK)
```json
{
  "success": true,
  "message": "Task types retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Network Problem",
      "type": "network",
      "created_at": "2026-09-19T14:00:00.000000Z",
      "updated_at": "2026-09-19T14:00:00.000000Z"
    }
  ]
}
```

---

## 2. Create Task Type
- **Method**: `POST`
- **URL**: `/api/task-types`

### Request Body
```json
{
  "name": "Device Maintenance",
  "type": "device"
}
```

### Response (201 Created)
```json
{
  "success": true,
  "message": "Task type created successfully.",
  "data": {
    "id": 2,
    "name": "Device Maintenance",
    "type": "device",
    "created_at": "2026-09-19T14:00:00.000000Z",
    "updated_at": "2026-09-19T14:00:00.000000Z"
  }
}
```

---

## 3. Show Task Type
- **Method**: `GET`
- **URL**: `/api/task-types/{id}`

---

## 4. Update Task Type
- **Method**: `PUT` / `POST`
- **URL**: `/api/task-types/{id}`

### Request Body
```json
{
  "name": "Advanced Device Maintenance",
  "type": "device"
}
```

---

## 5. Delete Task Type (Soft Delete)
- **Method**: `DELETE`
- **URL**: `/api/task-types/{id}`
