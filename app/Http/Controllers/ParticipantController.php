<?php

namespace App\Http\Controllers;

use App\Http\Requests\Participant\JoinRoomRequest;
use App\Models\Room;
use App\Services\ParticipantService;
use App\Services\RoomAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function __construct(
        private ParticipantService $participants,
        private RoomAccessService $access,
    ) {}

    public function store(Room $room, JoinRoomRequest $request): RedirectResponse
    {
        $result = $this->participants->join($room, $request->validated('display_name'));

        $this->access->rememberInSession($request, $room, $result->sessionToken);

        return redirect()->route('rooms.show', $room);
    }

    public function destroy(Room $room, Request $request): RedirectResponse
    {
        $actor = $request->roomActor();

        $this->participants->leave($actor->participant);
        $this->access->forgetFromSession($request, $room);

        return redirect()->route('home');
    }
}
