<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Http\Requests\Room\StoreRoomRequest;
use App\Http\Resources\RoomStateResource;
use App\Models\Room;
use App\Services\ParticipantService;
use App\Services\QrCodeService;
use App\Services\RoomAccessService;
use App\Services\RoomService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(
        private RoomService $rooms,
        private ParticipantService $participants,
        private RoomAccessService $access,
    ) {}

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $result = $this->rooms->create($request->validated());
        $this->participants->host($result->room);

        $this->access->rememberInSession($request, $result->room, $result->hostToken);

        return redirect()->route('rooms.show', $result->room);
    }

    public function show(Request $request, Room $room): View
    {
        if ($room->status !== RoomStatus::Active || $room->expires_at->isPast()) {
            return view('rooms.closed', [
                'room' => $room,
                'isExpired' => $room->status === RoomStatus::Expired || $room->expires_at->isPast(),
            ]);
        }

        $actor = $this->access->resolveFromRequest($request, $room);

        return view('rooms.show', [
            'room' => $room,
            'actor' => $actor,
        ]);
    }

    public function shortLink(Room $room): RedirectResponse
    {
        return redirect()->route('rooms.show', $room);
    }

    public function end(Room $room): RedirectResponse
    {
        $this->rooms->end($room);

        return redirect()->route('rooms.show', $room);
    }

    public function state(Room $room): JsonResponse
    {
        return ApiResponse::success(new RoomStateResource($room));
    }

    public function qr(Room $room, QrCodeService $qrCodes): Response
    {
        $png = $qrCodes->generate(route('rooms.short', $room));

        return response($png, 200)->header('Content-Type', 'image/png');
    }
}
