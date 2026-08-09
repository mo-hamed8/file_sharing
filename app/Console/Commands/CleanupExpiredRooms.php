<?php

namespace App\Console\Commands;

use App\Services\RoomCleanupService;
use Illuminate\Console\Command;

class CleanupExpiredRooms extends Command
{
    protected $signature = 'app:cleanup-expired-rooms';

    protected $description = 'Expire rooms past their lifetime and purge their files and participants';

    public function handle(RoomCleanupService $cleanup): int
    {
        $count = $cleanup->purgeExpiredRooms();

        $this->info("Purged {$count} expired room(s).");

        return self::SUCCESS;
    }
}
