<?php

declare(strict_types=1);

namespace Domain\Support\Captcha\Providers;

abstract class BaseProvider
{
    public function __construct(protected array $credentials)
    {
    }

    abstract public function verify(string $token, ?string $ip = null): bool;
}
