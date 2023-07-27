<?php

namespace Domain\Customer\Enums;

enum EmailVerificationType: string
{
    case LINK = 'link';
    case OTP = 'otp';
}
