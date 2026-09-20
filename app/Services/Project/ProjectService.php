<?php

namespace App\Services\Project;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ProjectService
{
    /**
     * Get all projects (optionally filtered by assigned workspaces or creator).
     */
    public function getAll(?int $userId = null): Collection
    {
        $query = Project::with('creator')->withCount(['workspaces', 'tasks'])->latest();

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->whereHas('workspaces', function ($wq) use ($userId) {
                    $wq->whereHas('users', fn($uq) => $uq->where('users.id', $userId))
                       ->orWhere('created_by', $userId);
                })->orWhere('created_by', $userId);
            });
        }

        return $query->get();
    }

    /**
     * Get project by instance or ID.
     */
    public function getById(Project $project): Project
    {
        return $project->loadMissing('creator')->loadCount(['workspaces', 'tasks']);
    }

    /**
     * Create a new project.
     */
    public function create(array $data, int $userId): Project
    {
        $data['created_by'] = $userId;

        return Project::create($data)->loadMissing('creator');
    }

    /**
     * Update project.
     */
    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh(['creator'])->loadCount(['workspaces', 'tasks']);
    }

    /**
     * Delete project.
     */
    public function delete(Project $project): bool
    {
        return (bool) $project->delete();
    }
}
