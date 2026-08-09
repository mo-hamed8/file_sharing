<?php

use App\Models\Participant;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Creates an active room together with its host's raw bearer token
 * (the host's Participant row is created directly, bypassing HTTP,
 * so tests can control multiple independent actors in one test).
 *
 * @return array{0: Room, 1: string}
 */
function createHostedRoom(array $roomAttributes = []): array
{
    $hostToken = Str::random(40);

    $room = Room::factory()->create(array_merge([
        'host_token_hash' => hash('sha256', $hostToken),
    ], $roomAttributes));

    Participant::factory()->host()->create(['room_id' => $room->id]);

    return [$room, $hostToken];
}

/**
 * @return array{0: Participant, 1: string}
 */
function joinAsParticipant(Room $room, string $displayName = 'Sara'): array
{
    $token = Str::random(40);

    $participant = Participant::factory()->create([
        'room_id' => $room->id,
        'display_name' => $displayName,
        'session_token_hash' => hash('sha256', $token),
    ]);

    return [$participant, $token];
}

/**
 * Mirrors the real dropzone/room-page JS, which always sends
 * Accept: application/json alongside the bearer token.
 *
 * @return array{Authorization: string, Accept: string}
 */
function bearer(string $token): array
{
    return [
        'Authorization' => "Bearer {$token}",
        'Accept' => 'application/json',
    ];
}
