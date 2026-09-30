<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user and generate access token.
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'code'       => !empty($data['code']) ? $data['code'] : null,
            'project_id' => $data['project_id'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user->loadMissing('project'),
            'token' => $token,
        ];
    }

    /**
     * Authenticate user and issue access token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::where(function ($query) use ($credentials) {
            if (!empty($credentials['code'])) {
                $query->where('code', $credentials['code']);
            } elseif (!empty($credentials['email'])) {
                $query->where('email', $credentials['email'])
                      ->orWhere('code', $credentials['email']);
            }
        })->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('messages.invalid_credentials')],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user'  => $user->loadMissing('project'),
            'token' => $token,
        ];
    }

    /**
     * Revoke user's current access token.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /**
     * Get all direct and inherited permissions for user.
     */
    public function getUserPermissions(User $user): \Illuminate\Support\Collection
    {
        return $user->getAllPermissions();
    }
}
