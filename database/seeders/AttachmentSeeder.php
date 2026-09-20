<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first() ?? User::first();
        $task = Task::first();

        if (!$task || !$admin) {
            return;
        }

        Attachment::updateOrCreate(
            [
                'attachable_type' => Task::class,
                'attachable_id'   => $task->id,
                'file'            => 'system_architecture.png',
            ],
            [
                'path'       => Task::ATTACHMENT_PATH . '/demo_architecture.png',
                'type'       => 'image/png',
                'size'       => 102400,
                'created_by' => $admin->id,
            ]
        );
    }
}
