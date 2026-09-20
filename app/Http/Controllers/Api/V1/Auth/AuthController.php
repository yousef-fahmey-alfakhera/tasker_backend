<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Resources\Api\V1\Auth\AuthResource;
use App\Http\Resources\Api\V1\Permission\PermissionResource;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse(
            new AuthResource($result['user'], $result['token']),
            __('messages.registered_successfully'),
            201
        );
    }

    /**
     * Authenticate user and return token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->successResponse(
            new AuthResource($result['user'], $result['token']),
            __('messages.logged_in_successfully')
        );
    }

    /**
     * Log out authenticated user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse(
            null,
            __('messages.logged_out_successfully')
        );
    }

    /**
     * Get authenticated user's permissions.
     */
    public function permissions(Request $request): JsonResponse
    {
        $permissions = $this->authService->getUserPermissions($request->user());

        return $this->successResponse(
            PermissionResource::collection($permissions),
            __('messages.permissions_retrieved')
        );
    }
}
