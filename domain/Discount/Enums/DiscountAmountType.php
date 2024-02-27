<?php

declare(strict_types=1);

namespace Domain\Discount\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum DiscountAmountType: string implements HasLabel
{
    case FIXED_VALUE = 'fixed_value';
    case PERCENTAGE = 'percentage';

    public function getLabel(): ?string
    {
        return Str::headline($this->value);
    }
}
