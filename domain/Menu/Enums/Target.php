<?php

declare(strict_types=1);

namespace Domain\Menu\Enums;

use InvalidArgumentException;

enum Target: string
{
    case blank = '_blank';
    case self = '_self';
    case parent = '_parent';
    case top = '_top';

    public function getTargetDataCalss(): string
    {
        return match ($this) {
            self::blank => '_blank',
            self::self => '_self',
            self::parent => '_parent',
            self::top => '_top',
            default => throw new InvalidArgumentException("`TargetData` class for `{$this->value}` is not specified.")
        };
    }
}
