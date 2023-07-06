<?php

declare(strict_types=1);

namespace Domain\Address\Enums;

enum AddressLabelAs: string
{
    case HOME = 'home';
    case OFFICE = 'office';
}
