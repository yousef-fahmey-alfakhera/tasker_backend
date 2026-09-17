<?php

namespace Tests\Feature\Api\V1\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_retrieve_profile(): void
    {
        $user = User::factory()->create([
            'name'  => 'Sara Smith',
            'email' => 'sara@tasker.test',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile retrieved successfully.',
                'data' => [
                    'id'    => $user->id,
                    'name'  => 'Sara Smith',
                    'email' => 'sara@tasker.test',
                ],
            ]);
    }

    public function test_authenticated_user_can_update_profile_via_put(): void
    {
        $user = User::factory()->create([
            'name'  => 'Initial Name',
            'email' => 'initial@tasker.test',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'name'  => 'Updated Name',
                'email' => 'updated@tasker.test',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data' => [
                    'name'  => 'Updated Name',
                    'email' => 'updated@tasker.test',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Updated Name',
            'email' => 'updated@tasker.test',
        ]);
    }

    public function test_authenticated_user_can_update_profile_via_post(): void
    {
        $user = User::factory()->create([
            'name'  => 'Initial Name',
            'email' => 'initial@tasker.test',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/profile', [
                'name' => 'Post Updated Name',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Post Updated Name',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id'   => $user->id,
            'name' => 'Post Updated Name',
        ]);
    }

    public function test_profile_returns_arabic_message_with_accept_language_header(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('Accept-Language', 'ar')
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'تم استرجاع بيانات الملف الشخصي بنجاح.',
            ]);
    }
}
