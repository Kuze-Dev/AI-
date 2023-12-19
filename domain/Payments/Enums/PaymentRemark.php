<?php

declare(strict_types=1);

namespace Domain\Payments\Enums;

enum PaymentRemark: string
{
    case APPROVED = 'approved';
    case DECLINED = 'declined';
}
