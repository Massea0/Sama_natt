<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\RoundResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Siak\Tontine\Service\Guild\RoundService;
use Siak\Tontine\Service\TenantService;

class RoundController extends Controller
{
    public function __construct(
        private TenantService $tenantService,
        private RoundService $roundService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $guild = $this->tenantService->guild();
        if (!$guild) {
            return $this->notFound('Tontine non trouvée');
        }

        $page = (int) $request->query('page', 1);
        $rounds = $this->roundService->getRounds($guild, $page);
        $total = $this->roundService->getRoundCount($guild);

        return $this->successWithMeta(
            RoundResource::collection($rounds),
            [
                'total' => $total,
                'page' => $page,
            ]
        );
    }

    public function show(): JsonResponse
    {
        $round = $this->tenantService->round();
        if (!$round) {
            return $this->notFound('Round non trouvé');
        }

        return $this->success(new RoundResource($round));
    }
}
