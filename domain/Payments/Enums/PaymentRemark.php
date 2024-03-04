<?php

declare(strict_types=1);

namespace Domain\Payments\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum PaymentRemark: string implements HasLabel
{
    case APPROVED = 'approved';
    case DECLINED = 'declined';

    public function getLabel(): ?string
    {
        return Str::headline($this->value);
    }
}
