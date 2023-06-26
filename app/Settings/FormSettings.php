<?php

declare(strict_types=1);

namespace App\Settings;

use Support\Captcha\CaptchaProvider;
use Spatie\LaravelSettings\Settings;

class FormSettings extends Settings
{
    public ?CaptchaProvider $provider = null;

    public ?string $site_key = null;

    public ?string $secret_key = null;

    public static function group(): string
    {
        return 'form';
    }

    public static function encrypted(): array
    {
        return [
            'site_key',
            'secret_key',
        ];
    }

    public function getCredentials(): array
    {
        return [
            'site_key' => $this->site_key,
            'secret_key' => $this->secret_key,
        ];
    }
}
