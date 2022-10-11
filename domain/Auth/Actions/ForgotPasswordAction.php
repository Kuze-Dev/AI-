<?php

namespace Domain\Auth\Actions;

use Domain\Auth\Enums\PasswordResetResult;
use Illuminate\Support\Facades\Password;

class ForgotPasswordAction
{
    public function execute(string $email, ?string $broker = null): PasswordResetResult
    {
        $result = Password::broker($broker)
            ->sendResetLink(compact('email'));

        return PasswordResetResult::from($result);
    }
}
