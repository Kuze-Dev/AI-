<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum ServiceBillStatus: string
{
    case PAID = 'paid';
}
