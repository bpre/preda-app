<?php

namespace App\Enums\Website;

enum FAQPrefixes: string
{
    case HOMEPAGE = 'homepage';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::HOMEPAGE => 'Strona główna',
        };
    }
}
