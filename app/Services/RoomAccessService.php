<?php

namespace App\Services;

use App\Models\Room;
use App\Support\RoomActor;
use Illuminate\Http\Request;

/**
 * Resolves "who is making this request" for a room and enforces
 * host/participant-only actions. Used identically by the web session
 * flow and the API bearer-token flow, so authorization logic never
 * has to be duplicated between them.
 */
class RoomAccessService
{
    public function resolveFromRequest(Request $request, Room $room): RoomActor
    {
        $token = $this->extractToken($request, $room);

        if ($token === null) {
            return new RoomActor($room);
        }

        $hash = hash('sha256', $token);

        if (hash_equals($room->host_token_hash, $hash)) {
            return new RoomActor($room, $room->host);
        }

        $participant = $room->participants()->where('session_token_hash', $hash)->first();

        return new RoomActor($room, $participant);
    }

    public function authorizeHost(RoomActor $actor): void
    {
        abort_unless($actor->isHost(), 403, 'Only the host can perform this action.');
    }

    public function authorizeParticipant(RoomActor $actor): void
    {
        abort_if($actor->isStranger(), 403, 'You must join this room first.');
    }

    public function rememberInSession(Request $request, Room $room, string $rawToken): void
    {
        $request->session()->put($this->sessionKey($room), $rawToken);
    }

    public function forgetFromSession(Request $request, Room $room): void
    {
        $request->session()->forget($this->sessionKey($room));
    }

    private function extractToken(Request $request, Room $room): ?string
    {
        if ($token = $request->bearerToken()) {
            return $token;
        }

        return $request->hasSession() ? $request->session()->get($this->sessionKey($room)) : null;
    }

    private function sessionKey(Room $room): string
    {
        return "rooms.{$room->public_id}.token";
    }
}
