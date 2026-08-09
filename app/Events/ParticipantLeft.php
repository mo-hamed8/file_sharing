<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Foundation\Events\Dispatchable;

class ParticipantLeft
{
    use Dispatchable;

    public function __construct(public Room $room, public string $displayName) {}
}
