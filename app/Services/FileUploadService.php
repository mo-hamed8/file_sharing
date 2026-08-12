<?php

namespace App\Services;

use App\Enums\FileStatus;
use App\Events\RoomFileUploaded;
use App\Exceptions\FileUploadRejectedException;
use App\Models\Participant;
use App\Models\Room;
use App\Models\SharedFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
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

        Log::info('File upload started', [
            'event' => 'file_upload_started',
            'room_id' => $room->public_id,
            'participant_id' => $uploader->public_id,
            'file_size' => $file->getSize(),
            'extension' => $extension,
        ]);

        try {
            $path = $file->storeAs($room->public_id, $storedName, $disk);
        } catch (\Throwable $e) {
            Log::error('File upload failed', [
                'event' => 'file_upload_failed',
                'room_id' => $room->public_id,
                'participant_id' => $uploader->public_id,
                'disk' => $disk,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

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

        Log::info('File uploaded successfully', [
            'event' => 'file_uploaded',
            'room_id' => $room->public_id,
            'file_id' => $sharedFile->public_id,
            'participant_id' => $uploader->public_id,
            'file_size' => $sharedFile->size,
        ]);

        RoomFileUploaded::dispatch($sharedFile);

        return $sharedFile;
    }

    public function assertWithinLimits(Room $room, UploadedFile $file): void
    {
        $maxFileSize = $room->maximum_file_size ?? config('rooms.uploads.max_file_size');

        if ($file->getSize() > $maxFileSize) {
            Log::warning('File upload rejected: file exceeds maximum size', [
                'event' => 'file_upload_rejected',
                'room_id' => $room->public_id,
                'reason' => 'max_file_size',
                'file_size' => $file->getSize(),
                'limit' => $maxFileSize,
            ]);

            throw new FileUploadRejectedException('This file exceeds the maximum allowed size for this room.');
        }

        $maxFiles = $room->maximum_files ?? config('rooms.uploads.max_files_per_room');

        if ($room->files()->active()->count() >= $maxFiles) {
            Log::warning('File upload rejected: room reached maximum file count', [
                'event' => 'file_upload_rejected',
                'room_id' => $room->public_id,
                'reason' => 'max_files_per_room',
                'limit' => $maxFiles,
            ]);

            throw new FileUploadRejectedException('This room has reached its maximum number of files.');
        }

        $maxStorage = $room->total_storage_limit ?? config('rooms.uploads.max_total_storage');
        $currentTotal = (int) $room->files()->active()->sum('size');

        if ($currentTotal + $file->getSize() > $maxStorage) {
            Log::warning('File upload rejected: room reached storage limit', [
                'event' => 'file_upload_rejected',
                'room_id' => $room->public_id,
                'reason' => 'max_total_storage',
                'current_total' => $currentTotal,
                'limit' => $maxStorage,
            ]);

            throw new FileUploadRejectedException('This room has reached its storage limit.');
        }
    }

    public function delete(SharedFile $file): void
    {
        try {
            Storage::disk($file->storage_disk)->delete($file->storage_path);
        } catch (\Throwable $e) {
            Log::error('File deletion failed', [
                'event' => 'file_delete_failed',
                'file_id' => $file->public_id,
                'disk' => $file->storage_disk,
                'exception' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $file->status = FileStatus::Deleted;
        $file->save();

        Log::info('File deleted', [
            'event' => 'file_deleted',
            'room_id' => $file->room->public_id,
            'file_id' => $file->public_id,
        ]);
    }

    public function download(SharedFile $file): StreamedResponse
{
    $stream = Storage::disk($file->storage_disk)
        ->readStream($file->storage_path);

    abort_if($stream === false, 404, 'File not found.');

    Log::info('File downloaded', [
        'event' => 'file_downloaded',
        'room_id' => $file->room->public_id,
        'file_id' => $file->public_id,
    ]);

    return response()->streamDownload(
        function () use ($stream) {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        },
        $file->original_name,
        [
            'Content-Type' => $file->mime_type ?? 'application/octet-stream',
        ]
    );
}
}
