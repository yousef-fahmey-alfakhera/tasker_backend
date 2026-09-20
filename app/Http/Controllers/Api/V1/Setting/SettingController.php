<?php

namespace App\Http\Controllers\Api\V1\Setting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Setting\StoreSettingRequest;
use App\Http\Requests\Api\V1\Setting\UpdateSettingRequest;
use App\Http\Resources\Api\V1\Setting\SettingResource;
use App\Models\Setting;
use App\Services\Setting\SettingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingController extends Controller implements HasMiddleware
{
    use ApiResponse;

    public function __construct(
        protected SettingService $settingService
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:show_settings', only: ['index', 'show']),
            new Middleware('permission:create_settings', only: ['store']),
            new Middleware('permission:update_settings', only: ['update']),
            new Middleware('permission:delete_settings', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of settings.
     */
    public function index(Request $request): JsonResponse
    {
        $settings = $this->settingService->getAll($request->query());

        return $this->successResponse(
            SettingResource::collection($settings),
            __('messages.setting_list')
        );
    }

    /**
     * Store a newly created setting.
     */
    public function store(StoreSettingRequest $request): JsonResponse
    {
        $setting = $this->settingService->create($request->validated());

        return $this->successResponse(
            new SettingResource($setting),
            __('messages.setting_created'),
            201
        );
    }

    /**
     * Display the specified setting.
     */
    public function show(Setting $setting): JsonResponse
    {
        $loadedSetting = $this->settingService->getById($setting);

        return $this->successResponse(
            new SettingResource($loadedSetting),
            __('messages.setting_retrieved')
        );
    }

    /**
     * Update the specified setting.
     */
    public function update(UpdateSettingRequest $request, Setting $setting): JsonResponse
    {
        $updated = $this->settingService->update($setting, $request->validated());

        return $this->successResponse(
            new SettingResource($updated),
            __('messages.setting_updated')
        );
    }

    /**
     * Remove the specified setting.
     */
    public function destroy(Setting $setting): JsonResponse
    {
        $this->settingService->delete($setting);

        return $this->successResponse(
            null,
            __('messages.setting_deleted')
        );
    }
}
