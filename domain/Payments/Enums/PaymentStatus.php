<?php

declare(strict_types=1);

namespace Domain\Payments\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCEL = 'cancel';
    case VOID = 'void';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially refunded';
}
