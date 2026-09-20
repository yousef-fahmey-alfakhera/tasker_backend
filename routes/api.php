<?php

use App\Http\Controllers\Api\V1\Attachment\AttachmentController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Permission\PermissionController;
use App\Http\Controllers\Api\V1\Profile\ProfileController;
use App\Http\Controllers\Api\V1\Project\ProjectController;
use App\Http\Controllers\Api\V1\Setting\SettingController;
use App\Http\Controllers\Api\V1\Task\TaskController;
use App\Http\Controllers\Api\V1\TaskStatus\TaskStatusController;
use App\Http\Controllers\Api\V1\TaskType\TaskTypeController;
use App\Http\Controllers\Api\V1\UserSetting\UserSettingController;
use App\Http\Controllers\Api\V1\UserType\UserTypeController;
use App\Http\Controllers\Api\V1\Workspace\WorkspaceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
|
| Endpoints accessible without authentication.
|
*/
Route::prefix('public')->group(function () {
    Route::group(
        [
            'prefix'     => 'auth',
            'controller' => AuthController::class,
        ],
        function () {
            Route::post('/register', 'register');
            Route::post('/login', 'login');
        }
    );
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum Authenticated)
|--------------------------------------------------------------------------
|
| Endpoints requiring a valid Bearer token.
|
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth actions (logout)
    Route::group(
        [
            'prefix'     => 'auth',
            'controller' => AuthController::class,
        ],
        function () {
            Route::post('/logout', 'logout');
            Route::get('/permissions', 'permissions');
        }
    );

    // Profile CRUD
    Route::group(
        [
            'prefix'     => 'profile',
            'controller' => ProfileController::class,
        ],
        function () {
            Route::get('/', 'show');
            Route::put('/', 'update');
            Route::post('/', 'update');
        }
    );

    // Projects CRUD
    Route::group(
        [
            'prefix'     => 'projects',
            'controller' => ProjectController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{project}', 'show');
            Route::put('/{project}', 'update');
            Route::post('/{project}', 'update');
            Route::delete('/{project}', 'destroy');
        }
    );

    // Workspaces CRUD
    Route::group(
        [
            'prefix'     => 'workspaces',
            'controller' => WorkspaceController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{workspace}', 'show');
            Route::put('/{workspace}', 'update');
            Route::post('/{workspace}', 'update');
            Route::delete('/{workspace}', 'destroy');
        }
    );

    // Task Statuses CRUD
    Route::group(
        [
            'prefix'     => 'task-statuses',
            'controller' => TaskStatusController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{taskStatus}', 'show');
            Route::put('/{taskStatus}', 'update');
            Route::post('/{taskStatus}', 'update');
            Route::delete('/{taskStatus}', 'destroy');
        }
    );

    // Tasks CRUD
    Route::group(
        [
            'prefix'     => 'tasks',
            'controller' => TaskController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{task}', 'show');
            Route::put('/{task}', 'update');
            Route::post('/{task}', 'update');
            Route::delete('/{task}', 'destroy');
        }
    );

    // Attachments CRUD
    Route::group(
        [
            'prefix'     => 'attachments',
            'controller' => AttachmentController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{attachment}', 'show');
            Route::put('/{attachment}', 'update');
            Route::post('/{attachment}', 'update');
            Route::delete('/{attachment}', 'destroy');
        }
    );

    // Permissions
    Route::group(
        [
            'prefix'     => 'permissions',
            'controller' => PermissionController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::get('/me', 'userPermissions');
        }
    );

    // Settings CRUD
    Route::group(
        [
            'prefix'     => 'settings',
            'controller' => SettingController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{setting}', 'show');
            Route::put('/{setting}', 'update');
            Route::post('/{setting}', 'update');
            Route::delete('/{setting}', 'destroy');
        }
    );

    // User Settings CRUD & Theme / Light Mode
    Route::group(
        [
            'prefix'     => 'user-settings',
            'controller' => UserSettingController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/theme', 'getTheme');
            Route::post('/theme', 'setTheme');
            Route::get('/{userSetting}', 'show');
            Route::put('/{userSetting}', 'update');
            Route::post('/{userSetting}', 'update');
            Route::delete('/{userSetting}', 'destroy');
        }
    );

    // Task Types CRUD
    Route::group(
        [
            'prefix'     => 'task-types',
            'controller' => TaskTypeController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{taskType}', 'show');
            Route::put('/{taskType}', 'update');
            Route::post('/{taskType}', 'update');
            Route::delete('/{taskType}', 'destroy');
        }
    );

    // User Types CRUD
    Route::group(
        [
            'prefix'     => 'user-types',
            'controller' => UserTypeController::class,
        ],
        function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{userType}', 'show');
            Route::put('/{userType}', 'update');
            Route::post('/{userType}', 'update');
            Route::delete('/{userType}', 'destroy');
        }
    );
});
