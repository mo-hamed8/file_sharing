<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('a participant can upload an allowed file', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();
    [, $token] = joinAsParticipant($room);

    $file = UploadedFile::fake()->create('handout.pdf', 500, 'application/pdf');

    $response = $this->withHeaders(bearer($token))
        ->post(route('rooms.files.store', $room), ['file' => $file]);

    $response->assertCreated();
    $response->assertJson(['success' => true]);
    expect($room->files()->count())->toBe(1);

    $sharedFile = $room->files()->first();
    Storage::disk('rooms')->assertExists($sharedFile->storage_path);
});

test('an upload with a blocked extension is rejected', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();
    [, $token] = joinAsParticipant($room);

    $file = UploadedFile::fake()->create('script.php', 10, 'application/x-php');

    $response = $this->withHeaders(bearer($token))
        ->post(route('rooms.files.store', $room), ['file' => $file]);

    $response->assertStatus(422);
    expect($room->files()->count())->toBe(0);
});

test('an oversized upload is rejected', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom(['maximum_file_size' => 1024]);
    [, $token] = joinAsParticipant($room);

    $file = UploadedFile::fake()->create('big.pdf', 5000, 'application/pdf');

    $this->withHeaders(bearer($token))
        ->post(route('rooms.files.store', $room), ['file' => $file])
        ->assertStatus(422);

    expect($room->files()->count())->toBe(0);
});

test('upload is rejected once the room file count limit is reached', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom(['maximum_files' => 1]);
    [, $token] = joinAsParticipant($room);

    $this->withHeaders(bearer($token))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('one.pdf', 100, 'application/pdf'),
    ])->assertCreated();

    $response = $this->withHeaders(bearer($token))->post(route('rooms.files.store', $room), [
        'file' => UploadedFile::fake()->create('two.pdf', 100, 'application/pdf'),
    ]);

    $response->assertStatus(422);
    expect($room->files()->count())->toBe(1);
});

test('upload is rejected once the room storage limit is reached', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom(['total_storage_limit' => 1024]);
    [, $token] = joinAsParticipant($room);

    $file = UploadedFile::fake()->create('big.pdf', 5000, 'application/pdf');

    $this->withHeaders(bearer($token))
        ->post(route('rooms.files.store', $room), ['file' => $file])
        ->assertStatus(422);

    expect($room->files()->count())->toBe(0);
});

test('a stranger without a session token cannot upload', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom();

    $file = UploadedFile::fake()->create('handout.pdf', 100, 'application/pdf');

    $this->post(route('rooms.files.store', $room), ['file' => $file])
        ->assertForbidden();
});

test('uploading is blocked after the room expires', function () {
    Storage::fake('rooms');

    [$room] = createHostedRoom(['expires_at' => now()->subMinute()]);
    [, $token] = joinAsParticipant($room);

    $file = UploadedFile::fake()->create('handout.pdf', 100, 'application/pdf');

    // The room page now renders the closed state instead of the dashboard...
    $this->get(route('rooms.show', $room))->assertSee('expired');

    // ...and the upload endpoint itself also refuses new files (defense in depth,
    // independent of whether the scheduled cleanup command has run yet).
    $this->withHeaders(bearer($token))
        ->post(route('rooms.files.store', $room), ['file' => $file])
        ->assertStatus(410);

    expect($room->files()->count())->toBe(0);
});
