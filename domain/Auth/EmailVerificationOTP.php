<?php

declare(strict_types=1);

namespace Domain\Auth;

use Domain\Auth\Actions\GenerateOTPForEmailVerificationAction;
use Domain\Auth\Enums\EmailVerificationType;
use Domain\Auth\Model\EmailVerificationOneTimePassword;
use Exception;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read \Domain\Auth\Model\EmailVerificationOneTimePassword|null $emailVerificationOneTimePassword
 */
trait EmailVerificationOTP
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Domain\Auth\Model\EmailVerificationOneTimePassword, $this> */
    public function emailVerificationOneTimePassword(): MorphOne
    {
        return $this->morphOne(EmailVerificationOneTimePassword::class, 'authenticatable');
    }

    public function getEmailVerificationColumn(): EmailVerificationType
    {
        return $this->email_verification_type ?? throw new Exception('Customer email_verification_type not defined.');
    }

    public function isEmailVerificationUseOTP(): bool
    {
        return $this->getEmailVerificationColumn() === EmailVerificationType::OTP;
    }

    public function generateEmailVerificationOTP(): string
    {
        return app(GenerateOTPForEmailVerificationAction::class)->execute($this);
    }
}
