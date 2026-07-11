<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\LetterNotification;

class Letter extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string',
        'files' => 'array',
        'files_names' => 'array',
        'recipientes' => 'array'
    ];
    protected $keyType = 'string';
    public $incrementing = false;

    public $fillable = ['type', 'label', 'date', 'sender', 'matter_id', 'files', 'files_names', 'description'];

    const TYP = [
        'in' => 'Przychodząca',
        'out' => 'Wychodząca'
    ];

    protected static function booted(): void
    {
        static::created(function (Letter $letter) {
            $letter->loadMissing(['matter', 'notifications']);
            $letter->syncNotificationsForRecipients();
        });

        static::updated(function (Letter $letter) {
            if ($letter->wasChanged('matter_id')) {
                $letter->loadMissing(['matter', 'notifications']);
                $letter->syncNotificationsForRecipients();

                return;
            }

            if ($letter->wasChanged('files')) {
                $letter->syncPendingNotificationsAttachments();
            }
        });
    }

    // RELACJE

    public function hasAnyRelation()
    {
        return $this->contact_letter_neostamp()->exists();

    }

    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(Contact::class, 'sender_id', 'id');
    }

    public function users()
    {
        return $this->hasManyThrough(MatterUser::class, Letter::class, 'id', 'matter_id');
    }

     // kontakty przypisane do korespondencji
     public function contactLetter(): HasMany
     {
         return $this->hasMany(ContactLetter::class)->with('contact');
     }

     public function contact_letter_neostamp(): HasMany
     {
         return $this->hasMany(ContactLetter::class)->with('neostamp');
     }

    public function recipients()
    {
        return $this->belongsToMany(Contact::class);
    }

    public function scopeMine($query) {
        $myMatters = Matter::where('lawyer_id', auth()->user()->id)->pluck('id');
        return $query->whereIn('matter_id', $myMatters);
    }
    public function notifications(): HasMany
    {
        return $this->hasMany(LetterNotification::class, 'letter_id', 'id');
    }

    public function syncNotificationsForRecipients(): void
    {
        if (! $this->matter) {
            return;
        }

        $allAttachments = is_array($this->files) ? array_values(array_filter($this->files)) : [];
        $hasAttachments = count($allAttachments) > 0;

        $this->notifications()
            ->whereIn('status', [
                LetterNotification::STATUS_PENDING,
                LetterNotification::STATUS_DRAFT,
                LetterNotification::STATUS_MISSING_RECIPIENT,
            ])
            ->delete();

        $recipients = $this->matter
            ->contacts()
            ->wherePivot('receives_notifications', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->get();

        if ($recipients->isEmpty()) {
            LetterNotification::create([
                'letter_id' => $this->id,
                'contact_id' => null,
                'status' => LetterNotification::STATUS_MISSING_RECIPIENT,
                'recipient_email' => null,
                'with_attachments' => $hasAttachments,
                'selected_attachments' => $allAttachments,
            ]);

            return;
        }

        foreach ($recipients as $contact) {
            LetterNotification::firstOrCreate(
                [
                    'letter_id' => $this->id,
                    'contact_id' => $contact->id,
                ],
                [
                    'status' => LetterNotification::STATUS_PENDING,
                    'recipient_email' => $contact->email,
                    'with_attachments' => $hasAttachments,
                    'selected_attachments' => $allAttachments,
                ]
            );
        }
    }

    public function syncPendingNotificationsAttachments(): void
    {
        $allAttachments = is_array($this->files) ? array_values(array_filter($this->files)) : [];
        $hasAttachments = count($allAttachments) > 0;

        $this->notifications()
            ->whereIn('status', [
                LetterNotification::STATUS_PENDING,
                LetterNotification::STATUS_MISSING_RECIPIENT,
            ])
            ->get()
            ->each(function (LetterNotification $notification) use ($allAttachments, $hasAttachments) {
                $notification->update([
                    'with_attachments' => $hasAttachments,
                    'selected_attachments' => $allAttachments,
                ]);
            });
    }

}
