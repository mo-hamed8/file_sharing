<?php

namespace App\Support;

use App\Models\Room;

readonly class RoomCreationResult
{
    public function __construct(
        public Room $room,
        public string $hostToken,
    ) {}
}
