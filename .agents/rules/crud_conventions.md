# Tasker API & CRUD Architecture Conventions

This document specifies the mandatory rules and structure for creating any API endpoint or CRUD feature in the **Tasker** backend.

---

## 1. Route Rules (`routes/api.php`)

All API routes in `routes/api.php` must be organized strictly under two top-level groups:

1. **Public Group** (unauthenticated endpoints):
   ```php
   Route::prefix('public')->group(function () {
       // Public endpoints (e.g. register, login, public invite links)
   });
   ```

2. **Protected Group** (authenticated endpoints):
   ```php
   Route::middleware('auth:sanctum')->group(function () {
       // Protected endpoints (e.g. profile, workspaces, tasks, projects)
   });
   ```

### Standard CRUD Route Group Syntax
Every CRUD module MUST use the following `Route::group` syntax with `prefix` and `controller`:

```php
Route::group(
    [
        'prefix' => 'resource-names', // plural kebab-case
        'controller' => ResourceNameController::class,
    ],
    function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{resourceName}', 'show');
        Route::put('/{resourceName}', 'update');
        Route::post('/{resourceName}', 'update'); // Supports multipart/form-data with file uploads
        Route::delete('/{resourceName}', 'destroy');
    }
);
```

---

## 2. Directory Structure Conventions

For any new feature or CRUD module named `{Feature}` (e.g., `DeviceVerifyRequest`, `Task`, `Workspace`, `Profile`):

| Component | Path | Example |
|---|---|---|
| **Controller** | `app/Http/Controllers/Api/V1/{Feature}/{Feature}Controller.php` | `app/Http/Controllers/Api/V1/Task/TaskController.php` |
| **Form Requests** | `app/Http/Requests/Api/V1/{Feature}/Store{Feature}Request.php`<br>`app/Http/Requests/Api/V1/{Feature}/Update{Feature}Request.php` | `app/Http/Requests/Api/V1/Task/StoreTaskRequest.php`<br>`app/Http/Requests/Api/V1/Task/UpdateTaskRequest.php` |
| **API Resource** | `app/Http/Resources/Api/V1/{Feature}/{Feature}Resource.php` | `app/Http/Resources/Api/V1/Task/TaskResource.php` |
| **Service** | `app/Services/{Feature}/{Feature}Service.php` | `app/Services/Task/TaskService.php` |
| **Feature Seeder** | `database/seeders/{Feature}Seeder.php` | `database/seeders/TaskSeeder.php` (Seeds fake/sample data) |
| **Permission Seeder**| `database/seeders/PermissionSeeder.php` | Register `(show,create,update,delete)_{resource}` with `name_ar` |
| **Translations** | `lang/en/messages.php`<br>`lang/ar/messages.php` | Bilingual keys added in both files |
| **Feature Test** | `tests/Feature/Api/V1/{Feature}/{Feature}Test.php` | `tests/Feature/Api/V1/Task/TaskTest.php` |
| **Documentation** | `docs/endpoints/{feature}.md`<br>`docs/collection.json` | Updated with request/response specs |

---

## 3. Controller Guidelines
- Controllers must use `App\Traits\ApiResponse`.
- Controllers must implement `Illuminate\Routing\Controllers\HasMiddleware` to guard all actions with their respective permissions (`show_*`, `create_*`, `update_*`, `delete_*`).
- Controllers must delegate business logic to the corresponding Service (`app/Services/{Feature}/{Feature}Service.php`).
- Type-hint Form Requests on `store` and `update` actions.
- Return responses using `successResponse($data, $message, $statusCode)` or `errorResponse($message, $statusCode, $errors)`.

Example:
```php
namespace App\Http\Controllers\Api\V1\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Task\StoreTaskRequest;
use App\Http\Requests\Api\V1\Task\UpdateTaskRequest;
use App\Http\Resources\Api\V1\Task\TaskResource;
use App\Services\Task\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(): JsonResponse
    {
        $tasks = $this->taskService->getAll();
        return $this->successResponse(TaskResource::collection($tasks), __('messages.success'));
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create($request->validated());
        return $this->successResponse(new TaskResource($task), __('messages.success'), 201);
    }
}
```

---

## 4. Service Guidelines
- All database operations, business calculations, and third-party interactions must reside in `app/Services/{Feature}/{Feature}Service.php`.
- Services must NOT interact with HTTP request objects directly; they accept clean arrays or typed DTOs/models.

---

## 5. Bilingual Localization Guidelines
- English strings: `lang/en/messages.php`
- Arabic strings: `lang/ar/messages.php`
- Client sends `Accept-Language: en` or `Accept-Language: ar`.
- The `SetLocale` middleware automatically applies the locale to `app()->getLocale()`.

---

## 6. Documentation Guidelines
Whenever a new endpoint or CRUD is created:
1. Add an endpoint documentation file in `docs/endpoints/{feature}.md` detailing:
   - Brief description of what the endpoint does
   - Method and URL
   - Headers (e.g. `Authorization: Bearer <token>`, `Accept-Language: ar|en`)
   - Request body (JSON / Form-data)
   - Response examples (200/201 Success, 422 Validation Error, 401 Unauthorized)
2. Update `docs/collection.json` (Postman Collection v2.1) with the new request items and example responses.

---

## 7. Testing Guidelines
- Every CRUD must have a corresponding Feature Test in `tests/Feature/Api/V1/{Feature}/{Feature}Test.php`.
- Tests must cover:
  - Validation failures (HTTP 422)
  - Unauthenticated access prevention (HTTP 401)
  - Unauthorized access prevention without permission (HTTP 403)
  - Successful execution (HTTP 200/201) with response assertions
  - Both English and Arabic localized responses if applicable

---

## 8. Seeder & Permission Conventions
Whenever a new CRUD module `{Feature}` is implemented:
1. **Feature Seeder**: Create `database/seeders/{Feature}Seeder.php` to populate realistic fake/sample data for local testing and development.
2. **Permission Seeder**: Register the feature's 4 core permissions in `database/seeders/PermissionSeeder.php`:
   - Format: `show_{resources}`, `create_{resources}`, `update_{resources}`, `delete_{resources}`
   - Attributes: `guard_name => 'sanctum'`, `name_ar => '<Arabic translation>'`
3. **Endpoint Guarding**: Implement `Illuminate\Routing\Controllers\HasMiddleware` in `{Feature}Controller.php`:
   ```php
   public static function middleware(): array
   {
       return [
           new Middleware('permission:show_resources', only: ['index', 'show']),
           new Middleware('permission:create_resources', only: ['store']),
           new Middleware('permission:update_resources', only: ['update']),
           new Middleware('permission:delete_resources', only: ['destroy']),
       ];
   }
   ```
4. **DatabaseSeeder**: Register the new seeder in `database/seeders/DatabaseSeeder.php`.
