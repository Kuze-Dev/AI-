<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\HasEmailVerificationOTP;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;

readonly class VerifyEmailViaOTPAction
{
    public function __construct(
        private VerifyEmailAction $verifyEmail
    ) {}

    public function execute(HasEmailVerificationOTP&MustVerifyEmail $user, string $otp): ?bool
    {
        $otpModel = $user->emailVerificationOneTimePassword;

        if (
            $otpModel === null ||
            $otpModel->expired_at < now() ||
            ! Hash::check($otp, $otpModel->password)
        ) {
            return false;
        }

        $result = $this->verifyEmail->execute($user);

        $otpModel->delete();

        return $result;
    }
}
