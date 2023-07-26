<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum OrderNotifications: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
}
