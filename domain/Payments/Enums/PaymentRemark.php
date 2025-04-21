<?php

declare(strict_types=1);

namespace Domain\Payments\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum PaymentRemark: string implements HasColor, HasIcon, HasLabel
{
    case APPROVED = 'approved';
    case DECLINED = 'declined';

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::APPROVED => 'success',
            self::DECLINED => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::APPROVED => 'heroicon-o-check-circle',
            self::DECLINED => 'heroicon-o-x-circle',
        };
    }
}
