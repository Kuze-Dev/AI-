<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Enums\PasswordResetResult;
use Domain\Auth\Events\PasswordResetSent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Password;

class ForgotPasswordAction
{
    public function execute(string $email, ?string $broker = null): PasswordResetResult
    {
        $result = Password::broker($broker)
            ->sendResetLink(
                compact('email'),
                function (CanResetPassword&Authenticatable&Model $user, string $token) {
                    $user->sendPasswordResetNotification($token);

                    event(new PasswordResetSent($user));
                }
            );

        return PasswordResetResult::from($result);
    }
}
