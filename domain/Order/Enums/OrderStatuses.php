<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum OrderStatuses: string
{
    case PENDING = 'Pending';
    case CANCELLED = 'Cancelled';
    case REFUNDED = 'Refunded';
    case PACKED = 'Packed';
    case SHIPPED = 'Shipped';
    case DELIVERED = 'Delivered';
    case FULFILLED = 'Fulfilled';
}
