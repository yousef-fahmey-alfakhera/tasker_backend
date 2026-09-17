<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\V1\Profile\ProfileResource;
use App\Services\Profile\ProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request): JsonResponse
    {
        $profile = $this->profileService->getProfile($request->user());

        return $this->successResponse(
            new ProfileResource($profile),
            __('messages.profile_retrieved')
        );
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $updatedUser = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return $this->successResponse(
            new ProfileResource($updatedUser),
            __('messages.profile_updated')
        );
    }
}
