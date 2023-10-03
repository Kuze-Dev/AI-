<?php

declare(strict_types=1);

namespace Support\ApiCall\Enums;

enum ApiCallLogType: string
{
    case API = 'api';
    case ADMIN = 'admin';
    case WEB = 'web';
}
