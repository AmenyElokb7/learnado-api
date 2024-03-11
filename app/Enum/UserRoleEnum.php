<?php

namespace App\Enum;

enum UserRoleEnum: int
{
    case USER = 0;
    case ADMIN = 1;
    case FACILITATOR = 2;
    case DESIGNER = 3;
}
