<?php

declare(strict_types=1);

namespace Domain\Support\Captcha\Facades;

use Domain\Support\Captcha\CaptchaManager;
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
