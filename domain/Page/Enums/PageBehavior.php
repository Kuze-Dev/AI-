<?php

declare(strict_types=1);

namespace Domain\Page\Enums;

enum PageBehavior: string
{
    case PUBLIC = 'public';
    case UNLISTED = 'unlisted';
    case HIDDEN = 'hidden';
}
