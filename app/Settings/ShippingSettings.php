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
    public ?string $ups_client_id = null;

    public ?string $ups_client_secret = null;

    public ?string $ups_shipper_account = null;

    /** auspost */
    public ?string $auspost_api_key = null;

    #[\Override]
    public static function group(): string
    {
        return 'shipping';
    }

    #[\Override]
    public static function encrypted(): array
    {
        return [
            'usps_username',
            'usps_password',
            'ups_client_id',
            'ups_client_secret',
        ];
    }
}
