<?php

namespace App\Services;

use Throwable;
use App\Mail\LetterNotificationMail;
use App\Models\LetterNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LetterNotificationSender
{
    public function send(LetterNotification $notification, int|string|null $sentBy = null): bool
    {
        return Cache::lock('letter-notification-send:' . $notification->getKey(), 120)->get(function () use ($notification, $sentBy) {
            $notification->refresh();
            $notification->loadMissing(['letter', 'contact', 'preparedBy']);

            if (! in_array($notification->status, [
                LetterNotification::STATUS_QUEUED,
                LetterNotification::STATUS_SENDING,
            ], true)) {
                return false;
            }

            if (! filled($notification->prepared_by) && filled($sentBy)) {
                $notification->update([
                    'prepared_by' => $sentBy,
                ]);

                $notification->refresh();
                $notification->loadMissing(['letter', 'contact', 'preparedBy']);
            }

            $errorMessage = $this->getValidationError($notification);

            if ($errorMessage !== null) {
                $notification->update([
                    'status' => LetterNotification::STATUS_FAILED,
                    'error_message' => $errorMessage,
                ]);

                return false;
            }

            try {
                Mail::to($notification->recipient_email)
                    ->send(new LetterNotificationMail($notification));

                $notification->update([
                    'status' => LetterNotification::STATUS_SENT,
                    'sent_at' => now(),
                    'sent_by' => $sentBy,
                    'error_message' => null,
                ]);

                return true;
            } catch (Throwable $e) {
                $notification->update([
                    'status' => LetterNotification::STATUS_FAILED,
                    'error_message' => $e->getMessage(),
                ]);

                return false;
            }
        }) ?? false;
    }

    protected function getValidationError(LetterNotification $notification): ?string
    {
        if (! filled($notification->recipient_email)) {
            return 'Brak adresu e-mail odbiorcy.';
        }

        if (! filled($notification->subject) || ! filled($notification->message)) {
            return 'Brak tematu lub treści wiadomości.';
        }

        $selectedAttachments = is_array($notification->selected_attachments)
            ? array_values(array_filter($notification->selected_attachments))
            : [];

        if (! $notification->with_attachments || count($selectedAttachments) === 0) {
            return null;
        }

        $allowedAttachments = is_array($notification->letter?->files)
            ? array_values(array_filter($notification->letter->files))
            : [];

        $selectedAttachments = array_values(array_intersect($selectedAttachments, $allowedAttachments));
        $totalBytes = $this->getSelectedAttachmentsTotalBytes($selectedAttachments);
        $maxBytes = LetterNotification::MAX_ATTACHMENTS_SIZE_MB * 1024 * 1024;

        if ($totalBytes <= $maxBytes) {
            return null;
        }

        $totalMb = $this->formatBytesToMb($totalBytes);

        return "Łączny rozmiar wybranych załączników wynosi {$totalMb} MB i przekracza " . LetterNotification::MAX_ATTACHMENTS_SIZE_MB . ' MB.';
    }

    protected function getSelectedAttachmentsTotalBytes(array $attachments): int
    {
        $totalBytes = 0;

        foreach ($attachments as $path) {
            $path = (string) $path;

            try {
                if (Storage::disk('local')->exists($path)) {
                    $totalBytes += (int) Storage::disk('local')->size($path);
                }
            } catch (Throwable $e) {
                // Pomijamy pojedynczy błąd odczytu pliku.
            }
        }

        return $totalBytes;
    }

    protected function formatBytesToMb(int $bytes): float
    {
        return round($bytes / 1024 / 1024, 2);
    }
}
