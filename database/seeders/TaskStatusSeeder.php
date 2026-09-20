<?php

namespace Database\Seeders;

use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class TaskStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'To Do', 'stage' => 'pending', 'order' => 1],
            ['name' => 'In Progress', 'stage' => 'working', 'order' => 2],
            ['name' => 'Review', 'stage' => 'working', 'order' => 3],
            ['name' => 'Done', 'stage' => 'completed', 'order' => 4],
            ['name' => 'Cancelled', 'stage' => 'completed', 'order' => 5],
        ];

        foreach ($statuses as $status) {
            TaskStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }
}
