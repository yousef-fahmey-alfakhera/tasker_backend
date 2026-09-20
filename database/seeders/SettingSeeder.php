<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'name'        => 'theme_mode',
                'type'        => 'string',
                'default'     => 'light',
                'description' => 'Active UI theme mode (light or dark)',
            ],
            [
                'name'        => 'light_mode',
                'type'        => 'bool',
                'default'     => 'true',
                'description' => 'Flag indicating if light mode is enabled',
            ],
            [
                'name'        => 'notifications_enabled',
                'type'        => 'bool',
                'default'     => 'true',
                'description' => 'Enable or disable application notifications',
            ],
            [
                'name'        => 'items_per_page',
                'type'        => 'num',
                'default'     => '25',
                'description' => 'Default pagination item limit per page',
            ],
            [
                'name'        => 'language',
                'type'        => 'string',
                'default'     => 'en',
                'description' => 'Default display language (en or ar)',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['name' => $setting['name']],
                $setting
            );
        }
    }
}
