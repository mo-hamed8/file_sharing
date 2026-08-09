<?php

namespace App\Http\Middleware;

use App\Services\ParticipantService;
use App\Services\RoomAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateRoomToken
{
    public function __construct(
        private RoomAccessService $access,
        private ParticipantService $participants,
    ) {}

    /**
     * @param  'host'|'participant'|'any'  $role
     */
    public function handle(Request $request, Closure $next, string $role = 'any'): Response
    {
        $room = $request->route('room');

        $actor = $this->access->resolveFromRequest($request, $room);

        match ($role) {
            'host' => $this->access->authorizeHost($actor),
            'participant' => $this->access->authorizeParticipant($actor),
            default => null,
        };

        if ($actor->isParticipant()) {
            $this->participants->touchLastSeen($actor->participant);
        }

        $request->attributes->set('room_actor', $actor);

        return $next($request);
    }
}
