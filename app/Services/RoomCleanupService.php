<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Support\Facades\Storage;

class RoomCleanupService
{
    /**
     * Expire every active room whose time is up. Returns how many were processed.
     */
    public function purgeExpiredRooms(): int
    {
        $rooms = Room::active()->expired()->get();

        foreach ($rooms as $room) {
            $this->purgeRoom($room, RoomStatus::Expired);
        }

        return $rooms->count();
    }

    /**
     * Remove a room's files (disk + rows) and participants, keeping the
     * Room row itself as a status tombstone so old links render a clear
     * "expired"/"ended" page instead of a bare 404.
     */
    public function purgeRoom(Room $room, RoomStatus $finalStatus = RoomStatus::Ended): void
    {
        Storage::disk(config('rooms.disk'))->deleteDirectory($room->public_id);

        $room->files()->delete();
        $room->participants()->delete();

        $room->status = $finalStatus;
        $room->save();
    }
}
