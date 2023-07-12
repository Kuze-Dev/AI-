<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum OrderResult
{
    case SUCCESS;
    case FAILED;
}
