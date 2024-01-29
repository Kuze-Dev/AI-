<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum ServiceOrderStatus: string
{
    case INPROGRESS = 'in_progress';
    case FORPAYMENT = 'for_payment';
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case CLOSED = 'closed';
    case FOR_APPROVAL = 'for_approval';
}
