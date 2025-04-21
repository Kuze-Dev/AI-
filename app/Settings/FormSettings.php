<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;
use Support\Captcha\CaptchaProvider;

class FormSettings extends Settings
{
    public ?CaptchaProvider $provider = null;

    public string $sender_email = '';

    public ?string $site_key = null;

    public ?string $secret_key = null;

    #[\Override]
    public static function group(): string
    {
        return 'form';
    }

    #[\Override]
    public static function encrypted(): array
    {
        return [
            // 'site_key', # uncomment if you want to encrypt this field
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
