<?php

declare(strict_types=1);

namespace Domain\Auth\DataTransferObjects;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class LoginData
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $remember = false,
        public readonly ?string $guard = null,
    ) {
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.Request::ip());
    }
}
