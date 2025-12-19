<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Siak\Tontine\Model\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Test User')
            ->assertJsonPath('data.email', 'test@example.com');
    }

    public function test_me_guilds_returns_user_guilds(): void
    {
        $user = User::factory()->create();
        $guild = $user->guilds()->create([
            'name' => 'Ma Tontine',
            'shortname' => 'MT',
            'country_code' => 'SN',
            'currency_code' => 'XOF',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me/guilds');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $guild->id)
            ->assertJsonPath('data.0.name', 'Ma Tontine')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'shortname', 'currency_code'],
                ],
                'meta' => ['total', 'page'],
            ]);
    }
}
