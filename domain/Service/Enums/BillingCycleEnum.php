<?php

declare(strict_types=1);

namespace Domain\Service\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum BillingCycleEnum: string implements HasLabel
{
    case DAILY = 'daily';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }
}
