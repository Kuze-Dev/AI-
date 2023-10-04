<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ServiceSettings extends Settings
{
    public ?int $service_category;

    public static function group(): string
    {
        return 'service';
    }
}
