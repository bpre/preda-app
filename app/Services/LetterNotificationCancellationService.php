<?php

namespace App\Services;

use App\Models\LetterNotification;

class LetterNotificationCancellationService
{
    public function cancelForMatterContact(string $matterId, string $contactId): int
    {
        if (! filled($matterId) || ! filled($contactId)) {
            return 0;
        }

        return LetterNotification::query()
            ->where('contact_id', $contactId)
            ->whereIn('status', LetterNotification::AUTO_CANCELLABLE_STATUSES)
            ->whereHas('letter', fn ($query) => $query->where('matter_id', $matterId))
            ->update([
                'status' => LetterNotification::STATUS_CANCELLED,
                'error_message' => null,
            ]);
    }
}
