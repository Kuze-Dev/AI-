<?php

declare(strict_types=1);

namespace Domain\Auth\Exceptions;

use LogicException;

class UserProviderNotSupportedException extends LogicException
{
    public function __construct(string $guard)
    {
        $provider = config("auth.guards.{$guard}.provider");
        $driver = config("auth.provider.{$provider}.driver");

        $this->message = "`auth.provider.{$provider}.driver` must be set to `eloquent`, `{$driver}` was found.";
    }
}
