<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first();
        $user = User::where('email', 'user@user.com')->first();
        $project = Project::first();

        if (!$project || !$admin) {
            return;
        }

        $workspaces = [
            [
                'project_id' => $project->id,
                'name'       => 'Sprint 1 - Backend Foundation',
                'created_by' => $admin->id,
            ],
            [
                'project_id' => $project->id,
                'name'       => 'Sprint 2 - UI & Dashboards',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($workspaces as $workspaceData) {
            $workspace = Workspace::updateOrCreate(
                [
                    'project_id' => $workspaceData['project_id'],
                    'name'       => $workspaceData['name'],
                ],
                $workspaceData
            );

            // Attach users with roles
            $workspace->users()->syncWithoutDetaching([
                $admin->id => ['role' => 'admin'],
            ]);

            if ($user) {
                $workspace->users()->syncWithoutDetaching([
                    $user->id => ['role' => 'editor'],
                ]);
            }
        }
    }
}
