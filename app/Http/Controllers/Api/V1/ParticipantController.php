<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Participant\UpdateParticipantRequest;
use App\Http\Resources\ParticipantResource;
use App\Models\Participant;
use App\Models\Room;
use App\Services\ParticipantService;
use Illuminate\Http\JsonResponse;

class ParticipantController extends Controller
{
    public function __construct(private ParticipantService $participants) {}

    public function index(Room $room): JsonResponse
    {
        $paginator = $room->participants()->orderBy('joined_at')->paginate(50);

        return $this->success([
            'participants' => ParticipantResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function update(Room $room, Participant $participant, UpdateParticipantRequest $request): JsonResponse
    {
        abort_unless($participant->room_id === $room->id, 404, 'Participant not found in this room.');

        $this->participants->kick($participant);

        return $this->success(message: 'Participant removed successfully.');
    }
}
