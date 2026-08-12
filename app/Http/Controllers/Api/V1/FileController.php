<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\File\StoreFileRequest;
use App\Http\Resources\SharedFileResource;
use App\Models\Room;
use App\Models\SharedFile;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(private FileUploadService $uploads) {}

    public function index(Room $room): JsonResponse
    {
        $paginator = $room->files()->active()->orderByDesc('uploaded_at')->paginate(20);

        return $this->success([
            'files' => SharedFileResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(Room $room, StoreFileRequest $request): JsonResponse
    {
        $actor = $request->roomActor();
        $canUpload = $room->allow_participant_uploads || $actor->isHost();

        if (! $canUpload) {
            Log::warning('Upload rejected: participant uploads disabled for this room', [
                'event' => 'upload_rejected',
                'room_id' => $room->public_id,
                'participant_id' => $actor->participant?->public_id,
            ]);
        }

        abort_unless($canUpload, 403, 'Uploads are disabled in this room.');

        $file = $this->uploads->store($room, $actor->participant, $request->file('file'));

        return $this->success(new SharedFileResource($file), 'File uploaded successfully.', 201);
    }

    public function show(Room $room, SharedFile $file): JsonResponse
    {
        $this->assertBelongsToRoom($room, $file);

        return $this->success(new SharedFileResource($file));
    }

    public function download(Room $room, SharedFile $file): StreamedResponse
    {
        $this->assertBelongsToRoom($room, $file);

        return $this->uploads->download($file);
    }

    public function destroy(Room $room, SharedFile $file, Request $request): JsonResponse
    {
        $this->assertBelongsToRoom($room, $file);

        $actor = $request->roomActor();
        $canDelete = $actor->isHost() || $actor->participant?->id === $file->participant_id;

        if (! $canDelete) {
            Log::warning('File delete rejected: insufficient permissions', [
                'event' => 'file_delete_rejected',
                'room_id' => $room->public_id,
                'file_id' => $file->public_id,
                'participant_id' => $actor->participant?->public_id,
            ]);
        }

        abort_unless($canDelete, 403, 'You cannot delete this file.');

        $this->uploads->delete($file);

        return $this->success(message: 'File deleted successfully.');
    }

    private function assertBelongsToRoom(Room $room, SharedFile $file): void
    {
        abort_unless($file->room_id === $room->id, 404, 'File not found in this room.');
    }
}
