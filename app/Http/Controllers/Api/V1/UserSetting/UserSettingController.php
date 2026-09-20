<?php

namespace App\Http\Controllers\Api\V1\UserSetting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UserSetting\SetThemeRequest;
use App\Http\Requests\Api\V1\UserSetting\StoreUserSettingRequest;
use App\Http\Requests\Api\V1\UserSetting\UpdateUserSettingRequest;
use App\Http\Resources\Api\V1\UserSetting\UserSettingResource;
use App\Models\UserSetting;
use App\Services\UserSetting\UserSettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserSettingController extends Controller implements HasMiddleware
{
    use ApiResponse;

    public function __construct(
        protected UserSettingService $userSettingService
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:show_user_settings', only: ['index', 'show', 'getTheme']),
            new Middleware('permission:create_user_settings', only: ['store', 'setTheme']),
            new Middleware('permission:update_user_settings', only: ['update']),
            new Middleware('permission:delete_user_settings', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of current user's settings.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $userSettings = $this->userSettingService->getAll($userId, $request->query());

        return $this->successResponse(
            UserSettingResource::collection($userSettings),
            __('messages.user_setting_list')
        );
    }

    /**
     * Store or update a user setting.
     */
    public function store(StoreUserSettingRequest $request): JsonResponse
    {
        $userSetting = $this->userSettingService->create(
            $request->validated(),
            $request->user()->id
        );

        return $this->successResponse(
            new UserSettingResource($userSetting),
            __('messages.user_setting_created'),
            201
        );
    }

    /**
     * Display the specified user setting.
     */
    public function show(UserSetting $userSetting): JsonResponse
    {
        $loaded = $this->userSettingService->getById($userSetting);

        return $this->successResponse(
            new UserSettingResource($loaded),
            __('messages.user_setting_retrieved')
        );
    }

    /**
     * Update the specified user setting.
     */
    public function update(UpdateUserSettingRequest $request, UserSetting $userSetting): JsonResponse
    {
        $updated = $this->userSettingService->update($userSetting, $request->validated());

        return $this->successResponse(
            new UserSettingResource($updated),
            __('messages.user_setting_updated')
        );
    }

    /**
     * Remove the specified user setting (reset to default).
     */
    public function destroy(UserSetting $userSetting): JsonResponse
    {
        $this->userSettingService->delete($userSetting);

        return $this->successResponse(
            null,
            __('messages.user_setting_deleted')
        );
    }

    /**
     * Get user active theme / light mode.
     */
    public function getTheme(Request $request): JsonResponse
    {
        $theme = $this->userSettingService->getTheme($request->user()->id);

        return $this->successResponse(
            $theme,
            __('messages.theme_retrieved')
        );
    }

    /**
     * Set user theme mode (light or dark).
     */
    public function setTheme(SetThemeRequest $request): JsonResponse
    {
        $theme = $this->userSettingService->setTheme($request->user()->id, $request->input('theme'));

        return $this->successResponse(
            $theme,
            __('messages.theme_updated')
        );
    }
}
