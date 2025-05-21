<?php

declare(strict_types=1);

namespace Domain\Auth\Contracts;

use Domain\Auth\Enums\EmailVerificationType;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read \Domain\Auth\Model\EmailVerificationOneTimePassword|null $emailVerificationOneTimePassword
 */
interface HasEmailVerificationOTP
{
    /** @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Domain\Auth\Model\EmailVerificationOneTimePassword>
     * @phpstan-ignore generics.lessTypes  */
    public function emailVerificationOneTimePassword(): MorphOne;

    public function getEmailVerificationColumn(): EmailVerificationType;

    public function isEmailVerificationUseOTP(): bool;

    public function generateEmailVerificationOTP(): string;
}
