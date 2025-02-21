<?php

declare(strict_types=1);

namespace Domain\Tier\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum TierApprovalStatus: string implements HasLabel
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }
}
