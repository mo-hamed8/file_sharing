<?php

namespace App\Models;

use App\Enums\ParticipantRole;
use App\Models\Concerns\HasPublicId;
use Database\Factories\ParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[RouteKey('public_id')]
#[Fillable([
    'room_id', 'session_token_hash', 'display_name', 'role', 'last_seen_at', 'joined_at',
])]
#[Hidden(['session_token_hash'])]
#[UseFactory(ParticipantFactory::class)]
class Participant extends Model
{
    use HasFactory, HasPublicId;

    protected function casts(): array
    {
        return [
            'role' => ParticipantRole::class,
            'last_seen_at' => 'datetime',
            'joined_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(SharedFile::class);
    }

    public function isHost(): bool
    {
        return $this->role === ParticipantRole::Host;
    }
}
