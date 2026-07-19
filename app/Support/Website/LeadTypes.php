<?php

namespace App\Support\Website;

class LeadTypes
{
    public const FORM = 'form';
    public const EMAIL = 'email';
    public const PHONE = 'phone';

    public const OPTIONS = [
        self::FORM => 'Formularz',
        self::EMAIL => 'E-mail',
        self::PHONE => 'Telefon',
    ];

    public static function options(): array
    {
        return self::OPTIONS;
    }

    public static function filterOptions(): array
    {
        return [
            'all' => 'Wszystkie',
            ...self::OPTIONS,
        ];
    }

    public static function default(): string
    {
        return self::FORM;
    }

    public static function isValid(?string $type): bool
    {
        return array_key_exists((string) $type, self::OPTIONS);
    }

    public static function normalize(?string $type): string
    {
        return self::isValid($type) ? (string) $type : self::default();
    }

    public static function label(?string $type): ?string
    {
        return self::OPTIONS[$type] ?? null;
    }
}
