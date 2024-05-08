<?php

namespace App\Enum;

enum UserRoleEnum: int
{
    case USER = 0;
    case ADMIN = 1;
    case FACILITATOR = 2;
    case DESIGNER = 3;

    public static function getValues() : array
    {
        return [
            UserRoleEnum::USER->value,
            UserRoleEnum::ADMIN->value,
            UserRoleEnum::FACILITATOR->value,
            UserRoleEnum::DESIGNER->value,
        ];
    }
}

// function to get values


