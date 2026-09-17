<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Profile\ProfileController;
use App\Http\Controllers\Api\V1\Project\ProjectController;
use App\Http\Controllers\Api\V1\Task\TaskController;
use App\Http\Controllers\Api\V1\TaskStatus\TaskStatusController;
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
});
