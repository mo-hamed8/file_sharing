<?php

namespace App\Http\Requests\File;

use App\Models\Room;
use App\Rules\NotBlockedExtension;
use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Room $room */
        $room = $this->route('room');

        $maxBytes = $room->maximum_file_size ?? config('rooms.uploads.max_file_size');
        $maxKilobytes = max(1, intdiv($maxBytes, 1024));

        return [
            'file' => ['required', 'file', "max:{$maxKilobytes}", new NotBlockedExtension],
        ];
    }
}
