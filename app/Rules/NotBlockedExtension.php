<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class NotBlockedExtension implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $extension = strtolower($value->getClientOriginalExtension() ?: ($value->extension() ?: ''));

        if (in_array($extension, config('rooms.uploads.blocked_extensions'), true)) {
            $fail('This file type is not allowed.');
        }
    }
}
