<?php

declare(strict_types=1);

namespace Domain\Taxation\Facades;

use Illuminate\Support\Facades\Facade;

class Taxation extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return 'taxation';
    }
}
