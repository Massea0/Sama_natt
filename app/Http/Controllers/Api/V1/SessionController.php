<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\SessionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Siak\Tontine\Service\Guild\RoundService;
use Siak\Tontine\Service\Guild\SessionService;
use Siak\Tontine\Service\TenantService;

class SessionController extends Controller
{
    public function __construct(
        private TenantService $tenantService,
        private RoundService $roundService,
        private SessionService $sessionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $round = $this->tenantService->round();
        if (!$round) {
            return $this->notFound('Round non trouvé');
        }

        $page = (int) $request->query('page', 1);
        $sessions = $this->roundService->getSessions($round, $page);
        $total = $this->roundService->getSessionCount($round);

        return $this->successWithMeta(
            SessionResource::collection($sessions),
            [
                'total' => $total,
                'page' => $page,
            ]
        );
    }

    public function show(Request $request, int $guild, int $round, int $session): JsonResponse
    {
        $guildModel = $this->tenantService->guild();
        if (!$guildModel) {
            return $this->notFound('Tontine non trouvée');
        }

        $sessionModel = $this->sessionService->getSession($guildModel, $session);
        if (!$sessionModel) {
            return $this->notFound('Session non trouvée');
        }

        return $this->success(new SessionResource($sessionModel));
    }

    public function beneficiary(Request $request, int $guild, int $round, int $session): JsonResponse
    {
        $guildModel = $this->tenantService->guild();
        if (!$guildModel) {
            return $this->notFound('Tontine non trouvée');
        }

        $sessionModel = $this->sessionService->getSession($guildModel, $session);
        if (!$sessionModel) {
            return $this->notFound('Session non trouvée');
        }

        // Load payables with remitments for this session
        $beneficiaries = $sessionModel->payables()
            ->whereHas('remitment')
            ->with(['subscription.member.def', 'remitment'])
            ->get()
            ->map(fn($payable) => [
                'id' => $payable->id,
                'member' => [
                    'id' => $payable->subscription->member->id,
                    'name' => $payable->subscription->member->name,
                ],
                'paid' => $payable->remitment !== null,
            ]);

        return $this->success([
            'session' => new SessionResource($sessionModel),
            'beneficiaries' => $beneficiaries,
        ]);
    }
}
