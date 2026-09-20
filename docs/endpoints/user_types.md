# User Types API Documentation

Manage user types and responsibility domains (e.g., `it`, `admin`, `normal`, `manager`, etc. with responsibilities array like `['focus', 'device', 'network']`).

Permissions: `show_user_types`, `create_user_types`, `update_user_types`, `delete_user_types`.

---

## 1. List User Types
- **Method**: `GET`
- **URL**: `/api/user-types`

### Response (200 OK)
```json
{
  "success": true,
  "message": "User types retrieved successfully.",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "user": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@admin.com"
      },
      "type": "it",
      "respnsapity": ["focus", "device", "network"],
      "created_at": "2026-09-19T14:00:00.000000Z",
      "updated_at": "2026-09-19T14:00:00.000000Z"
    }
  ]
}
```

---

## 2. Create User Type
- **Method**: `POST`
- **URL**: `/api/user-types`

### Request Body
```json
{
  "user_id": 2,
  "type": "it",
  "respnsapity": ["focus", "device"]
}
```

### Response (201 Created)
```json
{
  "success": true,
  "message": "User type created successfully.",
  "data": {
    "id": 2,
    "user_id": 2,
    "type": "it",
    "respnsapity": ["focus", "device"],
    "created_at": "2026-09-19T14:00:00.000000Z",
    "updated_at": "2026-09-19T14:00:00.000000Z"
  }
}
```

---

## 3. Show User Type
- **Method**: `GET`
- **URL**: `/api/user-types/{id}`

---

## 4. Update User Type
- **Method**: `PUT` / `POST`
- **URL**: `/api/user-types/{id}`

### Request Body
```json
{
  "type": "manager",
  "respnsapity": ["focus", "device", "other"]
}
```

---

## 5. Delete User Type
- **Method**: `DELETE`
- **URL**: `/api/user-types/{id}`
