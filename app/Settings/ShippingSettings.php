<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShippingSettings extends Settings
{
    public bool $usps_production_mode;
    public ?string $usps_username = null;
    public ?string $usps_password = null;

    public static function group(): string
    {
        return 'shipping';
    }

    public static function encrypted(): array
    {
        return [
            'usps_username',
            'usps_password',
        ];
    }
}
