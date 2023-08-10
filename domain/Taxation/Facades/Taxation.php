<?php

declare(strict_types=1);

namespace Domain\Taxation\Facades;

use Illuminate\Support\Facades\Facade;

class Taxation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'taxation';
    }
}
