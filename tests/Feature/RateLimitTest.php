<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('room creation is rate limited per IP', function () {
    $max = config('rooms.rate_limits.room_creation.max_attempts');

    for ($i = 0; $i < $max; $i++) {
        $this->post('/rooms', [])->assertRedirect();
    }

    $this->post('/rooms', [])->assertStatus(429);
});

test('room joining is rate limited per IP', function () {
    [$room] = createHostedRoom();
    $max = config('rooms.rate_limits.room_join.max_attempts');

    for ($i = 0; $i < $max; $i++) {
        $this->post(route('rooms.join', $room), ['display_name' => "Guest {$i}"]);
    }

    $this->post(route('rooms.join', $room), ['display_name' => 'One Too Many'])
        ->assertStatus(429);
});

test('file uploads are rate limited per IP', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();
    [, $token] = joinAsParticipant($room);
    $max = config('rooms.rate_limits.file_upload.max_attempts');

    for ($i = 0; $i < $max; $i++) {
        $this->withHeaders(bearer($token))->post(route('rooms.files.store', $room), [
            'file' => UploadedFile::fake()->create("file{$i}.pdf", 10, 'application/pdf'),
        ]);
    }

    $this->withHeaders(bearer($token))
        ->post(route('rooms.files.store', $room), [
            'file' => UploadedFile::fake()->create('one-too-many.pdf', 10, 'application/pdf'),
        ])
        ->assertStatus(429);
});
