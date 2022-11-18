<?php

declare(strict_types=1);

namespace Domain\Page\Enums;

use Illuminate\Support\Str;

enum PageBehavior: string
{
    case PUBLIC = 'public';
    case UNLISTED = 'unlisted';
    case HIDDEN = 'hidden';

    public function label(): string
    {
        return Str::headline($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::PUBLIC => 'success',
            self::UNLISTED => 'danger',
            self::HIDDEN => 'warning',
        };
    }
}
