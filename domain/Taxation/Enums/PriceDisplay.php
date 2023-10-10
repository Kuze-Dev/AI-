<?php

declare(strict_types=1);

namespace Domain\Taxation\Enums;

enum PriceDisplay: string
{
    case INCLUSIVE = 'inclusive';
    case EXCLUSIVE = 'exclusive';
}
