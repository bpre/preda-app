<?php

namespace App\Observers;

use App\Models\ContactMatter;
use App\Services\LetterNotificationCancellationService;
use Filament\Notifications\Notification;

class ContactMatterObserver
{
    protected static array $cancellationContexts = [];

    public function updating(ContactMatter $contactMatter): void
    {
        $context = $this->resolveUpdateCancellationContext($contactMatter);

        if ($context === null) {
            return;
        }

        self::$cancellationContexts[spl_object_id($contactMatter)] = $context;
    }

    public function updated(ContactMatter $contactMatter): void
    {
        $this->cancelNotificationsForStoredContext($contactMatter);
    }

    public function deleting(ContactMatter $contactMatter): void
    {
        self::$cancellationContexts[spl_object_id($contactMatter)] = [
            'matter_id' => (string) $contactMatter->matter_id,
            'contact_id' => (string) $contactMatter->contact_id,
        ];
    }

    public function deleted(ContactMatter $contactMatter): void
    {
        $this->cancelNotificationsForStoredContext($contactMatter);
    }

    protected function resolveUpdateCancellationContext(ContactMatter $contactMatter): ?array
    {
        $pairChanged = $contactMatter->isDirty('matter_id') || $contactMatter->isDirty('contact_id');
        $notificationsDisabled = $contactMatter->isDirty('receives_notifications') && ! $contactMatter->receives_notifications;

        if (! $pairChanged && ! $notificationsDisabled) {
            return null;
        }

        return [
            'matter_id' => (string) $contactMatter->getOriginal('matter_id'),
            'contact_id' => (string) $contactMatter->getOriginal('contact_id'),
        ];
    }

    protected function cancelNotificationsForStoredContext(ContactMatter $contactMatter): void
    {
        $context = $this->pullCancellationContext($contactMatter);

        if ($context === null) {
            return;
        }

        $cancelledCount = app(LetterNotificationCancellationService::class)
            ->cancelForMatterContact($context['matter_id'], $context['contact_id']);

        if ($cancelledCount === 0 || app()->runningInConsole()) {
            return;
        }

        Notification::make()
            ->title($this->buildNotificationTitle($cancelledCount))
            ->body('Klient został odpięty od sprawy lub wyłączono dla niego powiadomienia e-mail.')
            ->warning()
            ->send();
    }

    protected function pullCancellationContext(ContactMatter $contactMatter): ?array
    {
        $key = spl_object_id($contactMatter);
        $context = self::$cancellationContexts[$key] ?? null;

        unset(self::$cancellationContexts[$key]);

        return $context;
    }

    protected function buildNotificationTitle(int $cancelledCount): string
    {
        if ($cancelledCount === 1) {
            return 'Anulowano 1 niewysłane powiadomienie';
        }

        return 'Anulowano ' . $cancelledCount . ' niewysłanych powiadomień';
    }
}
