<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

enum PaymentPlanType: string
{
    case FULL = 'full';
    case MILESTONE = 'milestone';
}
