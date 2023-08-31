<?php

declare(strict_types=1);

namespace Domain\Product\Enums;

enum Taxonomy: string
{
    case BRAND = 'brand';
    case CATEGORIES = 'categories';
}
