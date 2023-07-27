<?php

namespace Domain\Customer\Enums;

enum EmailVerifiedType: string
{
    case LINK = 'link';
    case OTP = 'otp';
}
