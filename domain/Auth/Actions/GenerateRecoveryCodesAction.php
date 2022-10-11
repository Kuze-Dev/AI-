<?php

declare(strict_types=1);

namespace Domain\Auth\Actions;

use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Events\RecoveryCodesGenerated;
use Domain\Auth\Support\RecoveryCodeGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

class GenerateRecoveryCodesAction
{
    public function execute(TwoFactorAuthenticatable $authenticatable): TwoFactorAuthenticatable
    {
        $authenticatable->twoFactorAuthentication->recoveryCodes()->delete();
        $authenticatable->twoFactorAuthentication->recoveryCodes()
            ->createMany(
                Collection::times(
                    config('domain.auth.two_factor.recovery_codes.count'),
                    fn () => ['code' => RecoveryCodeGenerator::generate()]
                )
            );

        Event::dispatch(new RecoveryCodesGenerated($authenticatable));

        return $authenticatable;
    }
}
