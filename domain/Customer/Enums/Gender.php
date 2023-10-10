<?php

declare(strict_types=1);

namespace Domain\Customer\Enums;

enum Gender: string
{
    case FEMALE = 'female';
    case MALE = 'male';
}
