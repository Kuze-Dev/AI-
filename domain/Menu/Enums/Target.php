<?php

declare(strict_types=1);

namespace Domain\Menu\Enums;

enum Target: string
{
    case BLANK = '_blank';
    case self = '_self';
    case parent = '_parent';
    case TOP = '_top';
}
