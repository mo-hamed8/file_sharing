<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a host can end the room', function () {
    [$room, $hostToken] = createHostedRoom();

    $this->withHeaders(bearer($hostToken))
        ->post(route('rooms.end', $room))
        ->assertRedirect(route('rooms.show', $room));

    expect($room->fresh()->status->value)->toBe('ended');
});

test('ending a room purges its files from storage and the database', function () {
    Storage::fake('rooms');

    [$room, $hostToken] = createHostedRoom();
    [, $token] = joinAsParticipant($room);

    $upload = $this->withHeaders(bearer($token))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
    ]);
    $storagePath = $room->files()->first()->storage_path;

    $this->withHeaders(bearer($hostToken))->post(route('rooms.end', $room));

    expect($room->files()->count())->toBe(0);
    expect($room->participants()->count())->toBe(0);
    Storage::disk('rooms')->assertMissing($storagePath);
});

test('a normal participant cannot end the room', function () {
    [$room] = createHostedRoom();
    [, $token] = joinAsParticipant($room);

    $this->withHeaders(bearer($token))
        ->post(route('rooms.end', $room))
        ->assertForbidden();

    expect($room->fresh()->status->value)->toBe('active');
});

test('the room shows as ended after the host ends it', function () {
    [$room, $hostToken] = createHostedRoom();

    $this->withHeaders(bearer($hostToken))->post(route('rooms.end', $room));

    $this->get(route('rooms.show', $room))
        ->assertOk()
        ->assertSee('ended');
});
