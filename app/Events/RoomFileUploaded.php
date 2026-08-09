<?php

namespace App\Events;

use App\Models\SharedFile;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched whenever a file finishes uploading. No listeners are wired
 * yet — this exists so live updates (Reverb/broadcasting) can be added
 * later by implementing ShouldBroadcastNow on this class, without any
 * change to FileUploadService or controllers.
 */
class RoomFileUploaded
{
    use Dispatchable;

    public function __construct(public SharedFile $file) {}
}
