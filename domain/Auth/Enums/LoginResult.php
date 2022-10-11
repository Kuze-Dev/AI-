<?php

namespace Domain\Auth\Enums;

enum LoginResult: string
{
    case TWO_FACTOR_REQUIRED = 'two_factor_required';
    case SUCCESS = 'success';
}
