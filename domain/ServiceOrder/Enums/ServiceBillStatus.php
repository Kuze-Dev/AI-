<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum ServiceBillStatus: string implements HasColor, HasLabel
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case FOR_APPROVAL = 'for_approval';

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PAID => 'success',
            self::PENDING => 'warning',
            default => 'secondary',
        };
    }
}
