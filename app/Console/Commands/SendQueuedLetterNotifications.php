<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LetterNotification;
use App\Services\LetterNotificationSender;

class SendQueuedLetterNotifications extends Command
{
    protected $signature = 'letters:send-queued-notifications';

    protected $description = 'Wysyła zakolejkowane powiadomienia o pismach';

    public function handle(LetterNotificationSender $sender): int
    {
        $sent = 0;
        $failed = 0;

        LetterNotification::query()
            ->whereIn('status', [
                LetterNotification::STATUS_QUEUED,
                LetterNotification::STATUS_SENDING,
            ])
            ->with(['letter', 'contact'])
            // Iterate by primary key instead of offset pagination because the sender
            // changes statuses during processing, which would otherwise skip records.
            ->chunkById(100, function ($notifications) use ($sender, &$sent, &$failed) {
                foreach ($notifications as $notification) {
                    if ($sender->send($notification)) {
                        $sent++;
                    } else {
                        $failed++;
                    }
                }
            }, 'id', 'id');

        $this->info("Wysłano: {$sent}");
        $this->warn("Błędy: {$failed}");

        return self::SUCCESS;
    }
}
