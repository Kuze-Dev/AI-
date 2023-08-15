<?php

declare(strict_types=1);

namespace Support\Captcha\Facades;

use Support\Captcha\CaptchaManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool verify(string $token, ?string $ip = null)
 *
 * @mixin CaptchaManager
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CaptchaManager::class;
    }
}
