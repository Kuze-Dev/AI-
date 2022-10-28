<?php

declare(strict_types=1);

namespace Domain\Auth;

use Domain\Auth\Model\TwoFactorAuthentication;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read \Domain\Auth\Model\TwoFactorAuthentication|null $twoFactorAuthentication
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
            return $this->twoFactorAuthentication?->enabled_at !== null;
        }

        return $this->twoFactorAuthentication()->whereNotNull('enabled_at')->exists();
    }
}
