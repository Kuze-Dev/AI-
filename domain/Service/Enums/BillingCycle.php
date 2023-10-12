<?php

declare(strict_types=1);

namespace Domain\Service\Enums;

enum BillingCycle: string
{
    case DAILY = 'daily';

    case MONTHLY = 'monthly';

    case YEARLY = 'yearly';
}
