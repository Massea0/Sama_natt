<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Siak\Tontine\Model\Guild;
use Siak\Tontine\Model\User;
use Tests\TestCase;

class GuildTest extends TestCase
{
    use RefreshDatabase;

    public function test_guild_show_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/guilds/1');

        $response->assertStatus(401);
    }

    public function test_guild_show_returns_404_for_nonexistent_guild(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/guilds/999');

        $response->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_guild_show_returns_guild_details(): void
    {
        $user = User::factory()->create();
        $guild = $user->guilds()->create([
            'name' => 'Tontine Familiale',
            'shortname' => 'TF',
            'city' => 'Dakar',
            'country_code' => 'SN',
            'currency_code' => 'XOF',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/guilds/{$guild->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $guild->id)
            ->assertJsonPath('data.name', 'Tontine Familiale')
            ->assertJsonPath('data.city', 'Dakar')
            ->assertJsonPath('data.currency_code', 'XOF');
    }

    public function test_guild_show_returns_404_for_other_user_guild(): void
    {
        $owner = User::factory()->create();
        $guild = $owner->guilds()->create([
            'name' => 'Private Tontine',
            'shortname' => 'PT',
            'country_code' => 'SN',
            'currency_code' => 'XOF',
        ]);

        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $response = $this->getJson("/api/v1/guilds/{$guild->id}");

        $response->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_guild_members_returns_member_list(): void
    {
        $user = User::factory()->create();
        $guild = $user->guilds()->create([
            'name' => 'Test Guild',
            'shortname' => 'TG',
            'country_code' => 'SN',
            'currency_code' => 'XOF',
        ]);

        $guild->members()->create(['name' => 'Membre 1', 'active' => true]);
        $guild->members()->create(['name' => 'Membre 2', 'active' => true]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/guilds/{$guild->id}/members");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name'],
                ],
                'meta' => ['total', 'page'],
            ]);
    }
}
