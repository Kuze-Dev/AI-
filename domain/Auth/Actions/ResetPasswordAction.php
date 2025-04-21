<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\DataTransferObjects\ResetPasswordData;
use Domain\Auth\Enums\PasswordResetResult;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Password;

class ResetPasswordAction
{
    public function __construct(
        protected UpdatePasswordAction $updatePassword
    ) {}

    public function execute(ResetPasswordData $resetPasswordData, ?string $broker = null): PasswordResetResult
    {
        $result = Password::broker($broker)
            ->reset(
                [
                    'email' => $resetPasswordData->email,
                    'password' => $resetPasswordData->password,
                    'token' => $resetPasswordData->token,
                ],
                function (User $user, string $password) {
                    $this->updatePassword->execute($user, $password);

                    event(new PasswordReset($user));
                }
            );

        return PasswordResetResult::from($result);
    }
}
