<?php

declare(strict_types=1);

namespace Domain\Auth\Contracts;

use Domain\Auth\Enums\EmailVerificationType;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasEmailVerificationOTP
{
    public function emailVerificationOneTimePassword(): MorphOne;

    public function getEmailVerificationColumn(): EmailVerificationType;

    public function isEmailVerificationUseOTP(): bool;

    public function generateEmailVerificationOTP(): string;
}
