<?php

namespace App\Http\Controllers\Api\V1\UserType;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserType\StoreUserTypeRequest;
use App\Http\Requests\Api\V1\UserType\UpdateUserTypeRequest;
use App\Http\Resources\Api\V1\UserType\UserTypeResource;
use App\Models\UserType;
use App\Services\UserType\UserTypeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserTypeController extends Controller implements HasMiddleware
{
    use ApiResponse;

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:show_user_types', only: ['index', 'show']),
            new Middleware('permission:create_user_types', only: ['store']),
            new Middleware('permission:update_user_types', only: ['update']),
            new Middleware('permission:delete_user_types', only: ['destroy']),
        ];
    }

    public function __construct(
        protected UserTypeService $userTypeService
    ) {}

    /**
     * Display a listing of user types.
     */
    public function index(): JsonResponse
    {
        $userTypes = $this->userTypeService->getAll();

        return $this->successResponse(
            UserTypeResource::collection($userTypes),
            __('messages.user_type_list')
        );
    }

    /**
     * Store a newly created user type.
     */
    public function store(StoreUserTypeRequest $request): JsonResponse
    {
        $userType = $this->userTypeService->create($request->validated());

        return $this->successResponse(
            new UserTypeResource($userType),
            __('messages.user_type_created'),
            201
        );
    }

    /**
     * Display the specified user type.
     */
    public function show(UserType $userType): JsonResponse
    {
        $loadedUserType = $this->userTypeService->getById($userType);

        return $this->successResponse(
            new UserTypeResource($loadedUserType),
            __('messages.user_type_retrieved')
        );
    }

    /**
     * Update the specified user type.
     */
    public function update(UpdateUserTypeRequest $request, UserType $userType): JsonResponse
    {
        $updated = $this->userTypeService->update($userType, $request->validated());

        return $this->successResponse(
            new UserTypeResource($updated),
            __('messages.user_type_updated')
        );
    }

    /**
     * Remove the specified user type.
     */
    public function destroy(UserType $userType): JsonResponse
    {
        $this->userTypeService->delete($userType);

        return $this->successResponse(
            null,
            __('messages.user_type_deleted')
        );
    }
}
