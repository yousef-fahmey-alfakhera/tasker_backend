<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated active users with optional filters.
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with(['project', 'userType', 'roles'])
            ->withCount(['createdTasks', 'fixedTasks'])
            ->latest('id');

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated soft-deleted users.
     */
    public function getTrashed(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::onlyTrashed()
            ->with(['project', 'userType', 'roles'])
            ->withCount(['createdTasks', 'fixedTasks'])
            ->latest('deleted_at');

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get user by instance with relations loaded.
     */
    public function getById(User $user): User
    {
        return $user->loadMissing(['project', 'userType', 'roles'])
            ->loadCount(['createdTasks', 'fixedTasks']);
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        $role = $data['role'] ?? null;
        unset($data['role']);

        $data['password'] = Hash::make($data['password']);
        if (isset($data['code']) && empty($data['code'])) {
            $data['code'] = null;
        }

        $user = User::create($data);

        if ($role) {
            $user->assignRole($role);
        }

        return $user->loadMissing(['project', 'userType', 'roles']);
    }

    /**
     * Update an existing user.
     */
    public function update(User $user, array $data): User
    {
        $role = $data['role'] ?? null;
        unset($data['role']);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if (array_key_exists('code', $data) && empty($data['code'])) {
            $data['code'] = null;
        }

        $user->update($data);

        if ($role) {
            $user->syncRoles([$role]);
        }

        return $user->fresh(['project', 'userType', 'roles'])
            ->loadCount(['createdTasks', 'fixedTasks']);
    }

    /**
     * Soft delete a user.
     */
    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(int|string $id): User
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return $user->loadMissing(['project', 'userType', 'roles'])
            ->loadCount(['createdTasks', 'fixedTasks']);
    }
}
