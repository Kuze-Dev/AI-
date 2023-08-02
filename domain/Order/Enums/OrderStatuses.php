<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum OrderStatuses: string
{
        // case PROCESSING = 'processing';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case FULFILLED = 'fulfilled';
    case FORPAYMENT = 'for_payment';
}
