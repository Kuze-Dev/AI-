<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ECommerceSettings extends Settings
{
    public ?string $front_end_domain = null;

    public static function group(): string
    {
        return 'e-commerce';
    }
}
