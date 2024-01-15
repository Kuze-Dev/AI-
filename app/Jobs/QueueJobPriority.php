<?php

declare(strict_types=1);

namespace App\Jobs;

class QueueJobPriority
{
    final public const PRIORITIES = [
        //        self::HIGH,
        //        self::MEDIUM,
        //        self::LOW,
        self::DEFAULT,
        self::INVITE_CUSTOMER,
        self::MEDIA_LIBRARY,
        //        self::DB_BACKUP,
    ];

    //    final public const HIGH = 'high';
    //
    //    final public const MEDIUM = 'medium';
    //
    //    final public const LOW = 'low';

    final public const DEFAULT = 'default';

    final public const INVITE_CUSTOMER = 'invite_customer';

    final public const MEDIA_LIBRARY = 'media_library';

    //    final public const DB_BACKUP = 'db_backup';

    private function __construct()
    {
        //
    }
}
