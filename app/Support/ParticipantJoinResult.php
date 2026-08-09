<?php

namespace App\Support;

use App\Models\Participant;

readonly class ParticipantJoinResult
{
    public function __construct(
        public Participant $participant,
        public string $sessionToken,
    ) {}
}
