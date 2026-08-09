<?php

use App\Models\Room;

test('a participant can join an active room with a display name', function () {
    $room = Room::factory()->create();

    $response = $this->post(route('rooms.join', $room), [
        'display_name' => 'Sara',
    ]);

    $response->assertRedirect(route('rooms.show', $room));
    expect($room->participants()->where('display_name', 'Sara')->exists())->toBeTrue();
});

test('a participant cannot join an expired room', function () {
    $room = Room::factory()->expired()->create();

    $response = $this->post(route('rooms.join', $room), [
        'display_name' => 'Sara',
    ]);

    $response->assertSessionHasErrors('error');
    expect($room->participants()->where('display_name', 'Sara')->exists())->toBeFalse();
});

test('a participant cannot join a room the host already ended', function () {
    $room = Room::factory()->ended()->create();

    $response = $this->post(route('rooms.join', $room), [
        'display_name' => 'Sara',
    ]);

    $response->assertSessionHasErrors('error');
});

test('joining requires a display name', function () {
    $room = Room::factory()->create();

    $this->post(route('rooms.join', $room), ['display_name' => ''])
        ->assertSessionHasErrors('display_name');
});

test('visiting the room shows the join form to a stranger', function () {
    $room = Room::factory()->create();

    $this->get(route('rooms.show', $room))
        ->assertOk()
        ->assertSee('Join this room');
});
