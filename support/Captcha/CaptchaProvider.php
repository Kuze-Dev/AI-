<?php

declare(strict_types=1);

namespace Support\Captcha;

enum CaptchaProvider: string
{
    case GOOGLE_RECAPTCHA = 'google_recaptcha';
    case CLOUDFLARE_TURNSTILE = 'cloudflare_turnstile';
}
