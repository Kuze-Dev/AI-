<?php

declare(strict_types=1);

namespace Domain\Content\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum PublishBehavior: string implements HasLabel
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case UNLISTED = 'unlisted';

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }
}
