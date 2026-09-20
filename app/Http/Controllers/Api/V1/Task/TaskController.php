<?php

namespace App\Http\Controllers\Api\V1\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Task\StoreTaskRequest;
use App\Http\Requests\Api\V1\Task\UpdateTaskRequest;
use App\Http\Resources\Api\V1\Task\TaskResource;
use App\Models\Task;
use App\Services\Task\TaskService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TaskController extends Controller implements HasMiddleware
{
    use ApiResponse;

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:show_tasks', only: ['index', 'show']),
            new Middleware('permission:create_tasks', only: ['store']),
            new Middleware('permission:update_tasks', only: ['update']),
            new Middleware('permission:delete_tasks', only: ['destroy']),
        ];
    }

    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getAll($request->query(), $request->user());

        return $this->successResponse(
            TaskResource::collection($tasks),
            __('messages.task_list')
        );
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->create(
            $request->validated(),
            $request->user()->id
        );

        return $this->successResponse(
            new TaskResource($task),
            __('messages.task_created'),
            201
        );
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): JsonResponse
    {
        $loadedTask = $this->taskService->getById($task);

        return $this->successResponse(
            new TaskResource($loadedTask),
            __('messages.task_retrieved')
        );
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updated = $this->taskService->update($task, $request->validated());

        return $this->successResponse(
            new TaskResource($updated),
            __('messages.task_updated')
        );
    }

    /**
     * Remove the specified task (soft delete).
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->taskService->delete($task);

        return $this->successResponse(
            null,
            __('messages.task_deleted')
        );
    }
}
