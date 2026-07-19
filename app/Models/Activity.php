<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory, HasUuids;

    public const TYPE_NOTE = 'note';

    public const TYPE_PHONE_CALL = 'phone_call';

    public const TYPE_SMS = 'sms';

    public const TYPE_EMAIL = 'email';

    public const TYPE_MEETING = 'meeting';

    public const TYPE_POST_MEETING_NOTE = 'post_meeting_note';

    public const TYPE_OTHER = 'other';

    public const TYPE_LABELS = [
        self::TYPE_NOTE => 'Notatka',
        self::TYPE_PHONE_CALL => 'Rozmowa telefoniczna',
        self::TYPE_SMS => 'SMS',
        self::TYPE_EMAIL => 'E-mail',
        self::TYPE_MEETING => 'Spotkanie',
        self::TYPE_POST_MEETING_NOTE => 'Notatka po spotkaniu',
        self::TYPE_OTHER => 'Inne',
    ];

    protected $fillable = [
        'date',
        'type',
        'description',
        'is_visible_for_client',
        'visible_for_client_from',
        'matter_id',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_visible_for_client' => 'boolean',
        'visible_for_client_from' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Activity $activity): void {
            $activity->type ??= self::TYPE_NOTE;
            $activity->created_by ??= auth()->id();
        });
    }

    public static function typeLabel(?string $type): string
    {
        return self::TYPE_LABELS[$type] ?? self::TYPE_LABELS[self::TYPE_NOTE];
    }

    public function matter(): BelongsTo
    {
        return $this->belongsTo(Matter::class, 'matter_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readReceipts(): HasMany
    {
        return $this->hasMany(ActivityRead::class, 'activity_id');
    }

    public function isReadBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ((int) $this->created_by === (int) $user->getKey()) {
            return true;
        }

        return $this->readReceipts()
            ->where('user_id', $user->getKey())
            ->exists();
    }

    public function markAsReadBy(?User $user): void
    {
        if (! $user) {
            return;
        }

        $this->readReceipts()->updateOrCreate(
            ['user_id' => $user->getKey()],
            ['read_at' => now()],
        );
    }
}
