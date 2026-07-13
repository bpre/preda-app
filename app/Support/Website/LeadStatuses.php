<?php

namespace App\Support\Website;

class LeadStatuses
{
    public const NEW = 'Nowy lead';
    public const QUALIFIED = 'Zakwalifikowany';
    public const AUTOMATICALLY_QUALIFIED = 'Zakwalifikowany automatycznie';
    public const REJECTED = 'Odrzucony';

    public const REASON_SPAM = 'spam';
    public const REASON_DUPLICATE = 'duplicate';
    public const REASON_NOT_PROMISING = 'not_promising';
    public const REASON_OUT_OF_SCOPE = 'out_of_scope';
    public const REASON_NO_CONTACT = 'no_contact';
    public const REASON_OTHER = 'other';

    public const OPTIONS = [
        self::NEW => self::NEW,
        self::QUALIFIED => self::QUALIFIED,
        self::AUTOMATICALLY_QUALIFIED => self::AUTOMATICALLY_QUALIFIED,
        self::REJECTED => self::REJECTED,
    ];

    public const REJECTION_REASONS = [
        self::REASON_SPAM => 'Spam',
        self::REASON_DUPLICATE => 'Duplikat',
        self::REASON_NOT_PROMISING => 'Nierokujący',
        self::REASON_OUT_OF_SCOPE => 'Poza zakresem kancelarii',
        self::REASON_NO_CONTACT => 'Brak kontaktu',
        self::REASON_OTHER => 'Inne',
    ];

    public static function options(): array
    {
        return self::OPTIONS;
    }

    public static function default(): string
    {
        return self::NEW;
    }

    public static function isValid(?string $status): bool
    {
        return array_key_exists((string) $status, self::OPTIONS);
    }

    public static function normalize(?string $status): string
    {
        return self::isValid($status) ? (string) $status : self::default();
    }

    public static function color(?string $status): string
    {
        return match ($status) {
            self::NEW => 'gray',
            self::QUALIFIED,
            self::AUTOMATICALLY_QUALIFIED => 'success',
            self::REJECTED => 'danger',
            default => 'primary',
        };
    }

    public static function rejectionReasons(): array
    {
        return self::REJECTION_REASONS;
    }

    public static function rejectionReasonLabel(?string $reason): ?string
    {
        return self::REJECTION_REASONS[$reason] ?? null;
    }

    public static function qualifiedStatus(bool $automatic = false): string
    {
        return $automatic ? self::AUTOMATICALLY_QUALIFIED : self::QUALIFIED;
    }

    public static function isRejected(?string $status): bool
    {
        return $status === self::REJECTED;
    }
}
