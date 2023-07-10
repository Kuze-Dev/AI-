<?php

declare(strict_types=1);

namespace Domain\Order\Enums;

enum PlaceOrderResult
{
    case SUCCESS;
    case FAILED;
}
