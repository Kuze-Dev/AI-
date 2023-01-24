<?php

declare(strict_types=1);

namespace Domain\Menu\Enums;

// TODO: php-cs-fixer lowercase_static_reference rule should ignore cases in enum
enum Target: string
{
    case BLANK = '_blank';
    case self = '_self';
    case parent = '_parent';
    case TOP = '_top';
}
