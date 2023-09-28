<?php

declare(strict_types=1);

namespace Domain\Service\Enums;

enum BillingCycle: string
{
    case DAILY = 'Daily';

    case MONTHLY = 'Monthly';

    case YEARLY = 'Yearly';
}
