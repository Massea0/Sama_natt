<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Siak\Tontine\Model\Round;

class RoundResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->getStatusLabel(),
            'status_code' => $this->status,
            'notes' => $this->notes,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_pending' => $this->pending,
            'is_opened' => $this->opened,
            'is_closed' => $this->closed,
        ];
    }

    private function getStatusLabel(): string
    {
        return match ($this->status) {
            Round::STATUS_PENDING => 'pending',
            Round::STATUS_OPENED => 'opened',
            Round::STATUS_CLOSED => 'closed',
            default => 'unknown',
        };
    }
}
