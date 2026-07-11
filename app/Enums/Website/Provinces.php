<?php

namespace App\Enums\Website;

enum Provinces: string
{
    case DOLNOSLASKIE = 'dolnośląskie';
    case WIELKOPOLSKIE = 'wielkopolskie';
    case LUBUSKIE = 'lubuskie';

    public function getProvince(): string
    {
        return match($this) {
            self::DOLNOSLASKIE => 'Dolnośląskie',
            self::WIELKOPOLSKIE => 'Wielkopolskie',
            self::LUBUSKIE => 'Lubuskie'
        };
    }

    public function getProvinceId(): string
    {
        return match($this) {
            self::DOLNOSLASKIE => 1,
            self::WIELKOPOLSKIE => 2,
            self::LUBUSKIE => 3
        };
    }

    public static function all(): array
    {
        return self::cases();
    }

}
