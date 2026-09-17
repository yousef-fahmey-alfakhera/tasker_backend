# Tasker AI Assistant Guidelines

Welcome to **Tasker** - a ClickUp-like task management backend built with Laravel.

## Core Rules for CRUD & Endpoints
All feature modules and CRUD implementations must strictly adhere to the project conventions specified in [.agents/rules/crud_conventions.md](file:///d:/Tasker/tasker_backend/.agents/rules/crud_conventions.md) and [docs/crud_guidelines.md](file:///d:/Tasker/tasker_backend/docs/crud_guidelines.md).

### 1. Routes (`routes/api.php`)
Must always reside in one of two top-level groups:
- `Route::prefix('public')->group(function () { ... });`
- `Route::middleware('auth:sanctum')->group(function () { ... });`

Every resource CRUD must use the group syntax:
```php
Route::group(
    [
        'prefix'     => 'resource-names',
        'controller' => ResourceNameController::class,
    ],
    function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{resourceName}', 'show');
        Route::put('/{resourceName}', 'update');
        Route::post('/{resourceName}', 'update');
        Route::delete('/{resourceName}', 'destroy');
    }
);
```

### 2. Standard Directory Locations
- **Controller**: `app/Http/Controllers/Api/V1/{Feature}/{Feature}Controller.php` (uses `App\Traits\ApiResponse`)
- **Requests**: `app/Http/Requests/Api/V1/{Feature}/Store{Feature}Request.php`, `Update{Feature}Request.php`
- **Resource**: `app/Http/Resources/Api/V1/{Feature}/{Feature}Resource.php`
- **Service**: `app/Services/{Feature}/{Feature}Service.php`
- **Translations**: `lang/en/messages.php` and `lang/ar/messages.php`
- **Feature Tests**: `tests/Feature/Api/V1/{Feature}/{Feature}Test.php`
- **Docs & Postman**: Update `docs/endpoints/{feature}.md` and `docs/collection.json`
