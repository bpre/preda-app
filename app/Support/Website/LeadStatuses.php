<?php

namespace App\Support\Website;

class LeadStatuses
{
    public const NEW = 'Nowy lead';

    public const OPTIONS = [
        self::NEW => self::NEW,
        'Wysłano potwierdzenie kwalifikacji sprawy' => 'Wysłano potwierdzenie kwalifikacji sprawy',
        'Wysłano analizę umowy' => 'Wysłano analizę umowy',
        'Follow-up (po wysłaniu analizy)' => 'Follow-up (po wysłaniu analizy)',
        'Wysłano ofertę' => 'Wysłano ofertę',
        'Follow-up (po wysłaniu oferty, przed spotkaniem)' => 'Follow-up (po wysłaniu oferty, przed spotkaniem)',
        'Umówiono spotkanie' => 'Umówiono spotkanie',
        'Przeprowadzono spotkanie' => 'Przeprowadzono spotkanie',
        'Przedstawiono ofertę (podczas spotkania)' => 'Przedstawiono ofertę (podczas spotkania)',
        'Przygotowano wniosek o wydanie zaświadczenia' => 'Przygotowano wniosek o wydanie zaświadczenia',
        'Follow-up (po spotkaniu)' => 'Follow-up (po spotkaniu)',
        'Potwierdzono chęć zlecenia sprawy' => 'Potwierdzono chęć zlecenia sprawy',
        'Zlecono prowadzenie sprawy' => 'Zlecono prowadzenie sprawy',
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
        if (str_starts_with((string) $status, 'Follow-up')) {
            return 'warning';
        }

        return match ($status) {
            self::NEW => 'gray',
            'Zlecono prowadzenie sprawy',
            'Potwierdzono chęć zlecenia sprawy' => 'success',
            'Umówiono spotkanie',
            'Przeprowadzono spotkanie',
            'Przedstawiono ofertę (podczas spotkania)' => 'info',
            default => 'primary',
        };
    }
}
