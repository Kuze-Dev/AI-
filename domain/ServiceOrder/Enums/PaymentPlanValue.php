<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum PaymentPlanValue: string
{
    case FIXED = 'fixed';
    case PERCENT = 'percent';
}
