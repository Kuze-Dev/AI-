<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    public ?string $paypal_secret_id = null;

    public ?string $paypal_secret_key = null;

    public bool $paypal_production_mode;

    public ?string $stripe_publishable_key = null;

    public ?string $stripe_secret_key = null;

    public bool $stripe_production_mode;

    public ?string $vision_pay_apiKey = null;

    public bool $vision_pay_production_mode;
    

    public static function group(): string
    {
        return 'payments';
    }

    public static function encrypted(): array
    {
        return [
            'paypal_secret_id',
            'paypal_secret_key',
            'stripe_publishable_key',
            'stripe_secret_key',
            'vision_pay_apiKey',
        ];
    }
}
