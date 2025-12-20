<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Siak\Tontine\Model\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ========== LOGIN ==========

    public function test_login_returns_token_and_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email'],
                ],
            ])
            ->assertJsonPath('data.user.email', 'test@example.com');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPhone 15',
        ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'invalid_credentials');
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unknown@example.com',
            'password' => 'password123',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'invalid_credentials');
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }

    // ========== LOGOUT ==========

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('iPhone 15')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('data.message', 'Déconnexion réussie');

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPhone 15',
        ]);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    public function test_logout_only_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('iPhone 15')->plainTextToken;
        $user->createToken('iPad Pro');

        $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/v1/auth/logout');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPad Pro',
        ]);
    }

    // ========== ME ==========

    public function test_me_works_with_bearer_token(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $token = $user->createToken('iPhone 15')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Test User');
    }
}
