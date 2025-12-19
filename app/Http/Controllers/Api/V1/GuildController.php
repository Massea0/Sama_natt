<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\GuildResource;
use App\Http\Resources\Api\V1\MemberResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Siak\Tontine\Service\Guild\MemberService;
use Siak\Tontine\Service\TenantService;

class GuildController extends Controller
{
    public function __construct(
        private TenantService $tenantService,
        private MemberService $memberService
    ) {}

    public function show(): JsonResponse
    {
        $guild = $this->tenantService->guild();
        if (!$guild) {
            return $this->notFound('Tontine non trouvée');
        }

        return $this->success(new GuildResource($guild));
    }

    public function members(Request $request): JsonResponse
    {
        $guild = $this->tenantService->guild();
        if (!$guild) {
            return $this->notFound('Tontine non trouvée');
        }

        $search = $request->query('search', '');
        $page = (int) $request->query('page', 1);

        $members = $this->memberService->getMembers($guild, $search, true, $page);
        $total = $this->memberService->getMemberCount($guild, $search, true);

        return $this->successWithMeta(
            MemberResource::collection($members),
            [
                'total' => $total,
                'page' => $page,
            ]
        );
    }
}
