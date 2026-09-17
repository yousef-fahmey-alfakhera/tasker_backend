<?php

namespace App\Http\Controllers\Api\V1\TaskStatus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TaskStatus\StoreTaskStatusRequest;
use App\Http\Requests\Api\V1\TaskStatus\UpdateTaskStatusRequest;
use App\Http\Resources\Api\V1\TaskStatus\TaskStatusResource;
use App\Models\TaskStatus;
use App\Services\TaskStatus\TaskStatusService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class TaskStatusController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TaskStatusService $taskStatusService
    ) {}

    /**
     * Display a listing of task statuses.
     */
    public function index(): JsonResponse
    {
        $statuses = $this->taskStatusService->getAll();

        return $this->successResponse(
            TaskStatusResource::collection($statuses),
            __('messages.task_status_list')
        );
    }

    /**
     * Store a newly created task status.
     */
    public function store(StoreTaskStatusRequest $request): JsonResponse
    {
        $status = $this->taskStatusService->create($request->validated());

        return $this->successResponse(
            new TaskStatusResource($status),
            __('messages.task_status_created'),
            201
        );
    }

    /**
     * Display the specified task status.
     */
    public function show(TaskStatus $taskStatus): JsonResponse
    {
        $loaded = $this->taskStatusService->getById($taskStatus);

        return $this->successResponse(
            new TaskStatusResource($loaded),
            __('messages.task_status_retrieved')
        );
    }

    /**
     * Update the specified task status.
     */
    public function update(UpdateTaskStatusRequest $request, TaskStatus $taskStatus): JsonResponse
    {
        $updated = $this->taskStatusService->update($taskStatus, $request->validated());

        return $this->successResponse(
            new TaskStatusResource($updated),
            __('messages.task_status_updated')
        );
    }

    /**
     * Remove the specified task status.
     */
    public function destroy(TaskStatus $taskStatus): JsonResponse
    {
        $this->taskStatusService->delete($taskStatus);

        return $this->successResponse(
            null,
            __('messages.task_status_deleted')
        );
    }
}
