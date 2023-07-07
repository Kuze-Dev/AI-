<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShippingSettings extends Settings
{
    public ?array $usps_credentials = null;

    public bool $usps_mode;

    public static function group(): string
    {
        return 'shipping';
    }
}
