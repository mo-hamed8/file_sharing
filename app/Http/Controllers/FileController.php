<?php

namespace App\Http\Controllers;

use App\Http\Requests\File\StoreFileRequest;
use App\Http\Resources\SharedFileResource;
use App\Models\Room;
use App\Models\SharedFile;
use App\Services\FileUploadService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(private FileUploadService $uploads) {}

    /**
     * Uploaded via fetch/XHR from the dropzone component (needed to track
     * upload progress), so this returns JSON rather than redirecting.
     */
    public function store(Room $room, StoreFileRequest $request): JsonResponse
    {
        $actor = $request->roomActor();

        abort_unless($room->allow_participant_uploads || $actor->isHost(), 403, 'Uploads are disabled in this room.');

        $file = $this->uploads->store($room, $actor->participant, $request->file('file'));

        return ApiResponse::success(new SharedFileResource($file), 'File uploaded successfully.', 201);
    }

    public function download(Room $room, SharedFile $file): StreamedResponse
    {
        $this->assertBelongsToRoom($room, $file);

        return $this->uploads->download($file);
    }

    /**
     * Called via fetch from the room page's live file list, so this
     * returns JSON rather than redirecting.
     */
    public function destroy(Room $room, SharedFile $file, Request $request): JsonResponse
    {
        $this->assertBelongsToRoom($room, $file);

        $actor = $request->roomActor();
        abort_unless($actor->isHost() || $actor->participant?->id === $file->participant_id, 403, 'You cannot delete this file.');

        $this->uploads->delete($file);

        return ApiResponse::success(message: 'File deleted successfully.');
    }

    private function assertBelongsToRoom(Room $room, SharedFile $file): void
    {
        abort_unless($file->room_id === $room->id, 404, 'File not found in this room.');
    }
}
