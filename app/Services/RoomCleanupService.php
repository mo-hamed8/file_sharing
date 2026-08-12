<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Support\Facades\Log;
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

        Log::info('Expired room sweep completed', [
            'event' => 'room_expiry_sweep_completed',
            'rooms_purged' => $rooms->count(),
        ]);

        return $rooms->count();
    }

    /**
     * Remove a room's files (disk + rows) and participants, keeping the
     * Room row itself as a status tombstone so old links render a clear
     * "expired"/"ended" page instead of a bare 404.
     */
    public function purgeRoom(Room $room, RoomStatus $finalStatus = RoomStatus::Ended): void
    {
        try {
            Storage::disk(config('rooms.disk'))->deleteDirectory($room->public_id);
        } catch (\Throwable $e) {
            Log::error('Room storage cleanup failed', [
                'event' => 'room_cleanup_failed',
                'room_id' => $room->public_id,
                'disk' => config('rooms.disk'),
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $room->files()->delete();
        $room->participants()->delete();

        $room->status = $finalStatus;
        $room->save();

        Log::info(match ($finalStatus) {
            RoomStatus::Expired => 'Room expired',
            default => 'Room closed',
        }, [
            'event' => $finalStatus === RoomStatus::Expired ? 'room_expired' : 'room_closed',
            'room_id' => $room->public_id,
        ]);
    }
}
