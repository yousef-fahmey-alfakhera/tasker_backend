<?php

namespace Database\Seeders;

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
            UserType::updateOrCreate(
                ['user_id' => $admin->id],
                [
                    'type'        => 'it',
                    'respnsapity' => ['focus', 'device', 'network'],
                ]
            );
        }

        $user = User::where('email', 'user@user.com')->first();
        if ($user) {
            UserType::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'type'        => 'normal',
                    'respnsapity' => ['device'],
                ]
            );
        }
    }
}
