<?php

declare(strict_types=1);

namespace Support\Captcha\Facades;

use Illuminate\Support\Facades\Facade;
use Support\Captcha\CaptchaManager;

/**
 * @method static bool verify(string $token, ?string $ip = null)
 *
 * @mixin CaptchaManager
 */
class Captcha extends Facade
{
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return CaptchaManager::class;
    }
}
