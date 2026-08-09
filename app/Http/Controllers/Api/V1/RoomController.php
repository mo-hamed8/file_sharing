<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Participant\JoinRoomRequest;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Http\Resources\RoomStateResource;
use App\Models\Room;
use App\Services\ParticipantService;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function __construct(
        private RoomService $rooms,
        private ParticipantService $participants,
    ) {}

    public function store(StoreRoomRequest $request): JsonResponse
    {
        $result = $this->rooms->create($request->validated());
        $this->participants->host($result->room);

        return $this->success([
            'room' => new RoomResource($result->room),
            'host_token' => $result->hostToken,
        ], 'Room created successfully.', 201);
    }

    public function show(Room $room): JsonResponse
    {
        return $this->success(new RoomResource($room));
    }

    public function join(Room $room, JoinRoomRequest $request): JsonResponse
    {
        $result = $this->participants->join($room, $request->validated('display_name'));

        return $this->success([
            'room' => new RoomResource($room),
            'participant_token' => $result->sessionToken,
        ], 'Joined room successfully.');
    }

    public function leave(Room $room, Request $request): JsonResponse
    {
        $this->participants->leave($request->roomActor()->participant);

        return $this->success(message: 'Left room successfully.');
    }

    public function end(Room $room): JsonResponse
    {
        $this->rooms->end($room);

        return $this->success(message: 'Room ended successfully.');
    }

    public function state(Room $room): JsonResponse
    {
        return $this->success(new RoomStateResource($room));
    }
}
