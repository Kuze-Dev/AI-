<?php

declare(strict_types=1);

namespace Domain\Taxation\Enums;

use Filament\Support\Contracts\HasLabel;

enum TaxZoneType: string implements HasLabel
{
    case COUNTRY = 'country';
    case STATE = 'state';

    public function getLabel(): string
    {
        return match ($this) {
            self::COUNTRY => trans('Limit by Countries'),
            self::STATE =>  trans('Limit by States/Provinces'),
        };
    }
}
