<?php

namespace Database\Factories;

use App\Enums\ParticipantRole;
use App\Models\Participant;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'session_token_hash' => hash('sha256', Str::random(40)),
            'display_name' => fake()->firstName(),
            'role' => ParticipantRole::Participant,
            'last_seen_at' => now(),
            'joined_at' => now(),
        ];
    }

    public function host(): static
    {
        return $this->state(fn () => [
            'role' => ParticipantRole::Host,
        ]);
    }
}
