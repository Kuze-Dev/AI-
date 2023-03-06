<?php

declare(strict_types=1);

namespace Domain\Auth\Enums;

use Illuminate\Validation\ValidationException;

enum EmailVerification: string
{
    case VERIFIED = 'user.verified';
    case INVALID_USER = 'user.invalid';
    case SENT = 'user.verify';

    public function getMessage(): string
    {
        return trans($this->value);
    }

    public function failed(): bool
    {
        return match ($this) {
            self::VERIFIED, self::INVALID_USER => true,
            default => false,
        };
    }

    public function throw(string $redirectTo = null): void
    {
        if ($this->failed()) {
            $exception = ValidationException::withMessages(['email' => $this->getMessage()]);

            if ($redirectTo) {
                $exception->redirectTo($redirectTo);
            }

            throw $exception;
        }
    }
}
