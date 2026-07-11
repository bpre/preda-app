<?php

namespace App\Enums\Website;

enum ContactCategories: string
{
    case SEDZIA = 'Sędzia';
    case SAD = 'Sąd';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SEDZIA => 'Sędzia',
            self::SAD => 'Sąd'
        };
    }
}
