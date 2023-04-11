<?php

declare(strict_types=1);

namespace Domain\Content\Enums;

enum PublishBehavior: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case UNLISTED = 'unlisted';
}
