<?php

declare(strict_types=1);

namespace Domain\Customer\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum Status: string implements HasLabel
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BANNED = 'banned';

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }

    public function isAllowedInvite(): bool
    {
        return $this === self::INACTIVE;
    }
}
