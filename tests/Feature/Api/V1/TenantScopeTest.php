<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Siak\Tontine\Model\User;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_round_show_returns_404_for_round_from_other_guild(): void
    {
        $userA = User::factory()->create();
        $guildA = $userA->guilds()->create([
            'name' => 'Guild A',
            'shortname' => 'GA',
            'country_code' => 'SN',
            'currency_code' => 'XOF',
        ]);
        $userB = User::factory()->create();
        $guildB = $userB->guilds()->create([
            'name' => 'Guild B',
            'shortname' => 'GB',
            'country_code' => 'SN',
            'currency_code' => 'XOF',
        ]);
        $roundB = $guildB->rounds()->create([
            'title' => 'Round B',
            'status' => 0,
            'notes' => null,
        ]);

        Sanctum::actingAs($userA);

        $response = $this->getJson("/api/v1/guilds/{$guildA->id}/rounds/{$roundB->id}");

        $response->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }

    public function test_session_show_returns_404_for_session_from_other_round(): void
    {
        $userA = User::factory()->create();
        $guildA = $userA->guilds()->create([
            'name' => 'Guild A',
            'shortname' => 'GA',
            'country_code' => 'SN',
            'currency_code' => 'XOF',
        ]);
        $roundA = $guildA->rounds()->create([
            'title' => 'Round A',
            'status' => 0,
            'notes' => null,
        ]);

        $userB = User::factory()->create();
        $guildB = $userB->guilds()->create([
            'name' => 'Guild B',
            'shortname' => 'GB',
            'country_code' => 'SN',
            'currency_code' => 'XOF',
        ]);
        $roundB = $guildB->rounds()->create([
            'title' => 'Round B',
            'status' => 0,
            'notes' => null,
        ]);

        $sessionB = $roundB->sessions()->create([
            'title' => 'Session B1',
            'day_date' => now()->toDateString(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHour()->format('H:i:s'),
            'status' => 0,
            'notes' => null,
        ]);

        Sanctum::actingAs($userA);

        $response = $this->getJson("/api/v1/guilds/{$guildA->id}/rounds/{$roundA->id}/sessions/{$sessionB->id}");

        $response->assertStatus(404)
            ->assertJsonPath('error.code', 'not_found');
    }
}
