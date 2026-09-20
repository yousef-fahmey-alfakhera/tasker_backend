<?php

namespace Database\Seeders;

use App\Models\TaskType;
use Illuminate\Database\Seeder;

class TaskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Network Issue',
                'type' => 'network',
            ],
            [
                'name' => 'Device Maintenance',
                'type' => 'device',
            ],
            [
                'name' => 'Focus Work',
                'type' => 'focus',
            ],
            [
                'name' => 'General Tasks',
                'type' => 'other',
            ],
        ];

        foreach ($types as $type) {
            TaskType::firstOrCreate(
                ['type' => $type['type']],
                ['name' => $type['name']]
            );
        }
    }
}
