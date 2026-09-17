<?php

namespace Tests\Feature\Api\V1\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthE2EConsoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Executes the end-to-end authentication lifecycle and outputs formatted JSON responses.
     */
    public function test_complete_auth_and_profile_flow_with_response_output(): void
    {
        fwrite(STDOUT, "\n=======================================================\n");
        fwrite(STDOUT, "  TASKER API - END-TO-END AUTHENTICATION & PROFILE TEST  \n");
        fwrite(STDOUT, "=======================================================\n\n");

        // 1. REGISTER
        fwrite(STDOUT, "▶ STEP 1: Calling [POST /api/public/auth/register] ...\n");
        $registerPayload = [
            'name'                  => 'Alex Morgan',
            'email'                 => 'alex.morgan@tasker.test',
            'password'              => 'SecretPassword123!',
            'password_confirmation' => 'SecretPassword123!',
        ];

        $registerResponse = $this->postJson('/api/public/auth/register', $registerPayload);
        $registerResponse->assertStatus(201);
        fwrite(STDOUT, "Response (Status " . $registerResponse->status() . "):\n");
        fwrite(STDOUT, json_encode($registerResponse->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n\n");

        // 2. LOGIN
        fwrite(STDOUT, "▶ STEP 2: Calling [POST /api/public/auth/login] ...\n");
        $loginPayload = [
            'email'    => 'alex.morgan@tasker.test',
            'password' => 'SecretPassword123!',
        ];

        $loginResponse = $this->postJson('/api/public/auth/login', $loginPayload);
        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token.access_token');
        $this->assertNotEmpty($token);

        fwrite(STDOUT, "Response (Status " . $loginResponse->status() . "):\n");
        fwrite(STDOUT, json_encode($loginResponse->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        fwrite(STDOUT, "Extracted Bearer Token: " . substr($token, 0, 15) . "...\n\n");

        // 3. GET PROFILE (with Token)
        fwrite(STDOUT, "▶ STEP 3: Calling [GET /api/profile] with Bearer Token ...\n");
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/profile');
        $profileResponse->assertStatus(200);

        fwrite(STDOUT, "Response (Status " . $profileResponse->status() . "):\n");
        fwrite(STDOUT, json_encode($profileResponse->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n\n");

        // 4. UPDATE PROFILE (PUT with Token)
        fwrite(STDOUT, "▶ STEP 4: Calling [PUT /api/profile] to update profile ...\n");
        $updateResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/profile', [
                'name' => 'Alex Morgan (Senior Manager)',
            ]);
        $updateResponse->assertStatus(200);

        fwrite(STDOUT, "Response (Status " . $updateResponse->status() . "):\n");
        fwrite(STDOUT, json_encode($updateResponse->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n\n");

        // 5. TEST ARABIC LOCALIZATION
        fwrite(STDOUT, "▶ STEP 5: Calling [GET /api/profile] with Accept-Language: ar ...\n");
        $arabicResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeader('Accept-Language', 'ar')
            ->getJson('/api/profile');
        $arabicResponse->assertStatus(200);

        fwrite(STDOUT, "Response (Status " . $arabicResponse->status() . "):\n");
        fwrite(STDOUT, json_encode($arabicResponse->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n\n");

        // 6. LOGOUT
        fwrite(STDOUT, "▶ STEP 6: Calling [POST /api/auth/logout] with Bearer Token ...\n");
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/auth/logout');
        $logoutResponse->assertStatus(200);

        fwrite(STDOUT, "Response (Status " . $logoutResponse->status() . "):\n");
        fwrite(STDOUT, json_encode($logoutResponse->json(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        fwrite(STDOUT, "=======================================================\n\n");
    }
}
