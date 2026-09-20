<?php

namespace App\Http\Controllers\Api\V1\Permission;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Permission\PermissionResource;
use App\Models\Permission;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use ApiResponse;

    /**
     * Display all available system permissions or auth user permissions.
     */
    public function index(Request $request): JsonResponse
    {
        // If query param ?mine=true or not admin, return user permissions; else return all
        if ($request->boolean('mine') || !$request->user()->hasRole('admin')) {
            $permissions = $request->user()->getAllPermissions();
        } else {
            $permissions = Permission::all();
        }

        return $this->successResponse(
            PermissionResource::collection($permissions),
            __('messages.permissions_retrieved')
        );
    }

    /**
     * Get authenticated user permissions specifically.
     */
    public function userPermissions(Request $request): JsonResponse
    {
        $permissions = $request->user()->getAllPermissions();

        return $this->successResponse(
            PermissionResource::collection($permissions),
            __('messages.permissions_retrieved')
        );
    }
}
