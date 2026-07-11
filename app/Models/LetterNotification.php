<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LetterNotification extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string',
        'letter_id' => 'string',
        'contact_id' => 'string',
        'with_attachments' => 'boolean',
        'ignored_at' => 'datetime',
        'sent_at' => 'datetime',
        'template_id' => 'string',
        'selected_attachments' => 'array',
    ];

    protected $fillable = [
        'letter_id',
        'contact_id',
        'status',
        'recipient_email',
        'subject',
        'message',
        'prepared_by',
        'with_attachments',
        'ignored_at',
        'ignored_by',
        'sent_at',
        'sent_by',
        'error_message',
        'template_id',
        'selected_attachments',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public const MAX_ATTACHMENTS_SIZE_MB = 20;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IGNORED = 'ignored';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_MISSING_RECIPIENT = 'missing_recipient';

    public const AUTO_CANCELLABLE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_DRAFT,
        self::STATUS_QUEUED,
        self::STATUS_FAILED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Nowe',
        self::STATUS_DRAFT => 'Szkic',
        self::STATUS_QUEUED => 'Do wysyłki',
        self::STATUS_SENDING => 'Wysyłam...',
        self::STATUS_SENT => 'Wysłane',
        self::STATUS_FAILED => 'Błąd',
        self::STATUS_IGNORED => 'Zignorowane',
        self::STATUS_CANCELLED => 'Anulowane',
        self::STATUS_MISSING_RECIPIENT => 'Brak odbiorcy',
    ];

    public function letter(): BelongsTo
    {
        return $this->belongsTo(Letter::class, 'letter_id', 'id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'id');
    }

    public function ignoredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ignored_by');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(LetterNotificationTemplate::class, 'template_id', 'id');
    }
}
