<?php

declare(strict_types=1);

namespace Domain\Service\Enums;

enum RecurringPayment: string
{
    case MONTHLY = 'Monthly';

    case YEARLY = 'Yearly';
}
