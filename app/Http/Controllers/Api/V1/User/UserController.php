<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\StoreUserRequest;
use App\Http\Requests\Api\V1\User\UpdateUserRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Models\User;
use App\Services\User\UserService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    use ApiResponse;

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:show_users', only: ['index', 'show', 'trashed']),
            new Middleware('permission:create_users', only: ['store']),
            new Middleware('permission:update_users', only: ['update', 'restore']),
            new Middleware('permission:delete_users', only: ['destroy']),
        ];
    }

    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of active users.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $users = $this->userService->getAll($request->query(), $perPage);

        return $this->successResponse(
            UserResource::collection($users),
            __('messages.user_list'),
            200,
            [
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                    'last_page'    => $users->lastPage(),
                ],
            ]
        );
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return $this->successResponse(
            new UserResource($user),
            __('messages.user_created'),
            201
        );
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $loadedUser = $this->userService->getById($user);

        return $this->successResponse(
            new UserResource($loadedUser),
            __('messages.user_retrieved')
        );
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $updated = $this->userService->update($user, $request->validated());

        return $this->successResponse(
            new UserResource($updated),
            __('messages.user_updated')
        );
    }

    /**
     * Remove the specified user (soft delete).
     */
    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return $this->successResponse(
            null,
            __('messages.user_deleted')
        );
    }

    /**
     * Display a listing of soft-deleted users.
     */
    public function trashed(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $users = $this->userService->getTrashed($request->query(), $perPage);

        return $this->successResponse(
            UserResource::collection($users),
            __('messages.deleted_users_list'),
            200,
            [
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'per_page'     => $users->perPage(),
                    'total'        => $users->total(),
                    'last_page'    => $users->lastPage(),
                ],
            ]
        );
    }

    /**
     * Restore the specified soft-deleted user.
     */
    public function restore(int|string $id): JsonResponse
    {
        $user = $this->userService->restore($id);

        return $this->successResponse(
            new UserResource($user),
            __('messages.user_restored')
        );
    }
}
