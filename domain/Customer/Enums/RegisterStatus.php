<?php

namespace Domain\Customer\Enums;

enum RegisterStatus: string
{
    case UNREGISTERED = 'unregistered';
    case INVITED = 'invited';
    case REGISTERED = 'registered';
}
