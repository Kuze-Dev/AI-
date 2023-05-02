<?php

declare(strict_types=1);

namespace Domain\Page\Enums;

enum Visibility: string
{
    case PUBLIC = 'public';
    case GUEST = 'guest';
    case AUTHENTICATED = 'authenticated';
}
