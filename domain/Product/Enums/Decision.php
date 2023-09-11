<?php

declare(strict_types=1);

namespace Domain\Product\Enums;

enum Decision: string
{
    case YES = 'yes';
    case NO = 'no';
}
