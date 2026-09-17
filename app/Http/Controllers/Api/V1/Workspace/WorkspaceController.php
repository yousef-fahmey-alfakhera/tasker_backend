<?php

namespace App\Http\Controllers\Api\V1\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Workspace\StoreWorkspaceRequest;
use App\Http\Requests\Api\V1\Workspace\UpdateWorkspaceRequest;
use App\Http\Resources\Api\V1\Workspace\WorkspaceResource;
use App\Models\Workspace;
use App\Services\Workspace\WorkspaceService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class WorkspaceController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected WorkspaceService $workspaceService
    ) {}

    /**
     * Display a listing of workspaces.
     */
    public function index(): JsonResponse
    {
        $workspaces = $this->workspaceService->getAll();

        return $this->successResponse(
            WorkspaceResource::collection($workspaces),
            __('messages.workspace_list')
        );
    }

    /**
     * Store a newly created workspace.
     */
    public function store(StoreWorkspaceRequest $request): JsonResponse
    {
        $workspace = $this->workspaceService->create(
            $request->validated(),
            $request->user()->id
        );

        return $this->successResponse(
            new WorkspaceResource($workspace),
            __('messages.workspace_created'),
            201
        );
    }

    /**
     * Display the specified workspace.
     */
    public function show(Workspace $workspace): JsonResponse
    {
        $loadedWorkspace = $this->workspaceService->getById($workspace);

        return $this->successResponse(
            new WorkspaceResource($loadedWorkspace),
            __('messages.workspace_retrieved')
        );
    }

    /**
     * Update the specified workspace.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): JsonResponse
    {
        $updated = $this->workspaceService->update($workspace, $request->validated());

        return $this->successResponse(
            new WorkspaceResource($updated),
            __('messages.workspace_updated')
        );
    }

    /**
     * Remove the specified workspace.
     */
    public function destroy(Workspace $workspace): JsonResponse
    {
        $this->workspaceService->delete($workspace);

        return $this->successResponse(
            null,
            __('messages.workspace_deleted')
        );
    }
}
