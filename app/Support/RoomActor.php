<?php

namespace App\Support;

use App\Models\Participant;
use App\Models\Room;

/**
 * Resolves "who is making this request" for a given room: the host, a
 * joined participant, or a stranger with no valid token at all.
 */
readonly class RoomActor
{
    public function __construct(
        public Room $room,
        public ?Participant $participant = null,
    ) {}

    public function isHost(): bool
    {
        return $this->participant?->isHost() ?? false;
    }

    public function isParticipant(): bool
    {
        return $this->participant !== null;
    }

    public function isStranger(): bool
    {
        return $this->participant === null;
    }
}
