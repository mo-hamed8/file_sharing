<?php

namespace Database\Factories;

use App\Enums\FileStatus;
use App\Models\Participant;
use App\Models\Room;
use App\Models\SharedFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SharedFile>
 */
class SharedFileFactory extends Factory
{
    protected $model = SharedFile::class;

    public function definition(): array
    {
        $storedName = Str::random(40).'.pdf';

        return [
            'room_id' => Room::factory(),
            'participant_id' => Participant::factory(),
            'original_name' => fake()->word().'.pdf',
            'stored_name' => $storedName,
            'storage_disk' => 'rooms',
            'storage_path' => $storedName,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => fake()->numberBetween(1024, 1024 * 1024),
            'status' => FileStatus::Active,
            'uploaded_at' => now(),
            'expires_at' => now()->addHours(3),
        ];
    }
}
