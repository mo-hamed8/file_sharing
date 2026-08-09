<?php

namespace App\Services;

use App\Enums\FileStatus;
use App\Events\RoomFileUploaded;
use App\Exceptions\FileUploadRejectedException;
use App\Models\Participant;
use App\Models\Room;
use App\Models\SharedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileUploadService
{
    public function __construct(private RoomService $rooms) {}

    public function store(Room $room, Participant $uploader, UploadedFile $file): SharedFile
    {
        $this->rooms->assertJoinable($room);
        $this->assertWithinLimits($room, $file);

        $extension = strtolower($file->getClientOriginalExtension() ?: ($file->extension() ?: 'bin'));
        $storedName = Str::random(40).'.'.$extension;
        $disk = config('rooms.disk');

        $path = $file->storeAs($room->public_id, $storedName, $disk);

        $sharedFile = SharedFile::create([
            'room_id' => $room->id,
            'participant_id' => $uploader->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'storage_disk' => $disk,
            'storage_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'size' => $file->getSize(),
            'status' => FileStatus::Active,
            'uploaded_at' => now(),
            'expires_at' => $room->expires_at,
        ]);

        RoomFileUploaded::dispatch($sharedFile);

        return $sharedFile;
    }

    public function assertWithinLimits(Room $room, UploadedFile $file): void
    {
        $maxFileSize = $room->maximum_file_size ?? config('rooms.uploads.max_file_size');

        if ($file->getSize() > $maxFileSize) {
            throw new FileUploadRejectedException('This file exceeds the maximum allowed size for this room.');
        }

        $maxFiles = $room->maximum_files ?? config('rooms.uploads.max_files_per_room');

        if ($room->files()->active()->count() >= $maxFiles) {
            throw new FileUploadRejectedException('This room has reached its maximum number of files.');
        }

        $maxStorage = $room->total_storage_limit ?? config('rooms.uploads.max_total_storage');
        $currentTotal = (int) $room->files()->active()->sum('size');

        if ($currentTotal + $file->getSize() > $maxStorage) {
            throw new FileUploadRejectedException('This room has reached its storage limit.');
        }
    }

    public function delete(SharedFile $file): void
    {
        Storage::disk($file->storage_disk)->delete($file->storage_path);

        $file->status = FileStatus::Deleted;
        $file->save();
    }

    public function download(SharedFile $file): StreamedResponse
    {
        return Storage::disk($file->storage_disk)->download($file->storage_path, $file->original_name);
    }
}
