<?php

namespace App\Http\Resources;

use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SharedFile
 */
class SharedFileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'original_name' => $this->original_name,
            'extension' => $this->extension,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'uploader_id' => $this->participant?->public_id,
            'uploader_name' => $this->participant?->display_name ?? 'Unknown',
            'uploaded_at' => $this->uploaded_at->toIso8601String(),
            'download_url' => route('rooms.files.download', ['room' => $this->room->public_id, 'file' => $this->public_id]),
        ];
    }
}
