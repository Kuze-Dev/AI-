<?php

declare(strict_types=1);

namespace Domain\Menu\Enums;

enum NodeType: string
{
    case URL = 'url';
    case RESOURCE = 'resource';
}
