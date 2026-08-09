<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a host can delete any file in the room', function () {
    Storage::fake('rooms');

    [$room, $hostToken] = createHostedRoom();
    [, $participantToken] = joinAsParticipant($room, 'Sara');

    $upload = $this->withHeaders(bearer($participantToken))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ]);
    $fileId = $upload->json('data.id');

    $this->withHeaders(bearer($hostToken))
        ->delete(route('rooms.files.destroy', ['room' => $room, 'file' => $fileId]))
        ->assertOk();

    expect($room->files()->active()->count())->toBe(0);
});

test('a normal participant cannot delete a file uploaded by someone else', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();
    [, $sara] = joinAsParticipant($room, 'Sara');
    [, $omar] = joinAsParticipant($room, 'Omar');

    $upload = $this->withHeaders(bearer($sara))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ]);
    $fileId = $upload->json('data.id');

    $this->withHeaders(bearer($omar))
        ->delete(route('rooms.files.destroy', ['room' => $room, 'file' => $fileId]))
        ->assertForbidden();

    expect($room->files()->active()->count())->toBe(1);
});

test('the original uploader can delete their own file', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();
    [, $token] = joinAsParticipant($room, 'Sara');

    $upload = $this->withHeaders(bearer($token))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ]);
    $fileId = $upload->json('data.id');

    $this->withHeaders(bearer($token))
        ->delete(route('rooms.files.destroy', ['room' => $room, 'file' => $fileId]))
        ->assertOk();

    expect($room->files()->active()->count())->toBe(0);
});
