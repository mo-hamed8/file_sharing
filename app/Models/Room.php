<?php

namespace App\Models;

use App\Enums\ParticipantRole;
use App\Enums\RoomStatus;
use App\Models\Concerns\HasPublicId;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[RouteKey('public_id')]
#[Fillable([
    'name', 'code', 'host_token_hash', 'status', 'expires_at',
    'allow_participant_uploads', 'maximum_file_size', 'maximum_files', 'total_storage_limit',
])]
#[Hidden(['host_token_hash'])]
#[UseFactory(RoomFactory::class)]
class Room extends Model
{
    use HasFactory, HasPublicId;

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'allow_participant_uploads' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SharedFile::class);
    }

    public function host(): HasOne
    {
        return $this->hasOne(Participant::class)->where('role', ParticipantRole::Host);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', RoomStatus::Active);
    }

    #[Scope]
    protected function expired(Builder $query): void
    {
        $query->where('expires_at', '<=', now());
    }
}
