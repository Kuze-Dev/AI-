<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum OrderUserType: string
{
    case REGISTERED = 'registered';
    case GUEST = 'guest';
}
