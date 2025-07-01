<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class APISettings extends Settings
{
    public ?string $api_key = '';

    #[\Override]
    public static function group(): string
    {
        return 'api';
    }
}
