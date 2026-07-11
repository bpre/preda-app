<?php

namespace App\Jobs;

use App\Services\LetterNotificationQueueMonitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LetterNotificationQueueHeartbeatJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 30;

    public int $uniqueFor = 120;

    public function __construct()
    {
        $this->onConnection(SendLetterNotificationJob::CONNECTION);
        $this->onQueue(SendLetterNotificationJob::QUEUE);
    }

    public function uniqueId(): string
    {
        return 'letter-notifications-worker-heartbeat';
    }

    public function handle(LetterNotificationQueueMonitor $monitor): void
    {
        $monitor->touchHeartbeat();
    }
}
