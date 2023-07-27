<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShippingSettings extends Settings
{
    public bool $usps_production_mode;
    public bool $ups_production_mode;

    /** usps */
    public ?string $usps_username = null;
    public ?string $usps_password = null;

    /** ups */
    public ?string $ups_username = null;
    public ?string $ups_password = null;
    public ?string $access_license_number = null;

    public static function group(): string
    {
        return 'shipping';
    }

    public static function encrypted(): array
    {
        return [
            'usps_username',
            'usps_password',
            'ups_username',
            'ups_password',
            'access_license_number',
        ];
    }
}
