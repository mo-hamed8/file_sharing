<?php

use App\Models\Room;

test('a guest can create a room without an account', function () {
    $response = $this->post('/rooms', [
        'name' => 'Classroom 204',
        'duration_hours' => 3,
    ]);

    $room = Room::first();

    expect($room)->not->toBeNull();
    expect($room->name)->toBe('Classroom 204');
    expect($room->status->value)->toBe('active');
    expect($room->participants()->count())->toBe(1);
    expect($room->participants()->first()->role->value)->toBe('host');

    $response->assertRedirect(route('rooms.show', $room));
});

test('room expiration matches the requested duration', function () {
    $this->post('/rooms', ['duration_hours' => 6]);

    $room = Room::first();

    expect(now()->diffInHours($room->expires_at))->toBeGreaterThanOrEqual(5);
    expect(now()->diffInHours($room->expires_at))->toBeLessThanOrEqual(6);
});

test('the host is redirected straight into the room dashboard, not the join form', function () {
    $this->post('/rooms', []);

    $room = Room::first();

    $response = $this->get(route('rooms.show', $room));

    $response->assertOk();
    $response->assertSee('End room', false);
});

test('room creation is rate limited', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post('/rooms', [])->assertRedirect();
    }

    $this->post('/rooms', [])->assertStatus(429);
});
