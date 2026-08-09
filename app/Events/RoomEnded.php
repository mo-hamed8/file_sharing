<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Foundation\Events\Dispatchable;

class RoomEnded
{
    use Dispatchable;

    public function __construct(public Room $room) {}
}
