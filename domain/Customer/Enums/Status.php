<?php

declare(strict_types=1);

namespace Domain\Customer\Enums;

enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BANNED = 'banned';
}
