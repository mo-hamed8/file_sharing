<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoomRequest extends FormRequest
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
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'duration_hours' => ['nullable', 'integer', Rule::in(config('rooms.durations'))],
            'allow_participant_uploads' => ['nullable', 'boolean'],
            'maximum_file_size' => ['nullable', 'integer', 'min:1'],
            'maximum_files' => ['nullable', 'integer', 'min:1'],
            'total_storage_limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
