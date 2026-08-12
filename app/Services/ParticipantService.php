<?php

namespace App\Services;

use App\Enums\ParticipantRole;
use App\Events\ParticipantJoined;
use App\Events\ParticipantLeft;
use App\Exceptions\CannotRemoveHostException;
use App\Models\Participant;
use App\Models\Room;
use App\Support\ParticipantJoinResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParticipantService
{
    public function __construct(private RoomService $rooms) {}

    public function host(Room $room, string $displayName = 'Host'): ParticipantJoinResult
    {
        return $this->createParticipant($room, $displayName, ParticipantRole::Host);
    }

    public function join(Room $room, string $displayName): ParticipantJoinResult
    {
        $this->rooms->assertJoinable($room);

        $result = $this->createParticipant($room, $displayName, ParticipantRole::Participant);

        Log::info('Participant joined room', [
            'event' => 'participant_joined',
            'room_id' => $room->public_id,
            'participant_id' => $result->participant->public_id,
        ]);

        ParticipantJoined::dispatch($result->participant);

        return $result;
    }

    public function touchLastSeen(Participant $participant): void
    {
        $participant->forceFill(['last_seen_at' => now()])->save();
    }

    public function leave(Participant $participant): void
    {
        if ($participant->isHost()) {
            throw new CannotRemoveHostException;
        }

        $room = $participant->room;
        $displayName = $participant->display_name;

        $participant->delete();

        ParticipantLeft::dispatch($room, $displayName);
    }

    public function kick(Participant $target): void
    {
        $this->leave($target);
    }

    private function createParticipant(Room $room, string $displayName, ParticipantRole $role): ParticipantJoinResult
    {
        $token = Str::random(config('rooms.tokens.participant_length'));

        $participant = $room->participants()->create([
            'session_token_hash' => hash('sha256', $token),
            'display_name' => $displayName,
            'role' => $role,
            'last_seen_at' => now(),
            'joined_at' => now(),
        ]);

        return new ParticipantJoinResult($participant, $token);
    }
}
