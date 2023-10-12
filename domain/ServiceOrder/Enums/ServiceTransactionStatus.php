<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum ServiceTransactionStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case UNPAID = 'un_paid';
    case REFUNDED = 'refunded';
}
