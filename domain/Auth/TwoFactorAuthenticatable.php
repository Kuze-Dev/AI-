<?php

namespace Domain\Auth;

use Domain\Auth\Model\TwoFactorAuthentication;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read TwoFactorAuthentication $twoFactorAuthentication
 */
trait TwoFactorAuthenticatable
{
    protected string $twoFactorHolderAttribute = 'email';

    public function twoFactorHolder(): string
    {
        return $this->getAttribute($this->twoFactorHolderAttribute);
    }

    public function twoFactorAuthentication(): MorphOne
    {
        return $this->morphOne(TwoFactorAuthentication::class, 'authenticatable');
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        if ($this->relationLoaded('twoFactorAuthentication')) {
            return (bool) $this->twoFactorAuthentication->enabled_at;
        }

        return $this->twoFactorAuthentication()->whereNotNull('enabled_at')->exists();
    }
}
