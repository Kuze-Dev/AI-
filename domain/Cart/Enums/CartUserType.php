<?php

declare(strict_types=1);

namespace Domain\Cart\Enums;

enum CartUserType: string
{
    case AUTHENTICATED = 'authenticated';
    case GUEST = 'guest';
}
