<?php

declare(strict_types=1);

namespace Domain\Customer\Enums;

enum CustomerEvent: string
{
    case IMPORTED = 'imported';
    case REGISTERED = 'registered';
}
