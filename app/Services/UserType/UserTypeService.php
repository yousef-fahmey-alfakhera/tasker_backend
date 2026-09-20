<?php

namespace App\Services\UserType;

use App\Models\UserType;
use Illuminate\Database\Eloquent\Collection;

class UserTypeService
{
    /**
     * Get all user types.
     */
    public function getAll(): Collection
    {
        return UserType::with('user')->latest()->get();
    }

    /**
     * Get user type by instance.
     */
    public function getById(UserType $userType): UserType
    {
        return $userType->loadMissing('user');
    }

    /**
     * Create a user type.
     */
    public function create(array $data): UserType
    {
        return UserType::create($data)->loadMissing('user');
    }

    /**
     * Update user type.
     */
    public function update(UserType $userType, array $data): UserType
    {
        $userType->update($data);

        return $userType->fresh(['user']);
    }

    /**
     * Delete user type.
     */
    public function delete(UserType $userType): bool
    {
        return (bool) $userType->delete();
    }
}
