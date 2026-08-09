<?php

namespace App\Http\Resources;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Room
 */
class RoomStateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $participants = $this->participants()->orderBy('joined_at')->get();
        $files = $this->files()->active()->orderByDesc('uploaded_at')->get();

        return [
            'status' => $this->status->value,
            'expires_at' => $this->expires_at->toIso8601String(),
            'seconds_remaining' => $this->expires_at->isFuture() ? (int) now()->diffInSeconds($this->expires_at) : 0,
            'allow_participant_uploads' => $this->allow_participant_uploads,
            'participant_count' => $participants->count(),
            'participants' => ParticipantResource::collection($participants),
            'files' => SharedFileResource::collection($files),
        ];
    }
}
