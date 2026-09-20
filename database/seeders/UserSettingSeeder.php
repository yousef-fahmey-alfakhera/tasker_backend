<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;

class UserSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $themeSetting = Setting::where('name', 'theme_mode')->first();
        $lightModeSetting = Setting::where('name', 'light_mode')->first();

        foreach ($users as $user) {
            if ($themeSetting) {
                UserSetting::updateOrCreate(
                    [
                        'user_id'    => $user->id,
                        'setting_id' => $themeSetting->id,
                    ],
                    [
                        'value' => 'light',
                    ]
                );
            }

            if ($lightModeSetting) {
                UserSetting::updateOrCreate(
                    [
                        'user_id'    => $user->id,
                        'setting_id' => $lightModeSetting->id,
                    ],
                    [
                        'value' => 'true',
                    ]
                );
            }
        }
    }
}
