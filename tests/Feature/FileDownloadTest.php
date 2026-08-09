<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a participant can download a room file', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();
    [, $token] = joinAsParticipant($room);

    $upload = $this->withHeaders(bearer($token))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ]);
    $fileId = $upload->json('data.id');

    $this->withHeaders(bearer($token))
        ->get(route('rooms.files.download', ['room' => $room, 'file' => $fileId]))
        ->assertOk();
});

test('someone outside the room cannot download a private file', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();
    [, $token] = joinAsParticipant($room);

    $upload = $this->withHeaders(bearer($token))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ]);
    $fileId = $upload->json('data.id');

    // No Authorization header and no session token for this room at all.
    $this->flushHeaders();
    $this->get(route('rooms.files.download', ['room' => $room, 'file' => $fileId]))
        ->assertForbidden();
});

test('a file cannot be downloaded through the wrong room', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();
    [$otherRoom] = createHostedRoom();
    [, $token] = joinAsParticipant($room);
    [, $otherToken] = joinAsParticipant($otherRoom);

    $upload = $this->withHeaders(bearer($token))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ]);
    $fileId = $upload->json('data.id');

    $this->withHeaders(bearer($otherToken))
        ->get(route('rooms.files.download', ['room' => $otherRoom, 'file' => $fileId]))
        ->assertNotFound();
});
