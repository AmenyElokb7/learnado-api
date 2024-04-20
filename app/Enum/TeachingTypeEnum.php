<?php

namespace App\Enum;

enum TeachingTypeEnum: int
{
    case ONLINE = 1;
    case ON_A_PLACE = 2;

    public static function tryFromName(string $name): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->name === strtoupper($name)) {
                return $case;
            }
        }
        return null;
    }

}

