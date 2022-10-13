<?php

declare(strict_types=1);

namespace App\Enums\Api;

enum NotificationType: string
{
    case INFORMATION = 'information';
    case ERROR = 'error';
    case WARNING = 'warning';
}
