<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\HasEmailVerificationOTP;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class GenerateOTPForEmailVerificationAction
{
    public function execute(HasEmailVerificationOTP&MustVerifyEmail $model): string
    {
        $model->emailVerificationOneTimePassword?->delete();

        $password = (string) str()->uuid();

        $model
            ->emailVerificationOneTimePassword()
            ->create([
                'password' => $password,
                'expired_at' => now()->addMinutes(60),
            ]);

        return $password;
    }
}
