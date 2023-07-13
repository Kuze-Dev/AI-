<?php

namespace Domain\Taxation\Facades;

use Domain\Taxation\Taxation as TaxationTaxation;
use Illuminate\Support\Facades\Facade;

class Taxation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'taxation';
    }
}
