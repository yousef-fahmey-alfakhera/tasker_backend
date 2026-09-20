<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first();

        $projects = [
            [
                'name'        => 'Tasker Web Application',
                'description' => 'Core development of the ClickUp-like task management platform.',
                'created_by'  => $admin?->id,
            ],
            [
                'name'        => 'Mobile Companion App',
                'description' => 'Cross-platform mobile client for iOS and Android devices.',
                'created_by'  => $admin?->id,
            ],
            [
                'name'        => 'DevOps & Cloud Infrastructure',
                'description' => 'Kubernetes, CI/CD automation, and cloud deployments.',
                'created_by'  => $admin?->id,
            ],
        ];

        foreach ($projects as $project) {
            Project::updateOrCreate(
                ['name' => $project['name']],
                $project
            );
        }
    }
}
