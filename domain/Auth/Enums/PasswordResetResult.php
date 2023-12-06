<?php

declare(strict_types=1);

namespace Domain\Auth\Enums;

use Illuminate\Validation\ValidationException;

/**
 * @see \Illuminate\Contracts\Auth\PasswordBroker
 */
enum PasswordResetResult: string
{
    case RESET_LINK_SENT = 'passwords.sent';
    case PASSWORD_RESET = 'passwords.reset';
    case INVALID_USER = 'passwords.user';
    case INVALID_TOKEN = 'passwords.token';
    case RESET_THROTTLED = 'passwords.throttled';

    public function getMessage(): string
    {
        return trans($this->value);
    }

    public function failed(): bool
    {
        return match ($this) {
            self::INVALID_USER, self::INVALID_TOKEN, self::RESET_THROTTLED => true,
            default => false,
        };
    }

    public function throw(?string $redirectTo = null): void
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
