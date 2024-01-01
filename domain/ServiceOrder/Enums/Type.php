<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum Type: string
{
    case SERVICE_ORDER = 'serviceOrder';
    case SERVICE_BILL = 'serviceBill';
}
