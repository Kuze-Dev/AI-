<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ShippingSettings extends Settings
{
    public ?array $usps_credentials = null;

    public bool $usps_mode;

    public static function encrypted(): array
    {
        return [
            'usps_credentials',
        ];
    }

    public static function group(): string
    {
        return 'shipping';
    }

    public function getUSPSUsername(): string
    {
        return $this->check('username');
    }

    public function getUSPSPassword(): string
    {
        return $this->check('password');
    }

    private function check(string $param): string
    {
        if ($this->usps_credentials === null) {
            abort(400, 'Setting ['.self::group().'] not set.');
        }

        if ( ! isset($this->usps_credentials[$param])) {
            abort(400, 'Setting ['.self::group().'] not set.');
        }

        return $this->usps_credentials[$param];
    }
}
