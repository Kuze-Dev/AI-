<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PaymentSettings extends Settings
{
    public ?array $paypal_credentials = null;

    public bool $paypal_mode;

    public ?array $stripe_credentials = null;

    public bool $stripe_mode;

    public static function group(): string
    {
        return 'payments';
    }
}
