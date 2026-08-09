<?php

test('creating a room via the API returns the standard success envelope with a host token', function () {
    $response = $this->postJson('/api/v1/rooms', ['name' => 'Workshop A']);

    $response->assertCreated();
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => ['room' => ['id', 'code', 'status', 'expires_at'], 'host_token'],
    ]);
    $response->assertJson(['success' => true]);
});

test('validation errors via the API return the standard error envelope', function () {
    $response = $this->postJson('/api/v1/rooms', ['duration_hours' => 999]);

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
    $response->assertJsonStructure(['success', 'message', 'errors']);
});

test('an API request without a bearer token cannot access a protected endpoint', function () {
    [$room] = createHostedRoom();

    $response = $this->getJson("/api/v1/rooms/{$room->public_id}/state");

    $response->assertStatus(403);
    $response->assertJson(['success' => false]);
});

test('an API request with an invalid bearer token is rejected', function () {
    [$room] = createHostedRoom();

    $response = $this->withHeaders(bearer('not-a-real-token'))
        ->getJson("/api/v1/rooms/{$room->public_id}/state");

    $response->assertStatus(403);
});

test('joining a room via the API returns a participant token', function () {
    [$room] = createHostedRoom();

    $response = $this->postJson("/api/v1/rooms/{$room->public_id}/join", [
        'display_name' => 'Sara',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['data' => ['room', 'participant_token']]);
});

test('a host can end a room via the API', function () {
    [$room, $hostToken] = createHostedRoom();

    $this->withHeaders(bearer($hostToken))
        ->postJson("/api/v1/rooms/{$room->public_id}/end")
        ->assertOk()
        ->assertJson(['success' => true]);

    expect($room->fresh()->status->value)->toBe('ended');
});

test('requesting a room that does not exist returns a 404 envelope', function () {
    $response = $this->getJson('/api/v1/rooms/00000000000000000000000000');

    $response->assertStatus(404);
    $response->assertJson(['success' => false]);
});
