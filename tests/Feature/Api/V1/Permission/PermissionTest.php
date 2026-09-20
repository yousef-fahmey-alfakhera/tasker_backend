<?php

namespace Tests\Feature\Api\V1\Permission;

use App\Models\Permission;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected User $restrictedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $this->adminUser = User::where('email', 'admin@admin.com')->first();
        $this->regularUser = User::where('email', 'user@user.com')->first();
        $this->restrictedUser = User::factory()->create(); // No roles or permissions
    }

    public function test_guest_cannot_access_permissions(): void
    {
        $response = $this->getJson('/api/auth/permissions');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_retrieve_their_permissions(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/auth/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'guard_name',
                        'name_ar',
                    ],
                ],
            ]);

        $first = $response->json('data.0');
        $this->assertNotEmpty($first['name']);
        $this->assertEquals('sanctum', $first['guard_name']);
        $this->assertNotEmpty($first['name_ar']);
    }

    public function test_permissions_me_endpoint_returns_user_permissions(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/permissions/me');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_user_without_permission_receives_403_forbidden_on_guarded_endpoint(): void
    {
        // restrictedUser has no permissions
        $response = $this->actingAs($this->restrictedUser, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ]);
    }

    public function test_user_with_permission_can_access_guarded_endpoint(): void
    {
        $project = Project::create([
            'name'       => 'Guarded Project',
            'created_by' => $this->adminUser->id,
        ]);

        $workspace = Workspace::create([
            'project_id' => $project->id,
            'name'       => 'Guarded Workspace',
            'created_by' => $this->adminUser->id,
        ]);

        $status = TaskStatus::create([
            'name'  => 'Backlog',
            'stage' => 'pending',
            'order' => 1,
        ]);

        // adminUser has show_tasks permission
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(200);
    }

    public function test_arabic_bilingual_forbidden_response_when_lacking_permission(): void
    {
        $response = $this->actingAs($this->restrictedUser, 'sanctum')
            ->withHeader('Accept-Language', 'ar')
            ->getJson('/api/tasks');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'ليس لديك الصلاحية للقيام بهذا الإجراء.',
            ]);
    }
}
