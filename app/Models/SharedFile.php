<?php

namespace App\Models;

use App\Enums\FileStatus;
use App\Models\Concerns\HasPublicId;
use Database\Factories\SharedFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[RouteKey('public_id')]
#[Fillable([
    'room_id', 'participant_id', 'original_name', 'stored_name', 'storage_disk', 'storage_path',
    'mime_type', 'extension', 'size', 'status', 'uploaded_at', 'expires_at',
])]
#[Hidden(['storage_disk', 'storage_path', 'stored_name'])]
#[UseFactory(SharedFileFactory::class)]
class SharedFile extends Model
{
    use HasFactory, HasPublicId;

    protected function casts(): array
    {
        return [
            'status' => FileStatus::class,
            'size' => 'integer',
            'uploaded_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', FileStatus::Active);
    }
}
