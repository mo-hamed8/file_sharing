<?php

use App\Models\Room;
use App\Models\SharedFile;
use Illuminate\Support\Facades\Storage;

test('the cleanup command expires past-due active rooms and purges their data', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom(['expires_at' => now()->subMinute()]);
    [$participant] = joinAsParticipant($room);

    Storage::disk('rooms')->put("{$room->public_id}/dummy.pdf", 'contents');
    SharedFile::factory()->create([
        'room_id' => $room->id,
        'participant_id' => $participant->id,
        'storage_path' => "{$room->public_id}/dummy.pdf",
        'storage_disk' => 'rooms',
    ]);

    $this->artisan('app:cleanup-expired-rooms')
        ->expectsOutputToContain('Purged 1 expired room(s).')
        ->assertExitCode(0);

    $room->refresh();

    expect($room->status->value)->toBe('expired');
    expect($room->files()->count())->toBe(0);
    expect($room->participants()->count())->toBe(0);
    Storage::disk('rooms')->assertDirectoryEmpty($room->public_id);
});

test('the cleanup command leaves untouched rooms alone', function () {
    [$activeRoom] = createHostedRoom();

    $this->artisan('app:cleanup-expired-rooms');

    expect($activeRoom->fresh()->status->value)->toBe('active');
});

test('an expired room is kept as a tombstone rather than deleted outright', function () {
    [$room] = createHostedRoom(['expires_at' => now()->subMinute()]);

    $this->artisan('app:cleanup-expired-rooms');

    expect(Room::find($room->id))->not->toBeNull();
});
