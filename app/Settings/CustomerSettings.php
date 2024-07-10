<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CustomerSettings extends Settings
{
    public ?string $blueprint_id = '';

    public ?string $date_format = '';

    public static function group(): string
    {
        return 'customer';
    }
}
