<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        activity('auth')
            ->withProperties([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'attempted_email' => $event->credentials['email'] ?? 'N/A',
                'event_type' => 'login_failed',
            ])
            ->log("Phát hiện đợt đăng nhập thất bại với tài khoản: " . ($event->credentials['email'] ?? 'N/A'));
    }
}
