<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

enum ManipulationFit: string
{
    case FIT_CONTAIN = 'contain';
    case FIT_MAX = 'max';
    case FIT_FILL = 'fill';
    case FIT_FILL_MAX = 'fill-max';
    case FIT_STRETCH = 'stretch';
    case FIT_CROP = 'crop';

}
