<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Siak\Tontine\Model\Session;

class SessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'abbrev' => $this->abbrev,
            'status' => $this->getStatusLabel(),
            'status_code' => $this->status,
            'day_date' => $this->day_date?->toDateString(),
            'start_time' => $this->start_time?->format('H:i'),
            'end_time' => $this->end_time?->format('H:i'),
            'venue' => $this->venue,
            'agenda' => $this->agenda,
            'notes' => $this->notes,
            'is_pending' => $this->pending,
            'is_opened' => $this->opened,
            'is_closed' => $this->closed,
            'is_active' => $this->active,
        ];
    }

    private function getStatusLabel(): string
    {
        return match ($this->status) {
            Session::STATUS_PENDING => 'pending',
            Session::STATUS_OPENED => 'opened',
            Session::STATUS_CLOSED => 'closed',
            default => 'unknown',
        };
    }
}
