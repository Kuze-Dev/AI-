<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum OrderMailStatus: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
}
