<?php

namespace App\Services\UserSetting;

use App\Models\Setting;
use App\Models\UserSetting;
use Illuminate\Database\Eloquent\Collection;

class UserSettingService
{
    /**
     * Get all user settings for a user.
     */
    public function getAll(int $userId, array $filters = []): Collection
    {
        $query = UserSetting::with('setting')
            ->where('user_id', $userId);

        if (!empty($filters['setting_id'])) {
            $query->where('setting_id', $filters['setting_id']);
        }

        return $query->get();
    }

    /**
     * Get single user setting.
     */
    public function getById(UserSetting $userSetting): UserSetting
    {
        return $userSetting->loadMissing('setting');
    }

    /**
     * Create or update a user setting.
     */
    public function create(array $data, int $defaultUserId): UserSetting
    {
        $userId = $data['user_id'] ?? $defaultUserId;

        $userSetting = UserSetting::updateOrCreate(
            [
                'user_id'    => $userId,
                'setting_id' => $data['setting_id'],
            ],
            [
                'value' => (string) $data['value'],
            ]
        );

        return $userSetting->loadMissing('setting');
    }

    /**
     * Update an existing user setting.
     */
    public function update(UserSetting $userSetting, array $data): UserSetting
    {
        $userSetting->update([
            'value' => (string) $data['value'],
        ]);

        return $userSetting->fresh('setting');
    }

    /**
     * Delete / reset user setting.
     */
    public function delete(UserSetting $userSetting): bool
    {
        return (bool) $userSetting->delete();
    }

    /**
     * Get current theme / light mode status for a user.
     */
    public function getTheme(int $userId): array
    {
        $themeSetting = Setting::where('name', 'theme_mode')->first();
        $userSetting = $themeSetting
            ? UserSetting::where('user_id', $userId)->where('setting_id', $themeSetting->id)->first()
            : null;

        $activeTheme = $userSetting ? $userSetting->value : ($themeSetting?->default ?? 'light');

        return [
            'theme_mode' => $activeTheme,
            'is_light'   => $activeTheme === 'light',
            'is_dark'    => $activeTheme === 'dark',
        ];
    }

    /**
     * Set theme mode (light or dark) for a user.
     */
    public function setTheme(int $userId, string $theme): array
    {
        $themeSetting = Setting::firstOrCreate(
            ['name' => 'theme_mode'],
            [
                'type'        => 'string',
                'default'     => 'light',
                'description' => 'Active UI theme mode (light or dark)',
            ]
        );

        $lightModeSetting = Setting::firstOrCreate(
            ['name' => 'light_mode'],
            [
                'type'        => 'bool',
                'default'     => 'true',
                'description' => 'Flag indicating if light mode is enabled',
            ]
        );

        UserSetting::updateOrCreate(
            [
                'user_id'    => $userId,
                'setting_id' => $themeSetting->id,
            ],
            [
                'value' => $theme,
            ]
        );

        UserSetting::updateOrCreate(
            [
                'user_id'    => $userId,
                'setting_id' => $lightModeSetting->id,
            ],
            [
                'value' => $theme === 'light' ? 'true' : 'false',
            ]
        );

        return [
            'theme_mode' => $theme,
            'is_light'   => $theme === 'light',
            'is_dark'    => $theme === 'dark',
        ];
    }
}
