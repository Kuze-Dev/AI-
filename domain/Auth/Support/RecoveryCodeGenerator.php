<?php

namespace Domain\Auth\Support;

use Illuminate\Support\Str;

class RecoveryCodeGenerator
{
    public static function generate(): string
    {
        return Str::random(10).'-'.Str::random(10);
    }
}
