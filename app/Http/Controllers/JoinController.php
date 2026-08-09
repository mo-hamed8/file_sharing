<?php

namespace App\Http\Controllers;

use App\Http\Requests\Room\LookupRoomByCodeRequest;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JoinController extends Controller
{
    public function show(): View
    {
        return view('join');
    }

    public function store(LookupRoomByCodeRequest $request): RedirectResponse
    {
        $room = Room::where('code', $request->validated('code'))->first();

        if (! $room) {
            return back()->withErrors(['code' => 'No room was found with that code.'])->withInput();
        }

        return redirect()->route('rooms.show', $room);
    }
}
