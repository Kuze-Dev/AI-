<?php

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Model\RecoveryCode;

class ValidateRecoveryCodeAction
{
    public function execute(TwoFactorAuthenticatable $authenticatable, string $code): bool
    {
        /** @var RecoveryCode|null $recoveryCode */
        $recoveryCode = $authenticatable->twoFactorAuthentication->recoveryCodes
            ->first(fn (RecoveryCode $recoveryCode): bool => hash_equals($recoveryCode->code, $code));

        if ($recoveryCode && ! $recoveryCode->isUsed()) {
            $recoveryCode->markUsed();

            return true;
        }

        return false;
    }
}
