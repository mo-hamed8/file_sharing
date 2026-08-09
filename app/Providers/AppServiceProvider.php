<?php

namespace App\Providers;

use App\Support\RoomActor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->registerRateLimiters();

        Request::macro('roomActor', function (): ?RoomActor {
            /** @var Request $this */
            return $this->attributes->get('room_actor');
        });
    }

    private function registerRateLimiters(): void
    {
        foreach (['room_creation', 'room_join', 'file_upload'] as $limiter) {
            RateLimiter::for($limiter, function (Request $request) use ($limiter) {
                $config = config("rooms.rate_limits.{$limiter}");

                return Limit::perMinutes($config['decay_minutes'], $config['max_attempts'])
                    ->by($request->ip());
            });
        }
    }
}
