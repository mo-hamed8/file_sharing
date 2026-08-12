<?php

namespace App\Services;

use App\Enums\RoomStatus;
use App\Events\RoomEnded;
use App\Exceptions\RoomNotJoinableException;
use App\Models\Room;
use App\Support\RoomCreationResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RoomService
{
    public function __construct(private RoomCleanupService $cleanup) {}

    public function create(array $data): RoomCreationResult
    {
        $hostToken = Str::random(config('rooms.tokens.host_length'));

        $room = Room::create([
            'name' => $data['name'] ?? null,
            'code' => $this->generateUniqueCode(),
            'host_token_hash' => hash('sha256', $hostToken),
            'status' => RoomStatus::Active,
            'expires_at' => $this->computeExpiresAt($data['duration_hours'] ?? config('rooms.default_duration')),
            'allow_participant_uploads' => $data['allow_participant_uploads'] ?? false,
            'maximum_file_size' => $data['maximum_file_size'] ?? null,
            'maximum_files' => $data['maximum_files'] ?? null,
            'total_storage_limit' => $data['total_storage_limit'] ?? null,
        ]);

        Log::info('Room created', [
            'event' => 'room_created',
            'room_id' => $room->public_id,
            'expires_at' => $room->expires_at?->toIso8601String(),
            'allow_participant_uploads' => $room->allow_participant_uploads,
        ]);

        return new RoomCreationResult($room, $hostToken);
    }

    public function generateUniqueCode(): string
    {
        $alphabet = config('rooms.code.alphabet');
        $length = config('rooms.code.length');
        $alphabetLength = strlen($alphabet);

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, $alphabetLength - 1)];
            }
        } while (Room::where('code', $code)->exists());

        return $code;
    }

    public function computeExpiresAt(int $durationHours): Carbon
    {
        return now()->addHours($durationHours);
    }

    public function end(Room $room): void
    {
        $this->cleanup->purgeRoom($room, RoomStatus::Ended);

        RoomEnded::dispatch($room);
    }

    public function assertJoinable(Room $room): void
    {
        if ($room->status !== RoomStatus::Active || $room->expires_at->isPast()) {
            Log::warning('Room join rejected: room is closed or expired', [
                'event' => 'room_join_rejected',
                'room_id' => $room->public_id,
                'status' => $room->status->value,
            ]);

            throw new RoomNotJoinableException;
        }
    }
}
