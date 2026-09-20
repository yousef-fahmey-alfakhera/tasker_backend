<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            UserTypeSeeder::class,
            TaskStatusSeeder::class,
            TaskTypeSeeder::class,
            ProjectSeeder::class,
            WorkspaceSeeder::class,
            TaskSeeder::class,
            AttachmentSeeder::class,
            SettingSeeder::class,
            UserSettingSeeder::class,
        ]);
    }
}
