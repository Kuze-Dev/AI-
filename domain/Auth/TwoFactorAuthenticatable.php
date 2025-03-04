<?php

declare(strict_types=1);

namespace Domain\Auth;

use Domain\Auth\Actions\GenerateRecoveryCodesAction;
use Domain\Auth\Actions\SetupTwoFactorAuthenticationAction;
use Domain\Auth\Contracts\TwoFactorAuthenticatable as TwoFactorAuthenticatableContract;
use Domain\Auth\Model\TwoFactorAuthentication;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property-read \Domain\Auth\Model\TwoFactorAuthentication|null $twoFactorAuthentication
 */
trait TwoFactorAuthenticatable
{
    protected string $twoFactorHolderAttribute = 'email';

    public static function bootTwoFactorAuthenticatable(): void
    {
        self::created(function (TwoFactorAuthenticatableContract $twoFactorAuthenticatable) {
            app(SetupTwoFactorAuthenticationAction::class)->execute($twoFactorAuthenticatable);
            app(GenerateRecoveryCodesAction::class)->execute($twoFactorAuthenticatable);
        });
    }

    public function twoFactorHolder(): string
    {
        return $this->getAttribute($this->twoFactorHolderAttribute);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<\Domain\Auth\Model\TwoFactorAuthentication, $this>
     *  @phpstan-ignore method.childReturnType */
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
