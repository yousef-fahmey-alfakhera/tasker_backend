<?php

namespace Tests\Feature\Api\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $payload = [
            'name'                  => 'John Tasker',
            'email'                 => 'john@tasker.test',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson('/api/public/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Account registered successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'token' => [
                        'type',
                        'access_token',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@tasker.test',
        ]);
    }

    public function test_registration_validation_fails_for_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicate@tasker.test',
        ]);

        $response = $this->postJson('/api/public/auth/register', [
            'name'                  => 'Another User',
            'email'                 => 'duplicate@tasker.test',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'user@tasker.test',
            'password' => bcrypt('Secret123!'),
        ]);

        $response = $this->postJson('/api/public/auth/login', [
            'email'    => 'user@tasker.test',
            'password' => 'Secret123!',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged in successfully.',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'token' => ['access_token'],
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email'    => 'user@tasker.test',
            'password' => bcrypt('Secret123!'),
        ]);

        $response = $this->postJson('/api/public/auth/login', [
            'email'    => 'user@tasker.test',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_bilingual_response_with_arabic_header(): void
    {
        $payload = [
            'name'                  => 'علي أحمد',
            'email'                 => 'ali@tasker.test',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->withHeader('Accept-Language', 'ar')
            ->postJson('/api/public/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح.',
            ]);
    }
}
