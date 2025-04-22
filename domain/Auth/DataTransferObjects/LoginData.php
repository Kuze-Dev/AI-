<?php

declare(strict_types=1);

namespace Domain\Auth\DataTransferObjects;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
        public ?string $guard = null,
    ) {}

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.Request::ip());
    }
}
