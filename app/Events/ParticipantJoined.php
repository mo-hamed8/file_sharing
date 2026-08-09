<?php

namespace App\Events;

use App\Models\Participant;
use Illuminate\Foundation\Events\Dispatchable;

class ParticipantJoined
{
    use Dispatchable;

    public function __construct(public Participant $participant) {}
}
