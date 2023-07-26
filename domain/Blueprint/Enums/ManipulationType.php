<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

enum ManipulationType: string
{
    case WIDTH = 'width';
    case HEIGHT = 'height';
    case FIT = 'fit';
    case CROP = 'crop';
}
