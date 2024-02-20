<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum PaymentPlanValue: string implements HasLabel
{
    case FIXED = 'fixed';
    case PERCENT = 'percent';

    public function getLabel(): ?string
    {
        return Str::headline($this->value);
    }
}
