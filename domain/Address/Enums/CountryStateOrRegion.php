<?php

declare(strict_types=1);

namespace Domain\Address\Enums;

enum CountryStateOrRegion: string
{
    case STATE = 'state';
    case REGION = 'region';
}
