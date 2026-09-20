<?php

namespace Tests\Feature\Api\V1\Setting;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $restrictedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->adminUser = User::where('email', 'admin@admin.com')->first();
        $this->restrictedUser = User::factory()->create();
    }

    public function test_guest_cannot_access_settings(): void
    {
        $response = $this->getJson('/api/settings');
        $response->assertStatus(401);
    }

    public function test_unauthorized_user_cannot_access_settings(): void
    {
        $response = $this->actingAs($this->restrictedUser, 'sanctum')
            ->getJson('/api/settings');

        $response->assertStatus(403);
    }

    public function test_admin_can_list_settings(): void
    {
        Setting::create([
            'name'    => 'custom_timeout',
            'type'    => 'num',
            'default' => '60',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'type', 'default'],
                ],
            ]);
    }

    public function test_admin_can_create_setting_with_type_and_default(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/settings', [
                'name'        => 'theme_mode',
                'type'        => 'string',
                'default'     => 'light',
                'description' => 'Default system theme',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'name'    => 'theme_mode',
                    'type'    => 'string',
                    'default' => 'light',
                ],
            ]);

        $this->assertDatabaseHas('settings', [
            'name'    => 'theme_mode',
            'type'    => 'string',
            'default' => 'light',
        ]);
    }

    public function test_setting_creation_validates_type(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/settings', [
                'name' => 'invalid_setting',
                'type' => 'invalid_type', // Must be bool, string, num
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_admin_can_show_and_update_setting(): void
    {
        $setting = Setting::create([
            'name'    => 'light_mode',
            'type'    => 'bool',
            'default' => 'true',
        ]);

        $showResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/settings/' . $setting->id);

        $showResponse->assertStatus(200)
            ->assertJson(['data' => ['name' => 'light_mode']]);

        $updateResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson('/api/settings/' . $setting->id, [
                'default' => 'false',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJson(['data' => ['default' => 'false']]);
    }

    public function test_admin_can_delete_setting(): void
    {
        $setting = Setting::create([
            'name'    => 'delete_me',
            'type'    => 'string',
            'default' => 'val',
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson('/api/settings/' . $setting->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted('settings', ['id' => $setting->id]);
    }
}
