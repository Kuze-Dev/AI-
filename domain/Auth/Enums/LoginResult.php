<?php

declare(strict_types=1);

namespace Domain\Auth\Enums;

enum LoginResult: string
{
    case TWO_FACTOR_REQUIRED = 'two_factor_required';
    case SUCCESS = 'success';
}
