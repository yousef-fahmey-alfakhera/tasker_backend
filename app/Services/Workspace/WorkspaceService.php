<?php

namespace App\Services\Workspace;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Collection;

class WorkspaceService
{
    /**
     * Get all workspaces.
     */
    public function getAll(): Collection
    {
        return Workspace::with(['project', 'creator', 'users'])->withCount('tasks')->latest()->get();
    }

    /**
     * Get workspace details.
     */
    public function getById(Workspace $workspace): Workspace
    {
        return $workspace->loadMissing(['project', 'creator', 'users'])->loadCount('tasks');
    }

    /**
     * Create workspace and attach users.
     */
    public function create(array $data, int $userId): Workspace
    {
        $usersData = $data['users'] ?? [];
        unset($data['users']);

        $data['created_by'] = $userId;
        $workspace = Workspace::create($data);

        // Always attach creator as 'creator' role
        $syncData = [$userId => ['role' => 'creator']];

        foreach ($usersData as $item) {
            $syncData[$item['user_id']] = ['role' => $item['role']];
        }

        $workspace->users()->sync($syncData);

        return $workspace->loadMissing(['project', 'creator', 'users']);
    }

    /**
     * Update workspace and optionally sync users.
     */
    public function update(Workspace $workspace, array $data): Workspace
    {
        if (array_key_exists('users', $data)) {
            $usersData = $data['users'] ?? [];
            unset($data['users']);

            $syncData = [];
            foreach ($usersData as $item) {
                $syncData[$item['user_id']] = ['role' => $item['role']];
            }
            $workspace->users()->sync($syncData);
        }

        $workspace->update($data);

        return $workspace->fresh(['project', 'creator', 'users'])->loadCount('tasks');
    }

    /**
     * Delete workspace.
     */
    public function delete(Workspace $workspace): bool
    {
        return (bool) $workspace->delete();
    }
}
