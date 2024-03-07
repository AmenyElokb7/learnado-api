<?php

namespace App\Enum;

enum UserRoleEnum: int
{
    case USER = 0;
    case ADMIN = 1;
    case CONCEPTEUR = 3;
    case FACILITATOR = 4;
}
