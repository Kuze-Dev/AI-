<?php

declare(strict_types=1);

namespace Domain\Auth\Enums;

enum EmailVerificationType: string
{
    case LINK = 'link';
    case OTP = 'otp';
}
