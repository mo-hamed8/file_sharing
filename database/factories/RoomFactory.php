<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(7)),
            'name' => fake()->words(2, true),
            'host_token_hash' => hash('sha256', Str::random(40)),
            'status' => RoomStatus::Active,
            'expires_at' => now()->addHours(3),
            'allow_participant_uploads' => true,
            'maximum_file_size' => null,
            'maximum_files' => null,
            'total_storage_limit' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subHour(),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RoomStatus::Ended,
        ]);
    }
}
