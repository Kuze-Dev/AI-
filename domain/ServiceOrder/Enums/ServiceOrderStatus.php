<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum ServiceOrderStatus: string
{
    case PROCESSING = 'processing';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case FULFILLED = 'fulfilled';
    case ON_HOLD = 'on_hold';
    case SUBSCRIBED = 'subscribed';
    case UNSUBSCRIBED = 'unsubscribed';
    case EXPIRED = 'expired';
}
