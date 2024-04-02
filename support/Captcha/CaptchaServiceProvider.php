<?php

declare(strict_types=1);

namespace Support\Captcha;

use Illuminate\Support\ServiceProvider;

class CaptchaServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/captcha.php', 'captcha');
    }
}
