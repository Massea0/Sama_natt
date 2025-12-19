<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Siak\Tontine\Service\Guild\GuildService;
use Siak\Tontine\Service\TenantService;
use Symfony\Component\HttpFoundation\Response;

class ApiTenant
{
    public function __construct(
        private TenantService $tenantService,
        private GuildService $guildService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('unauthenticated', 'Non authentifié', 401);
        }

        $this->tenantService->setUser($user);

        // Guild from route param
        if ($guildId = $request->route('guild')) {
            $guild = $this->guildService->getUserOrGuestGuild($user, (int) $guildId);
            if (!$guild) {
                return $this->errorResponse('not_found', 'Tontine non trouvée', 404);
            }
            $this->tenantService->setGuild($guild);

            // Round from route param
            if ($roundId = $request->route('round')) {
                $round = $this->tenantService->getRound((int) $roundId);
                if (!$round) {
                    return $this->errorResponse('not_found', 'Round non trouvé', 404);
                }
                $this->tenantService->setRound($round);
            }
        }

        return $next($request);
    }

    private function errorResponse(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
            ]
        ], $status);
    }
}
