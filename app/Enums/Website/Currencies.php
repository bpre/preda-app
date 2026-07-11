<?php

namespace App\Enums\Website;

enum Currencies: string
{
    case CHF = 'CHF';
    case EUR = 'EUR';

    public static function all(): array
    {
        return self::cases();
    }

}
