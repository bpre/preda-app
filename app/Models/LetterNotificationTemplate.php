<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LetterNotificationTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean',
        'sort' => 'integer',
    ];

    protected $fillable = [
        'name',
        'letter_type',
        'subject',
        'message',
        'is_active',
        'sort',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public const LETTER_TYPES = [
        'in' => 'Przychodzące',
        'out' => 'Wychodzące',
    ];

    public const AVAILABLE_PLACEHOLDERS = [
        '{{pani_pana}}' => 'Pani / Pana',
        '{{nazwa_pisma}}' => 'Nazwa pisma',
        '{{data_doreczenia_pisma}}' => 'Data pisma',
        '{{nazwa_sprawy}}' => 'Identyfikator sprawy',
    ];

    protected const LEGACY_PLACEHOLDER_ALIASES = [
        '{{case_phrase}}' => '{{pani_pana}}',
        '{{letter_label}}' => '{{nazwa_pisma}}',
        '{{letter_date}}' => '{{data_doreczenia_pisma}}',
        '{{matter_label}}' => '{{nazwa_sprawy}}',
    ];

    public function renderForNotification(LetterNotification $notification): array
    {
        $letter = $notification->letter;
        $contact = $notification->contact;
        $matter = $letter?->matter;

        $replacements = [
            '{{pani_pana}}' => static::resolveCasePhrase($contact),
            '{{nazwa_pisma}}' => $letter?->label ?? '',
            '{{data_doreczenia_pisma}}' => $letter?->date ? Carbon::parse($letter->date)->format('d.m.Y') : '',
            '{{nazwa_sprawy}}' => $matter?->label ?? '',
        ];

        foreach (self::LEGACY_PLACEHOLDER_ALIASES as $legacyPlaceholder => $newPlaceholder) {
            $replacements[$legacyPlaceholder] = $replacements[$newPlaceholder] ?? '';
        }

        return [
            'subject' => strtr($this->subject, $replacements),
            'message' => strtr($this->message, $replacements),
        ];
    }

    protected static function resolveCasePhrase(?Contact $contact): string
    {
        if (! $contact) {
            return 'sprawie';
        }

        return match ($contact->sex) {
            'K' => 'Pani',
            'M' => 'Pana',
            default => '...',
        };
    }
}
