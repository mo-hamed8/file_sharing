<?php

namespace App\Http\Resources;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Room
 */
class RoomResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status->value,
            'expires_at' => $this->expires_at->toIso8601String(),
            'seconds_remaining' => $this->expires_at->isFuture() ? (int) now()->diffInSeconds($this->expires_at) : 0,
            'allow_participant_uploads' => $this->allow_participant_uploads,
            'maximum_file_size' => $this->maximum_file_size,
            'maximum_files' => $this->maximum_files,
            'total_storage_limit' => $this->total_storage_limit,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
