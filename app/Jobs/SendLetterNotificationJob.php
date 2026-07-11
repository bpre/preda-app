<?php

namespace App\Jobs;

use App\Models\LetterNotification;
use App\Services\LetterNotificationSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLetterNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const CONNECTION = 'database';

    public const QUEUE = 'letter-notifications';

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public string $notificationId,
        public int|string|null $sentBy = null,
    ) {
        $this->onConnection(self::CONNECTION);
        $this->onQueue(self::QUEUE);
    }

    public function handle(LetterNotificationSender $sender): void
    {
        $notification = LetterNotification::query()->find($this->notificationId);

        if (! $notification instanceof LetterNotification) {
            return;
        }

        $sender->send($notification, $this->sentBy);
    }
}
