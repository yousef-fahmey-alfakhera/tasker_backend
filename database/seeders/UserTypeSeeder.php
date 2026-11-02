<?php

namespace Database\Seeders;

use App\Models\TaskType;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@admin.com')->first();
        if ($admin) {
            $taskTypeIds = TaskType::where('project_id', $admin->project_id)
                ->orWhereNull('project_id')
                ->pluck('id')
                ->all();

            if (empty($taskTypeIds)) {
                $taskTypeIds = TaskType::pluck('id')->take(3)->all();
            }

            UserType::updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'type'        => 'it',
                    'respnsapity' => !empty($taskTypeIds) ? $taskTypeIds : ['focus', 'device', 'network'],
                ]
            );
        }

        $user = User::where('email', 'user@user.com')->first();
        if ($user) {
            $userTaskTypeIds = TaskType::where('project_id', $user->project_id)
                ->pluck('id')
                ->take(1)
                ->all();

            if (empty($userTaskTypeIds)) {
                $userTaskTypeIds = TaskType::pluck('id')->take(1)->all();
            }

            UserType::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'type'        => 'normal',
                    'respnsapity' => !empty($userTaskTypeIds) ? $userTaskTypeIds : ['device'],
                ]
            );
        }
    }
}
