<?php

declare(strict_types=1);

namespace Support\Captcha;

use Filament\Support\Contracts\HasLabel;

enum CaptchaProvider: string implements HasLabel
{
    case GOOGLE_RECAPTCHA = 'google_recaptcha';
    case CLOUDFLARE_TURNSTILE = 'cloudflare_turnstile';

    public function getLabel(): string
    {
       return match ($this) {
           self::GOOGLE_RECAPTCHA => 'Google reCAPTCHA',
           self::CLOUDFLARE_TURNSTILE => 'Cloudflare Turnstile',
       };
    }
}
