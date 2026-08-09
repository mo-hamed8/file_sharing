<?php

namespace App\Http\Resources;

use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Participant
 */
class ParticipantResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'display_name' => $this->display_name,
            'role' => $this->role->value,
            'joined_at' => $this->joined_at->toIso8601String(),
            'last_seen_at' => $this->last_seen_at?->toIso8601String(),
        ];
    }
}
