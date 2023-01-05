<?php

declare(strict_types=1);

namespace Domain\Collection\Enums;

enum PublishBehavior: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case UNLISTED = 'unlisted';
}
