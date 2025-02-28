<?php

declare(strict_types=1);

namespace Domain\Discount\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum DiscountConditionType: string implements HasLabel
{
    case ORDER_SUB_TOTAL = 'order_sub_total';
    case DELIVERY_FEE = 'delivery_fee';

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }
}
