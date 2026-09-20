<?php

namespace App\Services\Setting;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

class SettingService
{
    /**
     * Get all settings with optional filters.
     */
    public function getAll(array $filters = []): Collection
    {
        $query = Setting::query()->orderBy('name');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->get();
    }

    /**
     * Get setting by instance.
     */
    public function getById(Setting $setting): Setting
    {
        return $setting;
    }

    /**
     * Create a new setting.
     */
    public function create(array $data): Setting
    {
        return Setting::create($data);
    }

    /**
     * Update an existing setting.
     */
    public function update(Setting $setting, array $data): Setting
    {
        $setting->update($data);

        return $setting->fresh();
    }

    /**
     * Delete a setting.
     */
    public function delete(Setting $setting): bool
    {
        return (bool) $setting->delete();
    }
}
