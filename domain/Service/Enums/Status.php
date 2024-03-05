<?php

declare(strict_types=1);

namespace Domain\Service\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum Status: string implements HasLabel
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function getLabel(): ?string
    {
        return Str::headline($this->value);
    }
}
