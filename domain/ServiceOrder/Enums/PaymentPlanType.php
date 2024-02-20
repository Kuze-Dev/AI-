<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum PaymentPlanType: string implements HasLabel
{
    case FULL = 'full';
    case MILESTONE = 'milestone';

    public function getLabel(): ?string
    {
        return Str::headline($this->value);
    }
}
