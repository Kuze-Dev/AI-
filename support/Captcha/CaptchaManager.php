<?php

declare(strict_types=1);

namespace Support\Captcha;

use Closure;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Support\Captcha\Providers\BaseProvider;
use Support\Captcha\Providers\CloudflareTurnstileProvider;
use Support\Captcha\Providers\GoogleRecaptchaProvider;

class CaptchaManager
{
    protected static ?Closure $resolveProviderUsing = null;

    protected static ?Closure $resolveCredentialsUsing = null;

    protected Repository $config;

    public function __construct(protected Container $container)
    {
        $this->config = $container->make('config');
    }

    public function provider(?CaptchaProvider $provider = null): BaseProvider
    {
        return match ($provider ?? $this->getDefaultProvider()) {
            CaptchaProvider::GOOGLE_RECAPTCHA => $this->createGoogleRecaptchaProvider(),
            CaptchaProvider::CLOUDFLARE_TURNSTILE => $this->createCloudflareTurnstileProvider(),
            default => throw new InvalidArgumentException(sprintf(
                'Unable to resolve provider for [%s].',
                static::class
            ))
        };
    }

    public function getDefaultProvider(): ?CaptchaProvider
    {
        if (self::$resolveProviderUsing !== null) {
            return call_user_func(self::$resolveProviderUsing);
        }

        return $this->config->get('catpcha.provider');
    }

    protected function getCredentials(): array
    {
        if (self::$resolveCredentialsUsing !== null) {
            return call_user_func(self::$resolveCredentialsUsing);
        }

        return $this->config->get('captcha.credentials');
    }

    protected function createGoogleRecaptchaProvider(): GoogleRecaptchaProvider
    {
        return new GoogleRecaptchaProvider($this->getCredentials());
    }

    protected function createCloudflareTurnstileProvider(): CloudflareTurnstileProvider
    {
        return new CloudflareTurnstileProvider($this->getCredentials());
    }

    public static function resolveProviderUsing(Closure $callback): void
    {
        self::$resolveProviderUsing = $callback;
    }

    public static function resolveCredentialsUsing(Closure $callback): void
    {
        self::$resolveCredentialsUsing = $callback;
    }

    public function __call(string $method, array $parameters): mixed
    {
        return $this->provider()->$method(...$parameters);
    }
}
