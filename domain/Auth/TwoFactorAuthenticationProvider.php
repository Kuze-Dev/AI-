<?php

declare(strict_types=1);

namespace Domain\Auth;

use Domain\Auth\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Illuminate\Contracts\Cache\Repository;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationProvider implements TwoFactorAuthenticationProviderContract
{
    public function __construct(
        protected Google2FA $engine,
        protected ?Repository $cache = null
    ) {}

    #[\Override]
    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    #[\Override]
    public function getCurrentOtp(string $secret): string
    {
        return $this->engine->getCurrentOtp($secret);
    }

    #[\Override]
    public function qrCodeUrl(string $name, string $holder, string $secret): string
    {
        return $this->engine->getQRCodeUrl($name, $holder, $secret);
    }

    #[\Override]
    public function verify(string $secret, string $code): bool
    {
        if (is_int($customWindow = config('domain.auth.two_factor.totp.window'))) {
            $this->engine->setWindow($customWindow);
        }

        $cacheKey = '2fa_codes.'.md5($code);

        $timestamp = $this->engine->verifyKeyNewer($secret, $code, $this->cache?->get($cacheKey));

        if ($timestamp === false) {
            return false;
        }

        if ($timestamp === true) {
            $timestamp = $this->engine->getTimestamp();
        }

        $this->cache?->put($cacheKey, $timestamp, ($this->engine->getWindow() ?: 1) * 60);

        return true;
    }
}
