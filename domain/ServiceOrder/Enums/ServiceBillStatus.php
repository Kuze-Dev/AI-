<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum ServiceBillStatus: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case FOR_APPROVAL = 'for_approval';
}
