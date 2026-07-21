<?php

namespace App\Models;

use App\Models\Website\Lead as WebsiteLead;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailgunEvent extends Model
{
    use HasUuids;

    public const EVENT_LABELS = [
        'accepted' => 'Przyjęto',
        'delivered' => 'Dostarczono',
        'opened' => 'Otwarto',
        'clicked' => 'Kliknięto',
        'temporary_fail' => 'Błąd tymczasowy',
        'permanent_fail' => 'Błąd trwały',
        'failed' => 'Błąd',
        'rejected' => 'Odrzucono',
        'complained' => 'Oznaczono jako spam',
        'unsubscribed' => 'Wypisano',
    ];

    protected $fillable = [
        'mailgun_event_id',
        'payload_hash',
        'event',
        'domain',
        'recipient_email',
        'sender_email',
        'subject',
        'message_id',
        'mailgun_message_id',
        'url',
        'ip_address',
        'user_agent',
        'client_info',
        'tags',
        'user_variables',
        'crm_client_message_id',
        'matter_id',
        'website_lead_id',
        'letter_notification_id',
        'letter_id',
        'contact_id',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'client_info' => 'array',
        'tags' => 'array',
        'user_variables' => 'array',
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    public function crmClientMessage(): BelongsTo
    {
        return $this->belongsTo(CrmClientMessage::class, 'crm_client_message_id');
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class, 'matter_id');
    }

    public function websiteLead(): BelongsTo
    {
        return $this->belongsTo(WebsiteLead::class, 'website_lead_id');
    }

    public function letterNotification(): BelongsTo
    {
        return $this->belongsTo(LetterNotification::class, 'letter_notification_id');
    }

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'letter_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function eventLabel(): string
    {
        return self::labelFor($this->event);
    }

    public function eventColor(): string
    {
        return self::colorFor($this->event);
    }

    public static function labelFor(?string $event): string
    {
        if (! $event) {
            return 'Brak danych';
        }

        return self::EVENT_LABELS[$event] ?? $event;
    }

    public static function colorFor(?string $event): string
    {
        return match ($event) {
            'delivered' => 'success',
            'opened', 'clicked' => 'info',
            'accepted' => 'gray',
            'temporary_fail' => 'warning',
            'permanent_fail', 'failed', 'rejected', 'complained' => 'danger',
            'unsubscribed' => 'warning',
            default => 'gray',
        };
    }

    public static function priorityFor(?string $event): int
    {
        return match ($event) {
            'permanent_fail', 'failed', 'rejected', 'complained' => 90,
            'temporary_fail' => 80,
            'unsubscribed' => 70,
            'clicked' => 60,
            'opened' => 50,
            'delivered' => 40,
            'accepted' => 30,
            default => 0,
        };
    }

    /**
     * @param  iterable<self>  $events
     */
    public static function mostRelevantFrom(iterable $events): ?self
    {
        return collect($events)
            ->sortByDesc(fn (self $event): int => (self::priorityFor($event->event) * 1_000_000_000)
                + (int) ($event->occurred_at?->timestamp ?? $event->created_at?->timestamp ?? 0))
            ->first();
    }
}
