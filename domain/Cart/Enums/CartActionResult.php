<?php

declare(strict_types=1);

namespace Domain\Cart\Enums;

enum CartActionResult
{
    case SUCCESS;
    case FAILED;
}
