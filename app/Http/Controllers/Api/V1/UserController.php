<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\GuildResource;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Siak\Tontine\Service\Guild\GuildService;

class UserController extends Controller
{
    public function __construct(
        private GuildService $guildService
    ) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
        ]);

        $user = $request->user();
        $user->update($validated);

        return $this->success(new UserResource($user->fresh()));
    }

    public function guilds(Request $request): JsonResponse
    {
        $user = $request->user();
        $page = (int) $request->query('page', 1);

        $guilds = $this->guildService->getGuilds($user, $page);
        $guestGuilds = $this->guildService->getGuestGuilds($user, $page);

        $allGuilds = $guilds->merge($guestGuilds);

        return $this->successWithMeta(
            GuildResource::collection($allGuilds),
            [
                'total' => $this->guildService->getGuildCount($user) +
                    $this->guildService->getGuestGuildCount($user),
                'page' => $page,
            ]
        );
    }
}
