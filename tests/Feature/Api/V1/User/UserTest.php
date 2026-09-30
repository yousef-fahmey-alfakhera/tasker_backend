<?php

namespace Tests\Feature\Api\V1\User;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);

        $this->project = Project::create([
            'name' => 'IT Operations',
        ]);

        $this->adminUser = User::factory()->create([
            'project_id' => $this->project->id,
        ]);
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create([
            'project_id' => $this->project->id,
        ]);
        $this->regularUser->assignRole('user');
    }

    public function test_guest_cannot_access_users_endpoints(): void
    {
        $this->getJson('/api/users')->assertStatus(401);
        $this->getJson('/api/users/trashed')->assertStatus(401);
    }

    public function test_admin_can_list_users_with_pagination(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/users?per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Users retrieved successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'code',
                        'project_id',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
            ]);
    }

    public function test_admin_can_create_user_with_optional_code(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users', [
                'name'       => 'Test Staff',
                'email'      => 'staff@test.com',
                'password'   => 'Password123!',
                'code'       => 'EMP-200',
                'project_id' => $this->project->id,
                'role'       => 'user',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully.',
                'data'    => [
                    'name'       => 'Test Staff',
                    'email'      => 'staff@test.com',
                    'code'       => 'EMP-200',
                    'project_id' => $this->project->id,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'staff@test.com',
            'code'  => 'EMP-200',
        ]);
    }

    public function test_user_creation_without_code_succeeds_as_code_is_nullable(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users', [
                'name'       => 'No Code User',
                'email'      => 'nocode@test.com',
                'password'   => 'Password123!',
                'project_id' => $this->project->id,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'nocode@test.com',
            'code'  => null,
        ]);
    }

    public function test_user_creation_fails_without_project_id_with_custom_message(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users', [
                'name'     => 'Missing Project User',
                'email'    => 'missing_project@test.com',
                'password' => 'Password123!',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'project_id' => 'برجاء اختيار القسم',
            ]);
    }

    public function test_admin_can_show_and_update_user(): void
    {
        $targetUser = User::factory()->create([
            'project_id' => $this->project->id,
            'code'       => 'EMP-OLD',
        ]);

        $showResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/users/' . $targetUser->id);

        $showResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'id'   => $targetUser->id,
                    'code' => 'EMP-OLD',
                ],
            ]);

        $updateResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson('/api/users/' . $targetUser->id, [
                'name' => 'Updated Staff Name',
                'code' => 'EMP-NEW',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'name' => 'Updated Staff Name',
                    'code' => 'EMP-NEW',
                ],
            ]);
    }

    public function test_admin_can_soft_delete_and_restore_user(): void
    {
        $targetUser = User::factory()->create([
            'project_id' => $this->project->id,
            'code'       => 'EMP-DEL',
        ]);

        // 1. Soft delete
        $deleteResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson('/api/users/' . $targetUser->id);

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User deleted successfully.',
            ]);

        $this->assertNull(User::find($targetUser->id));
        $this->assertNotNull(User::withTrashed()->find($targetUser->id)->deleted_at);

        // 2. Trashed list
        $trashedResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/users/trashed');

        $trashedResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Deleted users retrieved successfully.',
            ]);

        $trashedIds = collect($trashedResponse->json('data'))->pluck('id');
        $this->assertTrue($trashedIds->contains($targetUser->id));

        // 3. Restore
        $restoreResponse = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users/' . $targetUser->id . '/restore');

        $restoreResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User restored successfully.',
            ]);

        $this->assertNotNull(User::find($targetUser->id));
        $this->assertNull(User::find($targetUser->id)->deleted_at);
    }
}
