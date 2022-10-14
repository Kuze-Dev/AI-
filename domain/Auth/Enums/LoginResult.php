<?php

declare(strict_types=1);

namespace Domain\Auth\Enums;

enum LoginResult
{
    case TWO_FACTOR_REQUIRED;
    case SUCCESS;
}
