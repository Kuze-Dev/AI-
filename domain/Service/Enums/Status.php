<?php

declare(strict_types=1);

namespace Domain\Service\Enums;

enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
