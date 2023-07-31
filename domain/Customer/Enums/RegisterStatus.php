<?php

declare(strict_types=1);

namespace Domain\Customer\Enums;

enum RegisterStatus: string
{
    case UNREGISTERED = 'unregistered';
    case INVITED = 'invited';
    case REGISTERED = 'registered';
}
