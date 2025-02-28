<?php

declare(strict_types=1);

namespace Domain\Product\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

enum Taxonomy: string implements HasLabel
{
    case BRAND = 'brand';
    case CATEGORIES = 'categories';

    public function getLabel(): string
    {
        return Str::headline($this->value);
    }
}
