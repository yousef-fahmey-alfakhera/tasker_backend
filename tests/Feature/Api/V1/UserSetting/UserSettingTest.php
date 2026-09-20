<?php

namespace Tests\Feature\Api\V1\UserSetting;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserSetting;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(SettingSeeder::class);

        $this->user = User::where('email', 'user@user.com')->first();
    }

    public function test_guest_cannot_access_user_settings(): void
    {
        $response = $this->getJson('/api/user-settings');
        $response->assertStatus(401);
    }

    public function test_user_can_list_their_settings(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/user-settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'user_id', 'setting_id', 'value', 'casted_value'],
                ],
            ]);
    }

    public function test_user_can_store_and_update_setting(): void
    {
        $setting = Setting::where('name', 'items_per_page')->first();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/user-settings', [
                'setting_id' => $setting->id,
                'value'      => '50',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'setting_id'   => $setting->id,
                    'value'        => '50',
                    'casted_value' => 50,
                ],
            ]);
    }

    public function test_user_can_get_active_theme_mode(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/user-settings/theme');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'theme_mode' => 'light',
                    'is_light'   => true,
                    'is_dark'    => false,
                ],
            ]);
    }

    public function test_user_can_set_theme_to_dark_and_light(): void
    {
        // Switch to dark mode
        $responseDark = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/user-settings/theme', [
                'theme' => 'dark',
            ]);

        $responseDark->assertStatus(200)
            ->assertJson([
                'data' => [
                    'theme_mode' => 'dark',
                    'is_light'   => false,
                    'is_dark'    => true,
                ],
            ]);

        // Switch back to light mode
        $responseLight = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/user-settings/theme', [
                'theme' => 'light',
            ]);

        $responseLight->assertStatus(200)
            ->assertJson([
                'data' => [
                    'theme_mode' => 'light',
                    'is_light'   => true,
                    'is_dark'    => false,
                ],
            ]);
    }

    public function test_user_can_delete_user_setting(): void
    {
        $setting = Setting::where('name', 'language')->first();

        $userSetting = UserSetting::create([
            'user_id'    => $this->user->id,
            'setting_id' => $setting->id,
            'value'      => 'ar',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/user-settings/' . $userSetting->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('user_settings', ['id' => $userSetting->id]);
    }
}
