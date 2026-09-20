<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TaskType;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first();
        $user = User::where('email', 'user@user.com')->first();
        $project = Project::first();
        $workspace = Workspace::first();
        $todoStatus = TaskStatus::where('stage', 'pending')->first();
        $inProgressStatus = TaskStatus::where('stage', 'working')->first();
        $doneStatus = TaskStatus::where('stage', 'completed')->first();

        $networkType = TaskType::where('type', 'network')->first();
        $deviceType = TaskType::where('type', 'device')->first();
        $focusType = TaskType::where('type', 'focus')->first();
        $otherType = TaskType::where('type', 'other')->first();

        if (!$project || !$workspace || !$admin || !$todoStatus) {
            return;
        }

        $tasks = [
            [
                'title'        => 'Setup Authentication and API Tokens',
                'description'  => 'Implement Sanctum token authentication and role checking.',
                'priority'     => 'Urgent',
                'status_id'    => $doneStatus?->id ?? $todoStatus->id,
                'task_type_id' => $networkType?->id,
                'position'     => 1,
                'fixed_by'     => $admin->id,
            ],
            [
                'title'        => 'Implement Polymorphic Attachments Module',
                'description'  => 'Build FileService and Attachments CRUD with direct URL support.',
                'priority'     => 'High',
                'status_id'    => $doneStatus?->id ?? $todoStatus->id,
                'task_type_id' => $deviceType?->id,
                'position'     => 2,
                'fixed_by'     => $user?->id ?? $admin->id,
            ],
            [
                'title'        => 'Create Settings & Light Mode Preference',
                'description'  => 'Allow users to configure light/dark mode and system preferences.',
                'priority'     => 'Normal',
                'status_id'    => $inProgressStatus?->id ?? $todoStatus->id,
                'task_type_id' => $focusType?->id,
                'position'     => 3,
                'fixed_by'     => $user?->id ?? $admin->id,
            ],
            [
                'title'        => 'Seeders & Permission Guarding for All CRUDs',
                'description'  => 'Add Spatie permission checks across all API endpoints.',
                'priority'     => 'High',
                'status_id'    => $inProgressStatus?->id ?? $todoStatus->id,
                'task_type_id' => $otherType?->id,
                'position'     => 4,
                'fixed_by'     => $admin->id,
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::updateOrCreate(
                [
                    'project_id'   => $project->id,
                    'workspace_id' => $workspace->id,
                    'title'        => $taskData['title'],
                ],
                array_merge($taskData, [
                    'project_id'   => $project->id,
                    'workspace_id' => $workspace->id,
                    'created_by'   => $admin->id,
                ])
            );
        }
    }
}
