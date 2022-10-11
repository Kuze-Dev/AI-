<?php

namespace Domain\Auth\Contracts;

use Domain\Auth\Model\TwoFactorAuthentication;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read TwoFactorAuthentication $twoFactorAuthentication
 */
interface TwoFactorAuthenticatable
{
    public function twoFactorHolder(): string;

    /** @return MorphOne<TwoFactorAuthentication> */
    public function twoFactorAuthentication(): MorphOne;

    public function hasEnabledTwoFactorAuthentication(): bool;
}
